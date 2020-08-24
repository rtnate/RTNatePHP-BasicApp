<?php

namespace RTNatePHP\BasicApp\Factories;

class ContainerFactory
{
    protected $builder;
    
    public function __construct()
    {
        $this->builder = new \DI\ContainerBuilder();
    }

    public function build()
    {
        return $this->builder->build();
    }

    public function add(array $definitions)
    {
        $this->builder->addDefinitions($definitions);
    }
}