<?php

namespace RTNatePHP\BasicApp\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * Session middleware
 *
 * Currently relies on native PHP sessions
 *
 */
class Session
{
    /**
     * The session settings 
     * 
     * @var array
     */
    protected $settings;

    /**
     * Constructor
     *
     * @param array $settings
     */
    public function __construct($settings = [])
    {
        $defaults = [
            'lifetime'     => '20 minutes',
            'path'         => '/',
            'domain'       => null,
            'secure'       => false,
            'httponly'     => false,
            'name'         => 'app_session',
            'autorefresh'  => false,
            'handler'      => null,
            'ini_settings' => [],
        ];

        $settings = array_merge($defaults, $settings);

        if (is_string($lifetime = $settings['lifetime'])) {
            $settings['lifetime'] = strtotime($lifetime) - time();
        }

        $this->settings = $settings;

        //INI SET NOT IMPLEMENTED HERE
        /*
        $this->iniSet($settings['ini_settings']);
        // Just override this, to ensure package is working
        if (ini_get('session.gc_maxlifetime') < $settings['lifetime']) {
            $this->iniSet([
                'session.gc_maxlifetime' => $settings['lifetime'] * 2,
            ]);
        }*/
    }

    /**
     * Called when middleware needs to be executed.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request PSR7 request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler PSR7 handler
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $this->startSession();

        return $handler->handle($request);
    }

    /**
     * Start session
     */
    protected function startSession()
    {
        //Check if a session has currently been started
        $sessionIsInactive = session_status() === PHP_SESSION_NONE;
        //If a session has already been started, return
        if (!$sessionIsInactive) return;

        //Set the session parameters based on the middleware settings
        $settings = $this->settings;
        $name = $settings['name'];

        session_set_cookie_params(
            $settings['lifetime'],
            $settings['path'],
            $settings['domain'],
            $settings['secure'],
            $settings['httponly']
        );

        // If autorefresh is set, refresh the cookie
        // once it has gone inactive
        // else PHP won't know we want this to refresh
        if ($settings['autorefresh'] && isset($_COOKIE[$name])) {
            setcookie(
                $name,
                $_COOKIE[$name],
                time() + $settings['lifetime'],
                $settings['path'],
                $settings['domain'],
                $settings['secure'],
                $settings['httponly']
            );
        }

        session_name($name);

        $handler = $settings['handler'];
        if ($handler) {
            //If the $handler is not a valid session handler, create one
            if ($handler) {
                if (!($handler instanceof \SessionHandlerInterface)) {
                    $handler = new $handler;
                }
                session_set_save_handler($handler, true);
            }
        }

        session_cache_limiter(false);
        session_start();
    }

    //INI Set not implemented
    /*
    protected function iniSet($settings)
    {
        foreach ($settings as $key => $val) {
            if (strpos($key, 'session.') === 0) {
                ini_set($key, $val);
            }
        }
    }*/
}