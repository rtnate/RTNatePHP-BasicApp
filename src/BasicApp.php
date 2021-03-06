<?php 

namespace RTNatePHP\BasicApp;

use Illuminate\Support\Arr;
use RTNatePHP\BasicApp\Helpers\ConfigHelper;
use Psr\Container\ContainerInterface;
use RTNatePHP\BasicApp\Helpers\PathHelper;
use RTNatePHP\BasicApp\Helpers\UrlHelper;
use RTNatePHP\Util\FileLoader;
use Slim\App as Slim;

class BasicApp implements \ArrayAccess{ 
    use \RTNatePHP\BasicApp\Traits\CIArrayAccess;
    use \RTNatePHP\Util\Traits\ForwardsCalls;

    protected $ci;
    protected $slim;

    private $capsule;

    public function __construct(ContainerInterface $container, Slim $slim) {
        $this->ci = $container;
        $this->slim = $slim;
        $this->loadRoutes();
    }

    static final public function prepare(ContainerInterface $container)
    {
        static::beforeBoot($container);
    }

    static protected function beforeBoot(ContainerInterface $conatiner)
    {
        return;
    }

    protected function onBoot()
    {
        return;
    }

    protected function beforeRun()
    {
        return;
    }

    public function run()
    {
        $this->beforeRun();
        $this->slim->run();
    }



    protected function loadMiddleware()
    {

    }

    protected function loadRoutes()
    {
        $routePath = $this->path(null, 'routes');
        $sourcePath = $this->path(null, 'source');
        $sourceNamespace = $this->config('source.namespace');
        $loader = new FileLoader($routePath, FileLoader::MODE_LOAD_PHP_CLASSES);
        $loader->setPsr4($sourcePath, $sourceNamespace);
        $routeClasses = $loader->load();
        foreach($routeClasses as $routeClass){
            $routes = new $routeClass();
            $routes($this);
        }
    }

    protected function getForwardObject()
    {
        return $this->slim;
    }


    public function asset(string $url)
    {
        $helper = $this->ci->get(UrlHelper::class);
        return $helper->asset($url);
    }

    /**
     * Url generating helper function.  
     * Generates the full url for the supplied relative route.
     * 
     * @param string $url The relative path to generate a url for.
     *          An empty string will return the page url.
     * 
     * @return string The generated url
     */
    public function url(string $url)
    {
        $helper = $this->ci->get(UrlHelper::class);
        return $helper($url);
    }

    public function config(string $key, $default = null)
    {
        $cfg = $this->ci->get(ConfigHelper::class);
        return $cfg->get($key, $default);
    }

    public function path($path, string $config_key = null)
    {
        $helper = $this->ci->get(PathHelper::class);
        if (is_string($path))
        {
            return $helper($path);
        }
        else if (is_string($config_key))
        {
            return $helper->get($config_key);
        }
        else return $helper->generate('/');
    }
} 

?> 