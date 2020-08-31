<?php

namespace  RTNatePHP\BasicApp\TwigView;

use NateMakesStuff\Helpers\ConfigHelper;
use Psr\Container\ContainerInterface;
use RTNatePHP\BasicApp\Helpers\UrlHelper;
use \Twig\Environment as TwigEnv;

class Extension extends \Twig\Extension\AbstractExtension implements \Twig\Extension\GlobalsInterface
{
    protected $ci;

    public function __construct(ContainerInterface $ci)
    {
        $this->ci = $ci;
    }

    public function getGlobals()
    {
        $cfg = $this->ci->get(ConfigHelper::class);
        $site = $cfg['site'];
        return
        [
            'site' => $site
        ];
    }

    public function getFunctions()
    {
        return [
            $this->urlHelper(), 
            $this->assetHelper()
        ];
    }

    public function getFilters()
    {
        return [
            $this->snakeCaseFilter()
        ];
    }

    public function urlHelper()
    {
        return new \Twig\TwigFunction('url', function($url)
        {
            $helper = $this->ci->get(UrlHelper::class);
            return $helper($url);
        });
    }

    public function assetHelper()
    {
        return new \Twig\TwigFunction('asset', function($url)
        {
            $helper = $this->ci->get(UrlHelper::class);
            return $helper->asset($url);
        });
    }

    public function snakeCaseFilter()
    {
        return new \Twig\TwigFilter('snake_case', function($str)
        {
            $output = str_replace([" ", "\t", "-"], "_", $str);
            return strtolower($output);
        });
    }
}