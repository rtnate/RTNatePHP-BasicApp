<?php

namespace NateMakesStuff\Controllers;

use Slim\Psr7\Response;
use \Twig\Environment as Twig;
use  RTNatePHP\BasicApp\BasicApp as App;

class Controller{

    protected $twig;
    protected $app;

    public function __construct(Twig $view, App $app)
    {
        $this->twig = $view;
        $this->app = $app;
    }

    public function view(Response $response, $name, array $context = [])
    {
        $result = $this->twig->render($name, $context);
        $response->getBody()->write($result);
        return $response;
    }
}