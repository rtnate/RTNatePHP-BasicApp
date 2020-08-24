<?php 

namespace RTNatePHP\BasicApp\Database;

use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Container\ContainerInterface;
use RTNatePHP\BasicApp\Helpers\ConfigHelper;

class ConnectionFactory
{
    static protected $capsule = null;

    static public function create(ContainerInterface $ci)
    {
        self::$capsule = new Capsule;
        $config = new ConfigHelper($ci);
        $connection = 
        [
            "driver" => $config->get('db.connection'),
            "host" =>   $config->get('db.host'),
            "database" =>  $config->get('db.database'),
            "username" =>  $config->get('db.username'),
            "password" => $config->get('db.password'),
            "prefix" =>  $config->get('db.table_prefix')
        ];
        self::$capsule->addConnection($connection);
        self::$capsule->setAsGlobal();
        self::$capsule->bootEloquent();
    }

    static public function getCapsule(){ return self::$capsule; }

}