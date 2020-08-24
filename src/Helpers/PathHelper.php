<?php

namespace RTNatePHP\BasicApp\Helpers;

use RTNatePHP\Util\Interfaces\GetterAndSetter;

class PathHelper implements GetterAndSetter{
    use \RTNatePHP\BasicApp\Traits\ConfigHelperFunctions;
    use \RTNatePHP\Util\Traits\GettableNotSettable;

    protected $configHelper;
    protected $basePath;
    protected $key;

    public function __construct(ConfigHelper $config, string $key = 'paths')
    {
            $this->configHelper = $config;
            $this->key = $key;
            $this->basePath = $this->configHelper->get("{$this->key}.root", "/");
    }

    public function __invoke(string $path)
    {
        return $this->generate($path);
    }

    public function generate(string $path)
    {
        return $this->basePath.$path;
    }

     /**
     * Get's a configuration value
     * 
     * @param string|int|null $key - The array key (dot notation supported)
     * @param mixed $default - The default value to return if not found 
     */
    public function get($key, $default = null)
    {
        if (!$default) $default = $this->basePath;
        $path = $this->configHelper->get("{$this->key}.{$key}");
        if (!$path){
            $path = $this->configHelper->get("{$key}");
            if (!$path) return $default;
        }
        return $this->generate($path);
    }

    public function has($key)
    {
        $path = $this->configHelper->get("{$this->key}".$key);
        if (is_string($path) && strlen($path) > 0) return true;
        else return false;
    }

}