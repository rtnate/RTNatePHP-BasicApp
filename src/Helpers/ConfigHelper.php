<?php 

namespace RTNatePHP\BasicApp\Helpers;

use ArrayAccess;
use \Illuminate\Support\Arr;
use Psr\Container\ContainerInterface;
use RTNatePHP\Util\Interfaces\GetterAndSetter;

//Configuration Helper Class Accesses App Configuration Values
class ConfigHelper implements GetterAndSetter{

    use \RTNatePHP\Util\Traits\GettableNotSettable;

    protected $container;
    protected $key;

    /**
     * Class constructor takes the array of configuration values as its Argument
     */
    public function __construct(ContainerInterface $container, string $key = 'config')
    {
        $this->container = $container;
        $this->key = $key;
    }

    public function has($key)
    {
        $config = $this->container->get($this->key);
        return Arr::has($config, $key);
    }

    /**
     * Get's a configuration value
     * 
     * @param string|int|null $key - The array key (dot notation supported)
     * @param mixed $default - The default value to return if not found 
     */
    public function get($key, $default = null)
    {
        $config = $this->container->get($this->key);
        if (!is_array($config)) throw new \Exception("Error fetching configuration from the DI Container");
        return Arr::get($config, $key, $default);
    }
}
