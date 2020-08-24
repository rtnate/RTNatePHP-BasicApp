<?php 

namespace RTNatePHP\BasicApp\Loaders;

class JsonLoader implements ContentLoaderInterface 
{
    protected $filename;
    protected $file_contents = [];
    protected $file_loaded = false;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
        if (file_exists($filename))
        {
            $contents = file_get_contents($filename);
            if ($contents === false) return;
            else
            {
                $data = json_decode($contents, true);
                if ($data)
                {
                    $this->file_contents = $data;
                    $file_loaded = true;
                }
            }
        }
    }

    public function valid(): bool
    {
        return $this->file_loaded;
    }

    public function content(): string
    {
        return '';
    }

    public function data(): array
    {
        return $this->file_contents;
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

    public function fileContents(): string
    {
        if ($this->file_loaded) return file_get_contents($this->filename);
    }

    public function fileType(): string
    {
        return "json";
    }

}