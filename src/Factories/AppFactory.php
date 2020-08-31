<?php

namespace RTNatePHP\BasicApp\Factories;

use NateMakesStuff\Exceptions\HTMLExceptionRenderer;
use NateMakesStuff\Middleware\Session;
use RTNatePHP\BasicApp\BasicApp;
use RTNatePHP\BasicApp\Database\ConnectionFactory;
use RTNatePHP\BasicApp\Helpers\ConfigHelper;
use Throwable;

final class AppFactory
{
    protected $appDirectory;
    protected $slimFactory;
    protected $containerFactory;
    protected $dotenv;
    protected $debug = false;
    protected $basePath = '';

    static protected $app;

    static public function getInstance()
    {
        return self::$app;
    }

    protected function buildContainer()
    {
        $this->containerFactory = new ContainerFactory;
        //Load configuration from the app directory
        $config = $this->getConfiguration();
        $this->containerFactory->add(['config' => $config]);
        //Load providers from the app directory
        $providers = $this->getProviders($config);
        $this->containerFactory->add($providers);
        //Build the container
        $container = $this->containerFactory->build();
        return $container;
    }

    protected function enableDebuggingIfSet($container)
    {
        $config = $container->get(ConfigHelper::class);
        $debug = $config['debug'];
        $this->basePath = $config->get('site.base', '/');
        if ($debug){
            $this->debug = true;
        }
        return $this->debug;
    }

    protected function buildSlim($container)
    {
        try{
            $this->slimFactory = new SlimFactory($container, ['debug' => $this->debug]);
            $this->slimFactory->addGlobalMiddleware(Session::class);
            $this->slimFactory->addErrorRenderer('text/html', HTMLExceptionRenderer::class);
            $slim =  $this->slimFactory->build();
            $slim->setBasePath($this->basePath);
            return $slim;
        }
        catch(\Throwable $e)
        {
            if ($this->debug) throw new \Exception($e);
            else throw new \Exception('Error building Slim instance');
        }
    }

    static protected function validateAppRoot(string $path)
    {
        $validAppRoot = true;
        //Check That The App Root is Set and Valid
        if (!file_exists($path)) $validAppRoot = false;
        if (!file_exists($path."/vendor")) $validAppRoot = false;
        //Check if APP_ROOT is defined, if not export error and die
        if (!$validAppRoot) throw new \Exception('Factory $appRoute not set correctly.');
    }

    static public function build(string $appRoot)
    {
        $showDetails = false;
        //Wrap the factory in a try/catch to so we can display errors if the app fails to build
        try{
            self::$app = null;
            //Validate the appRoot as provided
            self::validateAppRoot($appRoot);
            //Create a factory instance
            $factory = new static($appRoot);
            //Load environment variables
            $factory->loadEnvironment();
            //Start building the container
            $container = $factory->buildContainer();
            //Enable debugging if debugging is set
            $debug = $factory->enableDebuggingIfSet($container);
            if ($debug) $showDetails = true;
            //Build Slim
            $slim = $factory->buildSlim($container);
            //Create the Database Connection
            ConnectionFactory::create($container);
            //Create the App
            $app = new BasicApp($container, $slim);
            self::$app = $app;
            return $app;
        }
        catch(Throwable $exception)
        {
            //Always Show details for this file
            if ($exception->getFile() == __FILE__) $showDetails = true;
            http_response_code(500);
            if ($showDetails){
                echo '<h1>Error 500</h1>';
                echo "<p>App Failed to build. Exception:  ".$exception->getMessage()."</p>";
                echo "<p>Please contact the administrator if this issue persists.</p>";
            }
            die;
        }
    }

    private function __construct($appDirectory)
    {
        $this->appDirectory = APP_ROOT;
    }

    private function loadEnvironment()
    {
        $this->dotenv = \Dotenv\Dotenv::createImmutable($this->appDirectory);
        $this->dotenv->load();
        //Set the app root Environment Tag
        if (!getenv('PATH_ROOT')) putenv("PATH_ROOT={$this->appDirectory}");
    }

    private function getConfiguration()
    {
        $dir = $this->appDirectory;
        $defaultConfigurationFile = __DIR__."/../Config/default.php";
        if (!file_exists($defaultConfigurationFile))
        {
            throw new \Exception("Default configuration file is missing.");
        }
        $config = include($defaultConfigurationFile);
        if (!is_array($config)) throw new \Exception("Default configuration should return an array");
        $userConfig = getenv('PATH_CONFIG');
        if (!$userConfig) $userConfig = '/source/config.php';
        if (file_exists($dir.$userConfig))
        {
            $user = include($dir.$userConfig);
            if (!is_array($user)) throw new \Exception("User configuration file should return an array");
            $config = array_merge_recursive($config, $user);
        }
        return $config;
    }

    private function getProviders()
    {
        $providersPath = __DIR__."/../Providers/default.php";
        if (!file_exists($providersPath)) throw new \Exception("Default providers file is missing.");
        $providers = include($providersPath);
        if (!is_array($providers)) throw new \Exception("Default providers file should return an array.");
        //TODO: Load User Providers
        $dir = $this->appDirectory;
        $userProviders = [];
        $userProvidersFile = getenv('PATH_PROVIDERS');
        if (!$userProvidersFile) $userProvidersFile = '/source/Providers.php';
        if (file_exists($dir.$userProvidersFile))
        {
            $user = include($dir.$userProvidersFile);
            if (!is_array($user)) throw new \Exception("User providers file should return an array");
            $userProviders = $user;
        }
        return array_merge($providers, $userProviders);
    }


    
}