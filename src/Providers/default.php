<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use RTNatePHP\BasicApp\BasicApp;
use RTNatePHP\BasicApp\Database\ConnectionFactory;
use RTNatePHP\BasicApp\Factories\AppFactory;
use RTNatePHP\BasicApp\TwigView\Factory as TwigFactory;

return[
    BasicApp::class => function(){ return AppFactory::getInstance(); },
    \Twig\Environment::class => \DI\Factory([TwigFactory::class, 'build']),
    Capsule::class => function(){ return ConnectionFactory::getCapsuleOrFail(); }
];