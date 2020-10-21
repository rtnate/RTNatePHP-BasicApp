<?php 

namespace RTNatePHP\BasicApp\Controllers;

use RTNatePHP\BasicApp\Controllers\Controller;
use Throwable;

class ErrorController extends Controller
{
    protected function renderView(array $data)
    {
        $exists = $this->twig->getLoader()->exists('error.twig');
        if ($exists)
        {
            return $this->twig->render('error.twig', $data);
        }
        else return $this->createRaw($data);
    }

    protected function createRaw(array $data)
    {
        $title = $data['heading'];
        $heading = $data['heading'];
        $str = '<html><head><title>'.$title.'</title></head>';
        $str .= '<body><h1>'.$heading.'</h1><hr>';
        if ($data['details']['code'])
        {
            $str .= '<p>Error Code: '.$data['details']['code'].'</p>';
        }
        else 
        {
            $str .= '<p>The server has encountered an unknown error </p>';
        }
        if ($data['details']['message'])
        {
            $str .= '<p>'.$data['details']['message'].'</p>';
        }
        else 
        {
            $str .= '<p>Please contact the administrator if this issue persists.</p>';
        }
        if ($data['trace'])
        {
            $str .= '<ul>';
            foreach($data['trace'] as $item)
            {
                $str .= $this->explodeTrace($item);
            }
            $str .= '</ul>';
        }
        $str .= '</body></html>';
        return $str;
    }

    protected function explodeTrace($item)
    {
        return '<li>'.implode(', ', $item).'</li>';
    }

    public function renderException(Throwable $exception, bool $displayErrorDetails)
    {
        if ($displayErrorDetails)
        {
            $heading = "Application Error";
            $rootPath = $this->app->path("");
            $file = str_replace($rootPath, " ", $exception->getFile());
            $line = $exception->getLine();
            $location = "{$file}, Line #{$line}";
            $details = 
            [
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'location' => $location
            ];
            $trace = $exception->getTrace();
            return $this->renderView(['heading' => $heading, 'details' => $details, 'trace' => $trace]);
        }
        else
        {
            $heading = "Application Error";
            $details = 
            [
                'message' => '',
                'code' => '',
                'location' => ''
            ];
            $trace = [];
            return $this->renderView(['heading' => $heading, 'details' => $details, 'trace' => $trace]);
        }
    }

}