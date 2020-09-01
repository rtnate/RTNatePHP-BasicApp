<?php 
/**
 * SlimFactory Class Definition
  
 * PHP version 5
 *
 * LICENSE: MIT (see ../../LICENSE) 
 *
 * @package    rtnatephp/basic-app
 * @author     Nate Taylor <nate@rtelectronix.com
 * @copyright  2020 - Nate Taylor
 * @license    MIT (see ../../LICENSE) 
 * @link       http://www.github.com/rtnate/RTNatePHP-BasicApp/
 *
 */

namespace RTNatePHP\BasicApp\Factories;

use \DI\Bridge\Slim\Bridge as SlimDiBridge;
use \DI\Container;
use Illuminate\Support\Arr;

/**
 * Factory class from building a \Slim\App 
 * 
 * @see http://www.slimframework.com/docs/v4/
 */
class SlimFactory
{
    protected $slim;

    /**
     * Slim error middleware instance
     *
     * @var \Slim\Middleware\ErrorMiddleware|null
     */
    protected $error_middleware = null;

    protected $error_renderers = [];

    /**
     * Slim app's errror handler
     *
     * @var \Slim\Handlers\ErrorHandler|null
     */
    protected $error_handler = null;

    protected $global_middleware = [];

    /**
     * Slim app global debug flag
     *
     * @var bool
     */
    protected $debug;

    /**
     * Create a new SlimFactory 
     *
     * @param Container $container The PHP-DI Container 
     * @param array $options Set ['debug' => true] to enable debugging
     */
    public function __construct(Container $container, $options = [])
    {   
        $this->debug = Arr::get($options, 'debug', false);
        $this->slim = SlimDiBridge::create($container);
    }

    /**
     * Add the Slim Error Middleware to the app
     *
     * @return void
     */
    protected function addErrorMiddleware()
    {
        if (!$this->error_middleware) $this->error_middleware = $this->slim->addErrorMiddleware($this->debug, true, true);
        if ($this->error_handler) $this->error_middleware->setDefaultErrorHandler($this->error_handler);
        else $this->error_handler = $this->error_middleware->getDefaultErrorHandler();
        foreach($this->error_renderers as $type => $renderer)
        {
            if ($this->error_handler instanceof \Slim\Handlers\ErrorHandler)
                $this->error_handler->registerErrorRenderer($type, $renderer);
        }
    }

    /**
     * Load any supplied middleware into the slim instance
     *
     * @return void
     */
    protected function loadMiddleware()
    {
        foreach($this->global_middleware as $key => $middleware)
        {
            $this->slim->add($middleware);
        }
    }

    /**
     * Add global middleware to the slim app
     *
     * @param \Psr\Http\Server\MiddlewareInterface|string|callable $middleware
     * @return void
     */
    public function addGlobalMiddleware($middleware)
    {
        array_push($this->global_middleware, $middleware);
    }

    /**
     * Add an error rendered for the supplied content type
     *
     * @param string $contentType
     * @param \Psr\Http\Server\MiddlewareInterface|string|callable $renderer
     * @return void
     */
    public function addErrorRenderer($contentType, $renderer)
    {
        $this->error_renderers[$contentType] =  $renderer;
    }

    /**
     * Build the slim app
     *
     * @return \Slim\App The Slim app
     */
    public function build()
    {
        $this->slim->addRoutingMiddleware();
        $this->loadMiddleware();
        $this->addErrorMiddleware();
        return $this->slim;
    }

    /**
     * Shortcut function for creating a Slim app from the supplied PHP-DI container
     *
     * @param Container $container The PHP-DI instance
     * @return \Slim\App The Slim app instance
     */
    static function create(Container $container)
    {
        $factory = new static($container);
        return $factory->build();
    }

}