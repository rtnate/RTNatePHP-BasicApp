<?php 

namespace RTNatePHP\BasicApp\Exception;

use RTNatePHP\BasicApp\Controllers\ErrorController;
use Throwable;

class HTMLExceptionRenderer
{
    protected $controller;

    public function __construct(ErrorController $controller)
    {
        $this->controller = $controller;
    }

    public function __invoke(Throwable $exception, bool $displayErrorDetails)
    {
       return $this->controller->renderException($exception, $displayErrorDetails);
    }
}