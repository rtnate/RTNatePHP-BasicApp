<?php 

namespace RTNatePHP\BasicApp\Factories;

use \DI\Bridge\Slim\Bridge as SlimDiBridge;
use \DI\Container;
use Illuminate\Support\Arr;

class SlimFactory
{
    protected $slim;

    protected $error_middleware = null;

    protected $error_renderers = [];

    protected $error_handler = null;

    protected $global_middleware = [];

    protected $debug;

    public function __construct(Container $container, $options = [])
    {   
        $this->debug = Arr::get($options, 'debug', false);
        $this->slim = SlimDiBridge::create($container);
    }

    protected function addErrorMiddleware()
    {
        if (!$this->error_middleware) $this->error_middleware = $this->slim->addErrorMiddleware($this->debug, true, true);
        if ($this->error_handler) $this->error_middleware->setDefaultErrorHandler($this->error_handler);
        else $this->error_handler = $this->error_middleware->getDefaultErrorHandler();
        foreach($this->error_renderers as $type => $renderer)
        {
            $this->error_handler->registerErrorRenderer($type, $renderer);
        }
    }

    protected function loadMiddleware()
    {
        foreach($this->global_middleware as $key => $middleware)
        {
            $this->slim->add($middleware);
        }
    }

    public function addGlobalMiddleware($middleware)
    {
        array_push($this->global_middleware, $middleware);
    }

    public function addErrorRenderer($contentType, $renderer)
    {
        $this->error_renderers[$contentType] =  $renderer;
    }

    public function build()
    {
        $this->slim->addRoutingMiddleware();
        $this->loadMiddleware();
        $this->addErrorMiddleware();
        return $this->slim;
    }


    static function create(Container $container)
    {
        $factory = new static($container);
        return $factory->build();
    }

}