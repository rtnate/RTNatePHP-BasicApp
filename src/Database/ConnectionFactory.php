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
        //If database and username configuration isn't set
        //Do not boot the database
        if ($connection['database'] == 'donotuse' 
            && $connection['username'] == 'donotuse')
        {
            return;
        }
        self::$capsule = new Capsule;
        self::$capsule->addConnection($connection);
        self::$capsule->setAsGlobal();
        self::$capsule->bootEloquent();
    }

    static public function getCapsuleOrFail()
    {
        if (self::$capsule ==  null) throw new \Exception('Database has not been configured');
        return self::$capsule;
    }

    static public function getCapsule(){ return self::$capsule; }

}