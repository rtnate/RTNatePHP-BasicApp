<?php 

namespace RTNatePHP\BasicApp\Loaders;

class ContentFileLoader 
{
    protected $loader;

    public function __construct(string $filename)
    {
        $path_parts = pathinfo($filename);
        $this->loader = $this->parseFile($filename, $path_parts['extension']);
    }

    protected function parseFile($filename, $ext): ContentLoaderInterface
    {
        switch($ext)
        {
            case "json":
                return new JsonLoader($filename);
            case "md":
                return new MarkdownLoader($filename);
            default:
                return new GenericFileLoader($filename);
        };
    }
}