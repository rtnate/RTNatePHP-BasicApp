<?php

namespace RTNatePHP\BasicApp\Traits;

use RTNatePHP\BasicApp\Helpers\ConfigHelper;

trait ConfigHelperFunctions
{
    public function config($key = null, $default = null)
    {
        $config = $this->configHelper;
        if ($key == null) return $config;
        else{
            return $config->get($key, $default);
        }
    }
}