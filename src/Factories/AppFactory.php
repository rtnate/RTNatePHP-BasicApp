<?php
/**
 * App Factory Class Definition
  
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

use RTNatePHP\BasicApp\Middleware\Session;
use RTNatePHP\BasicApp\BasicApp;
use RTNatePHP\BasicApp\Database\ConnectionFactory;
use RTNatePHP\BasicApp\Helpers\ConfigHelper;
use RTNatePHP\BasicApp\Exception\HTMLExceptionRenderer;
use Throwable;


/**
 * Factory class that builds the BasicApp instance
 * 
 * Project should call build from index.php to instantiate an app.
 * 
 * @uses \DI\Container
 * @uses Psr\Container\ContainerInterface
 */
final class AppFactory
{
    /************************************/
    /*         Static Members           */
    /************************************/

    /**
     * The global BasicApp instance.  
     *
     * @var BasicApp
     */
    static protected $app = null;

    /**
     * Get the BasicApp instance (or derivative) that was built by this factory
     *
     * @return BasicApp
     */
    static public function getInstance()
    {
        return self::$app;
    }

    /**
     * Build an app using this factory.
     * 
     * Call this function to build a Basic App instance.
     * On a failue to build, the app will echo an error page and then PHP will die.
     *
     * @param string $appRoot   The absolute path to the app's root directory.
     *                          This is the folder where composer.json and /vendor 
     *                          are located.
     * 
     * @param string $appClass  (optional) The app class to instantiate.
     *                          This defaults to BasicApp, but an app-specific
     *                          derived class may be used instead
     * 
     * @return BasicApp The basic app instance (or derived class instance)
     * 
     */
    static public function build(string $appRoot, string $appClass = BasicApp::class)
    {
        $showDetails = false;
        //The app build process is wrapped in a try, catch loop.
        //If the app failes to build completely, an exception will be thrown
        //This is then caught an a basic error message will be returned as a response
        //and then the app will die.
        try{
            self::$app = null;
            //Validate the appRoot as provided
            self::validateAppRoot($appRoot);
            //Create a factory instance
            $factory = new static($appRoot, $appClass);
            //Load environment variables
            $factory->loadEnvironment();
            //Start building the container
            $container = $factory->buildContainer();
            //Prepare the app class for instantiation
            //This enabled pre-build hooks to run
            $appClass::prepare($container);
            //Enable debugging if debugging is set
            $debug = $factory->enableDebuggingAndLoadBasePath($container);
            if ($debug) $showDetails = true;
            //Build Slim
            $slim = $factory->buildSlim($container);
            //Create the Database Connection
            ConnectionFactory::create($container);
            //Create the App
            $app = new $appClass($container, $slim);
            self::$app = $app;
            return $app;
        }
        catch(Throwable $exception)
        {
            //Always Show details for this file
            self::logExceptionAndDie($exception);
        }
    }

    /**
     * Validate the provided App Root path.  
     * This checks to make sure the App Root actually exists and composer
     * has installed a /vendor directory
     *
     * @param string $path The app root abosule file path on the server
     * @throws Exception If the path is not a valid path
     * @return void
     */
    static protected function validateAppRoot(string $path)
    {
        $validAppRoot = true;
        //Verify the app root path actually exists
        if (!file_exists($path)) $validAppRoot = false;
        //Verify the vendor directory in the app root also exists
        if (!file_exists($path."/vendor")) $validAppRoot = false;
        //If either of these tests fail, throw an exception
        if (!$validAppRoot) throw new \Exception('Factory $appRoute not set correctly.');
    }

