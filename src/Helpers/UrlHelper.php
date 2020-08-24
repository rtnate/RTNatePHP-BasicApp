<?php

namespace RTNatePHP\BasicApp\Helpers;

use RTNatePHP\Util\Interfaces\GetterAndSettter;

class UrlHelper
{
    protected $base;
    protected $asset;

    public function __construct(ConfigHelper $config)
    {
        $this->base = $config->get('site.url');
        $this->asset = $config->get('site.asset_path');
    }

    public function __invoke(string $url)
    {
        return $this->generate($url);
    }

    public function generate(string $url)
    {
        return "{$this->base}/{$url}";
    }

    public function asset(string $url)
    {
        return $this->base.$this->asset."/".$url;
    }
}