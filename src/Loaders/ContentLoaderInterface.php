<?php 

namespace RTNatePHP\BasicApp\Loaders;

interface ContentLoaderInterface 
{
    public function valid(): bool;

    public function content(): string;

    public function data(): array;

    public function fileContents(): string;

    public function fileType(): string;

    public function html(string $containerNode = ''): string;

    public function info(): array;
}