    /**
     * Handles an exception while building the app/
     * This allows exceptions encountered before error-handling is
     * booted to be dealt with (somewhat) gracefully
     * 
     * This fucntion will output a bare-bones error page and 
     * then PHP will die.
     *
     * @param Throwable $exception
     * @return void
     */
    private static function logExceptionAndDie(Throwable $exception)
    {
        //If the exception was thrown from this file, show the message.
        //Error messages in this class should be user-safe.
        if ($exception->getFile() == __FILE__) $showDetails = true;
        //Set HTTP status code to 500 (internal server error)
        http_response_code(500);
        //Echo a basic error page as a response
        echo '<html><head><title>Error</title></head><body>';
        echo '<h1>Error 500</h1>';
        //If it is safe to show details, display the exception message
        if ($showDetails)
            echo "<p>App Failed to build. Exception:  ".$exception->getMessage()."</p>";
        //Otherwise display this generic message.
        //[code: 33] is simply a clue that the exception was caught and handled here
        else 
            echo '<p>The server has encountered a fatal error [code: 33]</p>';
        //Finish echoing the page and then die
        echo "<p>Please contact the administrator if this issue persists.</p>";
        echo '</body></html>';
        die;
    } 

    /************************************/
    /*     Class Instance Members       */
    /************************************/

    /**
     * The App's root directory
     *
     * @var string
     */
    protected $appDirectory;

    /**
     * The App's full class name
     *
     * @var string
     */
    protected $appClass;

    /**
     * The slim factory instance
     *
     * @var SlimFactory
     */
    protected $slimFactory;

    /**
     * The container factory instance
     *
     * @var ContainerFactory
     */
    protected $containerFactory;

    /**
     * The Dotenv instance
     *
     * @var \Dotenv\Dotenv
     */
    protected $dotenv;

    /**
     * App debug flag
     *
     * @var boolean
     */
    protected $debug = false;

    /**
     * App routing url base path
     * 
     * @var string
     */
    protected $basePath = '';


    /**
     * Construct a new AppFactory instance.
     * 
     * This function is private - use build() to create a
     * new app.
     *
     * @param string $appDirectory The app's root directory
     * @param string $appClass The app to be instantiated's class name
     */
    private function __construct(string $appDirectory, string $appClass)
    {
        $this->appDirectory = $appDirectory;
        $this->appClass = $appClass;
    }

    /**
     * Build the PHP-DI container for the app
     *
     * @return \DI\Container
     */
    protected function buildContainer()
    {
        //Create a new container factory
        $this->containerFactory = new ContainerFactory;
        //Load configuration from the app directory
        $config = $this->getConfiguration();
        //Add the configuration to the container factory
        $this->containerFactory->add(['config' => $config]);
        //Load providers from the app directory
        $providers = $this->getProviders($config);
        //Add the various providers to the container
        $this->containerFactory->add($providers);
        //Add the derived class instance to the container
        $this->containerFactory->add([$this->appClass => function(){ return self::getInstance(); }]);
        //Build the container
        $container = $this->containerFactory->build();
        return $container;
    }

    /**
     * Enables debugging if set in configuration and loads the app's
     * base path from the configuration
     *
     * @param \Psr\Container\ContainerInterface $container
     * @return bool True if debug has been enabled
     */
    protected function enableDebuggingAndLoadBasePath($container)
    {
        $config = $container->get(ConfigHelper::class);
        $debug = $config['debug'];
        if ($debug) $this->debug = true;
        $this->basePath = $config->get('site.base', '/');
        return $this->debug;
    }

    /**
     * Builds the underlying slim app instance
     *
     * @param \DI\Container $container
     * @throws Exception on error building the slim instance
     * @return \Slim\App
     */
    protected function buildSlim($container)
    {
        //Build the slim instance
        //This is wrapped in try/catch so if the Slim app fails
        //to build the exception can be caught and reformatted
        try
        {
            //Create a new SlimFactory
            $this->slimFactory = new SlimFactory($container, ['debug' => $this->debug]);
            //Add the global session middleware
            $this->slimFactory->addGlobalMiddleware(Session::class);
            //Add the HTML Exception rendered to error handling
            $this->slimFactory->addErrorRenderer('text/html', HTMLExceptionRenderer::class);
            //Build the slim instance
            $slim =  $this->slimFactory->build();
            //Set the slim base path to this app's configured base path
            $slim->setBasePath($this->basePath);
            return $slim;
        }
        catch(\Throwable $e)
        {
            //If debugging is enabled, re-throw the excpetion
            //with complete error details
            if ($this->debug) throw new \Exception($e);
            //Otherwise throw a generic, safe error message
            else throw new \Exception('Error building Slim instance');
        }
    }

