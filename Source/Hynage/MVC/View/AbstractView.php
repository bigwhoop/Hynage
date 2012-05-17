<?php
/**
 * This file is part of Hynage.
 *
 * (c) Philippe Gerber <philippe@bigwhoop.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Hynage\MVC\View;
use Hynage\Config,
    Hynage\MVC\Controller\Front,
    Hynage\I18n\Translator;

abstract class AbstractView
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
     * @var Translator|null
     */
    private $translator = null;
    
    
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
     * @return \Hynage\MVC\View\AbstractView
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
     * @return \Hynage\MVC\View\AbstractView
     */
    public function setParam($key, $value)
    {
        $this->_params[$key] = $value;
        
        return $this;
    }
    
    
    /**
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }


    /**
     * Helper function to format a datetime object
     *
     * @param \DateTime $dt
     * @return string
     */
    protected function dateTime(\DateTime $dt)
    {
        return $dt->format('d.m.Y H:i');
    }


    /**
     * @param string $string
     * @return string
     */
    protected function escape($string)
    {
        return htmlentities($string);
    }


    /**
     * @param mixed $value
     * @param mixed $ifEmptyValue
     * @param mixed $ifNotEmptyValue
     * @return mixed
     */
    protected function emptyThen($value, $ifEmptyValue, $ifNotEmptyValue = null)
    {
        if (null === $ifNotEmptyValue) {
            $ifNotEmptyValue = $value;
        }

        return empty($value) ? $ifEmptyValue : $ifNotEmptyValue;
    }
    
    
    /**
     * @param \Hynage\I18n\Translator $translator
     * @return AbstractView
     */
    public function setTranslator(Translator $translator)
    {
        $this->translator = $translator;
        return $this;
    }


    /**
     * Returns the translation of the given string
     *
     * @param string $string [more arguments for printf-like placeholders)
     * @return string
     */
    public function _($string)
    {
        if (!$this->translator) {
            throw new \RuntimeException('No translator available.'); 
        }
        
        $args = func_get_args();
        return call_user_func_array(array($this->translator, 'translate'), $args);
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