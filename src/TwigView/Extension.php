<?php

namespace  RTNatePHP\BasicApp\TwigView;

use RTNatePHP\BasicApp\Helpers\ConfigHelper;
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
            $this->snakeCaseFilter(),
            $this->titleShortenedFilter()
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

    public function titleShortenedFilter()
    {
        return new \Twig\TwigFilter('short_title', function($str)
        {
            //Split the title on the string ' - '
            $split = explode(' - ', $str);
            //Get the last element of the split array
            $short = end($split);
            return ucfirst($short);
        });
    }
}