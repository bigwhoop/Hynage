<?php
/**
 * This file is part of Hynage.
 *
 * (c) Philippe Gerber <philippe@bigwhoop.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Hynage\Application;
use Hynage\Config,
    Hynage\Application\Component\ComponentInterface;

abstract class AbstractApplication implements ApplicationInterface
{
    /**
     * Holds the components set for bootstrapping.
     * 
     * @var array
     */
    protected $_components = array();

    /**
     * Dependencies of components.
     *
     * @var array
     */
    protected $_dependencies = array();

    /**
     * An array of the components that are currently
     * bootstrapped. Helps to detect recursion.
     *
     * @var array
     */
    protected $_currentComponents = array();

    /**
     * An array of the results from bootstrapped
     * components
     *
     * @var array
     */
    protected $_componentResults = array();
    
    /**
     * @var \Hynage\Config
     */
    protected $_config = null;
    
    
    /**
     * Private constructor
     * 
     * @param array|Hynage\Config|string|null $config
     */
    public function __construct($config)
    {
        $this->setConfig($config);
    }
    
    
    /**
     * Set the configuration
     * 
     * @param array|string|Hynage\Config $config
     * @return \Hynage\Application\AbstractApplication
     */
    public function setConfig($config)
    {
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
     * @param string $name
     * @param Component\ComponentInterface $component
     * @param array $dependencies
     * @return AbstractApplication
     */
    public function setComponent($name, Component\ComponentInterface $component, array $dependencies = array())
    {
        $name = mb_strtolower($name);
        
        $this->_components[$name] = $component;

        if (!empty($dependencies)) {
            foreach ($dependencies as $dependency) {
                $this->addDependency($name, $dependency);
            }
        }

        return $this;
    }


    /**
     * @param string $name
     * @return false|Component\ComponentInterface
     */
    public function getComponent($name)
    {
        $name = mb_strtolower($name);

        if (!array_key_exists($name, $this->_components)) {
            return false;
        }

        return $this->_components[$name];
    }


    /**
     * @param string $componentName
     * @param string $dependency
     * @return AbstractApplication
     */
    public function addDependency($componentName, $dependency)
    {
        $componentName = mb_strtolower($componentName);

        if (!array_key_exists($componentName, $this->_dependencies)) {
            $this->_dependencies[$componentName] = array();
        }

        $this->_dependencies[$componentName][] = $dependency;
        return $this;
    }


    /**
     * @param string $componentName
     * @return array
     */
    public function getDependencies($componentName)
    {
        $componentName = mb_strtolower($componentName);

        if (!array_key_exists($componentName, $this->_dependencies)) {
            return array();
        }

        return $this->_dependencies[$componentName];
    }


    /**
     * Bootstrap one, several or all components. A component is a protected
     * method of this class starting with '_init'. Each component is only
     * bootstrapped once.
     *
     * @param string|array|null $componentNames
     * @return mixed
     */
    public function bootstrap($componentNames = null)
    {
        $this->setUp();

        if (null === $componentNames) {
            $componentNames = array_keys($this->_components);
        } elseif (is_string($componentNames)) {
            $componentNames = array($componentNames);
        } elseif (!is_array($componentNames)) {
            throw new Exception('Argument "components" must either be a string, an array or null to bootstrap all components.');
        }
        
        $componentNames = array_map('mb_strtolower', $componentNames);
        $componentNames = array_unique($componentNames);
        
        foreach ($componentNames as $componentName) {
            $component = $this->getComponent($componentName);
            if (!$component) {
                throw new Exception('Invalid component "' . $componentName . '" detected.');
            }

            // Component is already being bootstrapped
            if (array_key_exists($componentName, $this->_currentComponents)) {
                throw new Exception('Recursion detected.');
            }

            // Component already bootstrapped
            if (array_key_exists($componentName, $this->_componentResults)) {
                continue;
            }

            $this->_currentComponents[$componentName] = true;

            // Bootstrap dependencies
            if (count($dependencies = $this->getDependencies($componentName)) > 0) {
                $this->bootstrap($dependencies);
            }

            // Bootstrap component
            $component->setApplication($this);
            $this->preBootstrap($component);
            $this->_componentResults[$componentName] = $component->bootstrap();
            $this->postBootstrap($component);
            
            unset($this->_currentComponents[$componentName]);
        }

        $results = array();
        foreach ($componentNames as $componentName) {
            $results[$componentName] = $this->_componentResults[$componentName];
        }

        return 1 == count($results) ? current($results) : $results;
    }


    /**
     * Pre-bootstrap event
     *
     * @param Component\ComponentInterface $component
     */
    public function preBootstrap(ComponentInterface $component)
    {}


    /**
     * Post-bootstrap event
     *
     * @param Component\ComponentInterface $component
     */
    public function postBootstrap(ComponentInterface $component)
    {}
}
