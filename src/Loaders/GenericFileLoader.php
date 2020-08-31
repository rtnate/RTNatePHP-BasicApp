<?php 

namespace RTNatePHP\BasicApp\Loaders;

use Spatie\YamlFrontMatter\YamlFrontMatter;

class GenericFileLoader implements ContentLoaderInterface 
{
    protected $parser;
    protected $filename;
    protected $file_loaded = false;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
        if (file_exists($filename))
        {
            $contents = file_get_contents($filename);
            if ($contents === false) $this->parser = YamlFrontMatter::parse('');
            else
            {
                $this->parser = YamlFrontMatter::parse('');
                $file_loaded = true;
            }
        }
        else 
        {
            $this->parser = YamlFrontMatter::parse('');
        }
    }

    public function info(): array 
    {
        return pathinfo($this->filename);
    }

    public function valid(): bool
    {
        return $this->file_loaded;
    }

    public function content(): string
    {
        return $this->parser->body();
    }

    public function data(): array
    {
        return $this->parser->matter();
    }

    public function fileContents(): string
    {
        if ($this->file_loaded) return file_get_contents($this->filename);
        else return '';
    }

    public function fileType(): string
    {
        return "generic";
    }

    public function html(string $containerNode = ''): string
    {
        if ($containerNode)
        {
            $tag = strtolower($containerNode);
            return "<$tag>".$this->content()."</$tag>";
        }
        else return $this->content();
    }
}