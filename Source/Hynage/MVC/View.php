<?php
/**
 * This file is part of Hynage.
 *
 * (c) Philippe Gerber <philippe@bigwhoop.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Hynage\MVC;
use Hynage\Config as Config,
    Hynage\MVC\Controller\Front;

class View
{
    /**
     * @var array
     */
    protected $_params = array();
    
    /**
     * @var \Hynage\Config
     */
    protected $_config = null;

    /**
     * @var \Hynage\MVC\Controller\Front
     */
    protected $_frontController = null;
    
    
    /**
     * Create the view
     * 
     * @param \Hynage\Config $config
     */
    public function __construct(Config $config)
    {
        $this->_config = $config;
    }
    
    
    /**
     * Return the view configuration
     * 
     * @return \Hynage\Config
     */
    public function getConfig()
    {
        return $this->_config;
    }


    /**
     * @param \Hynage\MVC\Controller\Front $front
     * @return \Hynage\MVC\View
     */
    public function setFrontController(Front $front)
    {
        $this->_frontController = $front;
        return $this;
    }


    /**
     * @return \Hynage\MVC\Controller\Front|null
     */
    public function getFrontController()
    {
        return $this->_frontController;
    }
    
    
    /**
     * Set a param which is passed to the view script later
     * 
     * @param string $key
     * @param mixed $value
     * @return \Hynage\MVC\View
     */
    public function setParam($key, $value)
    {
        $this->_params[$key] = $value;
        
        return $this;
    }


    /**
     * Helper function to format a datetime object
     *
     * @param \DateTime $dt
     * @return string
     */
    protected function _dateTime(\DateTime $dt)
    {
        return $dt->format('d.m.Y H:i');
    }


    /**
     * @param mixed $value
     * @param mixed $ifEmptyValue
     * @param mixed $ifNotEmptyValue
     * @return mixed
     */
    protected function _emptyThen($value, $ifEmptyValue, $ifNotEmptyValue = null)
    {
        if (null === $ifNotEmptyValue) {
            $ifNotEmptyValue = $value;
        }

        return empty($value) ? $ifEmptyValue : $ifNotEmptyValue;
    }
    
    
    /**
     * Render a view script
     * 
     * @param string $script
     * @param bool $echo
     * @return string
     */
    public function render($script, $echo = true)
    {
        $basePath = $this->getConfig()->get('basePath');
        $path = $basePath . '/' . $script;
        
        if (!is_readable($path)) {
            throw new View\Exception('Script "' . $script . '" was not found or could not be read at base path "' . $basePath . '".');
        }

        ob_start();
        include $path;
        $content = ob_get_clean();
        
        if ($echo) {
            echo $content;
        }
        
        return $content;
    }


    /**
     * Returns the translation of the given string
     *
     * @param string $string
     * @param mixed Arguments for printf
     * @return string
     */
    public function _($string)
    {
        $args = func_get_args();
        return call_user_func_array(array('\Hynage\I18n\Translator', 'translate'), $args);
    }
    
    
    /**
     * Dynamic getter for view parameters
     * 
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        if (array_key_exists($key, $this->_params)) {
            return $this->_params[$key];
        }
        
        return null;
    }
}