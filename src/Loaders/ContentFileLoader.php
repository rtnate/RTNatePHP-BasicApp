<?php 

namespace RTNatePHP\BasicApp\Loaders;

class ContentFileLoader implements ContentLoaderInterface
{
    protected $loader;
    protected $path_parts = [];

    public function __construct(string $filename)
    {
        $this->path_parts = pathinfo($filename);
        $this->loader = $this->parseFile($filename, $this->path_parts['extension']);
    }

    public function info(): array 
    {
        return $this->path_parts;
    }

    public function valid(): bool
    {
        return $this->loader ? $this->loader->valid() : false;
    }

    public function content(): string
    {
        return $this->loader ? $this->loader->content() : '';
    }

    public function data(): array
    {
        return $this->loader ? $this->loader->data() : [];
    }

    public function fileContents(): string
    {
        return $this->loader ? $this->loader->fileContents() : '';
    }

    public function fileType(): string
    {
        return $this->loader ? $this->loader->fileType() : 'invalid';
    }

    public function html(string $containerNode = ''): string
    {
        if ($containerNode)
        {
            $tag = strtolower($containerNode);
            return $this->loader ? $this->loader->html($tag) : "<$tag></$tag>";
        }
        else return $this->loader ? $this->loader->html() : "";
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