    /**
     * Boots and loads the .env file using phpdotenv
     *
     * @return void
     */
    private function loadEnvironment()
    {
        //Create and load the Dotenv instance
        $this->dotenv = \Dotenv\Dotenv::createImmutable($this->appDirectory);
        $this->dotenv->load();
        //Set the PATH_ROOT Environment Tag if not set
        if (!getenv('PATH_ROOT')) putenv("PATH_ROOT={$this->appDirectory}");
    }

    /**
     * Loads the app configuration from the configuration files.
     * 
     * The app gets its configuration from three places: 
     *  - The .env file in the app directory
     *  - The default configuration file (../Config/default.php)
     *  - The app specific configuration file (/source/config.php) or PATH_CONFIG
     * 
     *  The default configuration file loads all .env variables into the app's
     *  configuration array.  This is then merged with the app-specific config file
     *  which is located at APP_ROOT/source/config or has set by the PATH_CONFIG .env 
     *  settings.
     * 
     * @return array The app's configuration
     */
    private function getConfiguration()
    {
        //First verify that the default configuration file exists.
        //If it does not, throw an exception as that file is required for boot.
        $dir = $this->appDirectory;
        $defaultConfigurationFile = __DIR__."/../Config/default.php";
        if (!file_exists($defaultConfigurationFile))
        {
            throw new \Exception("Default configuration file is missing.");
        }
        //If the file does exist, load it via include()
        $config = include($defaultConfigurationFile);
        //If the file does not return an array, the file is ill-formed 
        //Throw an exception as the app cannot be built without valid configuration
        if (!is_array($config)) throw new \Exception("Default configuration should return an array");
        //Get the user (app-specific) config file location from the environment
        $userConfig = getenv('PATH_CONFIG');
        //If a location as not been set, default to /source/config.php
        if (!$userConfig) $userConfig = '/source/config.php';
        //If a user configuration file exists, load it
        if (file_exists($dir.$userConfig))
        {
            //Load and verify the user configuration file and throw and exception if it is ill-formed
            $user = include($dir.$userConfig);
            if (!is_array($user)) throw new \Exception("User configuration file should return an array");
            //If the user configuration loads, merge it recursively into the default configuration
            $config = array_merge_recursive($config, $user);
        }
        //Return this configuration for injection into the container
        return $config;
    }

    /**
     * Loads the app's DI providers from the provider files.
     * This is where PHP-DI definintions are created and loaded.
     * 
     * The app loads providers from two places: 
     *  - The default providers file (../Providers/default.php)
     *  - The user providers file (/source/Providers.php or PATH_PROVIDERS)
     *
     * @see http://php-di.org/doc/definition.html
     * @return array The PHP-DI class provider definitions
     */
    private function getProviders()
    {
        //First verify the existance of the default providers path
        //If this file doesn't exist, throw an exception as the app can't boot without it
        $providersPath = __DIR__."/../Providers/default.php";
        if (!file_exists($providersPath)) throw new \Exception("Default providers file is missing.");
        //Load the file using include()
        $providers = include($providersPath);
        //If the file is ill-formed (doesn't return an array) throw an exception as the app can't
        //boot if this file is ill-formed
        if (!is_array($providers)) throw new \Exception("Default providers file should return an array.");
        //TODO: Polish User Provider Loading (add Flexibility)
        $dir = $this->appDirectory;
        $userProviders = [];
        //Find the user providers file either from PATH_PROVIDERS environment variable 
        //Or use /source/Providers.php by default
        $userProvidersFile = getenv('PATH_PROVIDERS');
        if (!$userProvidersFile) $userProvidersFile = '/source/Providers.php';
        //If a user providers file exists, load it
        if (file_exists($dir.$userProvidersFile))
        {
            //Load the file using include()
            $user = include($dir.$userProvidersFile);
            //If the file is ill-formed (doesn't return an array) throw an exception as the app can't
            //boot if this file is ill-formed
            if (!is_array($user)) throw new \Exception("User providers file should return an array");
            $userProviders = $user;
        }
        //Merge any loaded user providers with the defaults and return the array
        return array_merge($providers, $userProviders);
    }


    
}