<?php

namespace RTNatePHP\BasicApp\Traits;

use RTNatePHP\BasicApp\Helpers\ConfigHelper;

trait CIHelperFunctions
{
    public function config($key = null, $default = null)
    {
        $config = $this->ci->get(ConfigHelper::class);
        if ($key == null) return $config;
        else{
            return $config->get($key, $default);
        }
    }
}

