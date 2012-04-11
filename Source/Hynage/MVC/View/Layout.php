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
    Hynage\Data\DataContainer;

class Layout extends AbstractView
{
    /**
     * @var string
     */
    protected $_content = null;

    /**
     * @var \Hynage\Data\DataContainer|null
     */
    protected $_metaTitle = null;

    /**
     * @var \Hynage\Data\DataContainer|null
     */
    protected $_metaKeywords = null;

    /**
     * @var \Hynage\Data\DataContainer|null
     */
    protected $_metaDescription = null;
    
    
    /**
     * Create the view
     * 
     * @param \Hynage\Config $config
     */
    public function __construct(Config $config)
    {
        parent::__construct($config);
        
        foreach ($config->get('params') as $key => $value) {
            $this->setParam($key, $value);
        }
    }
    
    
    /**
     * Set the layout's inner content
     * 
     * @param string $content
     * @return \Hynage\MVC\View\Layout
     */
    public function setContent($content)
    {
        $this->_content = $content;
        
        return $this;
    }
    
    
    /**
     * Return the layout's inner content
     * 
     * @return string
     */
    public function getContent()
    {
        return $this->_content;
    }


    /**
     * @return \Hynage\Data\DataContainer
     */
    public function getMetaTitle()
    {
        if (!$this->_metaTitle) {
            $this->_metaTitle = new DataContainer();
        }

        return $this->_metaTitle;
    }


    /**
     * @return \Hynage\Data\DataContainer
     */
    public function getMetaKeywords()
    {
        if (!$this->_metaKeywords) {
            $this->_metaKeywords = new DataContainer();
        }

        return $this->_metaKeywords;
    }


    /**
     * @return \Hynage\Data\DataContainer
     */
    public function getMetaDescription()
    {
        if (!$this->_metaDescription) {
            $this->_metaDescription = new DataContainer();
        }

        return $this->_metaDescription;
    }

    
    /**
     * Render the layout
     * 
     * @param bool $echo
     * @return string
     */
    public function render($echo = true)
    {
        $basePath = $this->getConfig()->get('basePath');
        $layout   = $this->getConfig()->get('layout');
        
        $path = $basePath . '/' . $layout;
        
        if ('/' == $path || !is_readable($path)) {
            throw new InvalidViewScriptException('Layout "' . $layout . '" was not found or could not be read at base path "' . $basePath . '".');
        }
        
        ob_start();
        include $path;
        $content = ob_get_clean();
        
        if ($echo) {
            echo $content;
        }
        
        return $content;
    }
}