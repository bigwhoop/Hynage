<?php
/**
 * This file is part of Hynage.
 *
 * (c) Philippe Gerber <philippe@bigwhoop.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Hynage;
use Hynage\MVC\Controller,
    Hynage\Autoloading,
    Hynage\HTTP;

class Application
{
    /**
     * @var \Hynage\Application
     */
    private static $_instance = null;
    
    /**
     * Holds the results of the bootstrapped components
     * 
     * @var array
     */
    protected $_components = array();

    /**
     * An array of the components that are currently
     * bootstrapped. Helps to detect recursion.
     *
     * @var array
     */
    protected $_currentComponents = array();
    
    /**
     * @var \Hynage\Config
     */
    protected $_config = null;

    /**
     * @var \SplObjectStorage
     */
    protected $_autoloaders = null;
    
    
    /**
     * Return the only instance of Hynage\Application
     * 
     * @param array|string|Hynage\Config|null $config
     * @return \Hynage\Application
     */
    public static function getInstance($config = null)
    {
        if (!self::$_instance) {
            $class = get_called_class();
            self::$_instance = new $class($config);
        }
        
        return self::$_instance;
    }


    /**
     * Put errors into an ErrorException object an throw it at the clown.
     *
     * @throws \ErrorException
     * @param int $severity
     * @param string $message
     * @param string $filename
     * @param int $line
     */
    public function handleError($severity, $message, $filename, $line)
    {
        if (error_reporting() === 0) {
            return;
        }
        
        if (error_reporting() & $severity) {
            throw new \ErrorException($message, 0, $severity, $filename, $line);
        } 
    }


    /**
     * @param \Exception $e
     */
    public function handleException(\Exception $e)
    {
        if (php_sapi_name() == 'cli') {
            exit((string)$e);
        }
        
        try {
            $config = $this->getConfig();

            $errorUrl = sprintf(
                "/%s/%s",
                $config->get('frontController.errors.controller', 'errors'),
                $config->get('frontController.errors.action', 'error')
            );

            $request = new HTTP\Request($errorUrl);
            $request->setParam('exception', $e);
            $this->bootstrap('Frontcontroller')->dispatch($request);
        } catch (\Exception $e2) {
            exit($e);
        }
    }
    
    
    /**
     * Private constructor
     * 
     * @param array|Hynage\Config|string|null $config
     */
    private function __construct($config)
    {
        $this->_autoloaders = new \SplObjectStorage();

        if (null !== $config) {
            $this->setConfig($config);
        }
    }
    
    
    /**
     * Set the configuration
     * 
     * @param array|string|Hynage\Config $config
     * @return \Hynage\Application
     */
    public function setConfig($config)
    {
        $this->bootstrap('autoloader');

        if (is_array($config)) {
            $config = new Config($config);
        } elseif (is_string($config)) {
            $path = $config;
            
            if (!file_exists($path)) {
                throw new Config\Exception('Config file "' . $path . '" does not exist.');
            }
            
            $config = require $path;
            if (is_array($config)) {
                $config = new Config($config);
            } elseif (!$config instanceof Config) {
                throw new Config\Exception('Config file "' . realpath($path) . '" must return an array or an instance of Hynage\Config.');
            }
        } elseif (!$config instanceof Config) {
            throw new Config\Exception('Config must be an array, an instance of Hynage\Config or a string to a config file.');
        }
        
        $this->_config = $config;

        // Constants
        $constants = $config->get('constants', new Config())->getData();
        if (!empty($constants)) {
            foreach ($constants as $key => $value) {
                define($key, $value);
            }
        }

        // User include paths
        $userIncludePaths = $config->get('includePaths', new Config())->getData();
        if (!empty($userIncludePaths)) {
            $this->addIncludePath($userIncludePaths);
        }

        // User autoloaders
        $callbacks = $this->getConfig()->get('autoloaders', new Config())->getData();
        foreach ($callbacks as $loader) {
            $this->addAutoloader($loader);
        }
        
        return $this;
    }
    
    
    /**
     * Return configuration
     * 
     * @return \Hynage\Config
     */
    public function getConfig()
    {
        return $this->_config;
    }


    /**
     * Add one or multiple include paths
     *
     * @param string|array $userPaths
     * @return \Hynage\Application
     */
    public function addIncludePath($userPaths)
    {
        $paths = explode(PATH_SEPARATOR, get_include_path());

        foreach ((array)$userPaths as $userPath) {
            $paths[] = $userPath;
        }

        set_include_path(join(PATH_SEPARATOR, $paths));

        return $this;
    }


    /**
     * Add an autoloader
     *
     * @param \Hynage\Autoloading\Loadable $loader
     * @return \Hynage\Application
     */
    public function addAutoloader(Autoloading\Loadable $loader)
    {
        $this->_autoloaders->attach($loader);
        return $this;
    }


    /**
     * Try to load a class by the defined autoloaders
     *
     * @param string $class
     */
    public function loadClass($class)
    {
        foreach ($this->_autoloaders as $loader) {
            if ($loader->canLoad($class)) {
                return $loader->load($class);
            }
        }

        return false;
    }


    /**
     * Bootstrap one, several or all components. A component is a protected
     * method of this class starting with '_init'. Each component is only
     * bootstrapped once.
     *
     * @param string|array|null $components
     * @return mixed
     */
    public function bootstrap($components = null)
    {
        if (null === $components) {
            $components = $this->_getAllComponents();
        } elseif (is_string($components)) {
            $components = array($components);
        } elseif (!is_array($components)) {
            throw new Exception('Argument "components" must either be a string, an array or null to bootstrap all components.');
        }

        $components = array_map('strtolower', $components);
        $components = array_unique($components);

        foreach ($components as $component) {
            $method = '_init' . ucfirst($component);

            // Component is already being bootstrapped
            if (array_key_exists($component, $this->_currentComponents)) {
                throw new Exception('Recursion detected.');
            }

            // Component already bootstrapped
            if (array_key_exists($component, $this->_components)) {
                continue;
            }

            // Check if the method exists
            if (!method_exists($this, $method)) {
                throw new Exception('Invalid component "' . $component . '" detected.');
            }

            $this->_currentComponents[$component] = true;
            $this->_components[$component] = $this->$method();
            unset($this->_currentComponents[$component]);
        }

        $results = array();
        foreach ($components as $component) {
            $results[$component] = $this->_components[$component];
        }

        return 1 == count($results) ? current($results) : $results;
    }


    /**
     * Dispatch a request
     *
     * @param \Hynage\HTTP\Request|null $request
     */
    public function dispatch(HTTP\Request $request = null)
    {
        $this->bootstrap('frontcontroller')->dispatch($request);
    }
    
    
    /**
     * Init the autoloader
     */
    protected function _initAutoloader()
    {
        $this->bootstrap('includepath');

        require 'Hynage/Autoloading/Loadable.php';
        require 'Hynage/Autoloading/NamespaceToDirectory.php';
        $this->addAutoloader(new Autoloading\NamespaceToDirectory('Hynage'));
        
        spl_autoload_register(array(get_called_class(), 'loadClass'));
    }
    

    /**
     * Set the path constants
     */
    protected function _initPathconstants()
    {
        define('HYNAGE_PATH', realpath(__DIR__ . '/..'));
    }
    

    /**
     * Prepare the include path
     */
    protected function _initIncludepath()
    {
        $this->bootstrap('pathconstants');

        $this->addIncludePath(HYNAGE_PATH);
    }
    
    
    /**
     * Set our own error handler
     */
    protected function _initErrorHandler()
    {
        set_error_handler(array($this, 'handleError'));
    }


    /**
     * Set our own exception handler
     */
    protected function _initExceptionHandler()
    {
        set_exception_handler(array($this, 'handleException'));
    }
    
    
    /**
     * Init the database connection
     * 
     * @return PDO
     */
    protected function _initDatabase()
    {
        $uri = $this->getConfig()->database->uri;
        return new Database\Connection($uri);
    }
    
    
    /**
     * Check the config file for a "php settings" section.
     * The does a quick validation and sets them.
     * 
     * @return array All changed settings with their new value
     */
    protected function _initPhpsettings()
    {
        $settings = $this->getConfig()->phpSettings;
        if (!$settings) {
            return array();
        }
        
        $allowedKeys = array_keys(ini_get_all());
        $changedSettings = array();
        
        foreach ($settings as $key => $value) {
            if (in_array($key, $allowedKeys)) {
                ini_set($key, $value);
                $changedSettings[$key] = $value;
            }
        }
        
        return $changedSettings;
    }
    
    
    /**
     * Start the session
     */
    protected function _initSession()
    {
        session_start();
    }
    
    
    /**
     * Init the front controller and set the default controller
     * and default action.
     * 
     * @return \Hynage\MVC\Controller\Front
     */
    protected function _initFrontcontroller()
    {
        $this->bootstrap(array('autoloader', 'session', 'errorhandler', 'exceptionhandler'));
        
        $config = $this->getConfig();
        
        $front = new MVC\Controller\Front($this);
        $front->setController($config->get('frontController.defaults.controller', 'index'))
              ->setAction($config->get('frontController.defaults.action', 'index'));
        
        return $front;
    }
    
    
    /**
     * Scan this class for all methods that are protected and start with '_init'.
     * 
     * @return array
     */
    protected function _getAllComponents()
    {
        $class   = new \ReflectionClass(get_class($this));
        $methods = $class->getMethods(\ReflectionMethod::IS_PROTECTED);
        
        $components = array();
        foreach ($methods as $method) {
            $method = $method->name;
            
            if (0 === strpos($method, '_init')) {
                $components[] = substr($method, strlen('_init'));
            }
        }
        
        return $components;
    }
    
    
    /**
     * Private cloner
     */
    private function __clone()
    {}
}