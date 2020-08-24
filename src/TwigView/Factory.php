<?php

namespace RTNatePHP\BasicApp\TwigView;

use Psr\Container\ContainerInterface;
use RTNatePHP\BasicApp\Helpers\PathHelper;

class Factory
{
    static public function build(ContainerInterface $ci)
    {
        $paths = $ci->get(PathHelper::class);
        $templates = $paths->get('twig.template_location');
        $cache = $paths->get('twig.cache_location');
        $options = $paths->config('twig.options');
        $options['cache'] = $cache;
        $loader = new \Twig\Loader\FilesystemLoader($templates);
        $twig = new \Twig\Environment($loader, $options);
        $extension = new Extension($ci);
        $twig->addExtension($extension);
        return $twig;
    }
}