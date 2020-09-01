<?php

/**
 * ConatinerFactory Class Definition
  
 * PHP version 5
 *
 * LICENSE: MIT (see ../../LICENSE) 
 *
 * @package    rtnatephp/basic-app
 * @author     Nate Taylor <nate@rtelectronix.com
 * @copyright  2020 - Nate Taylor
 * @license    MIT (see ../../LICENSE) 
 * @link       http://www.github.com/rtnate/RTNatePHP-BasicApp/
 *
 */

namespace RTNatePHP\BasicApp\Factories;

/**
 * Factory class for building the PHP-DI container
 * 
 * @see \DI\ContainerBuilder
 */
class ContainerFactory
{
    /**
     * PHP-DI ContainerBuilder instance
     *
     * @var \DI\ContainerBuilder
     */
    protected $builder;
    
    /**
     * Constuct a new ContainerFactory
     */
    public function __construct()
    {
        $this->builder = new \DI\ContainerBuilder();
    }

    /**
     * Build the container
     *
     * @return \DI\Container
     */
    public function build()
    {
        return $this->builder->build();
    }

    /**
     * Add PHP-DI definitions to the container
     *
     * @param array $definitions Definitions to add into the container
     * @return void
     * @see http://php-di.org/doc/definition.html
     */
    public function add(array $definitions)
    {
        $this->builder->addDefinitions($definitions);
    }
}