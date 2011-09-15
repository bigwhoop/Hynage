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

class Layout extends View
{
    /**
     * @var string
     */
    protected $_content = null;
    
    
    /**
     * Set the layout's inner content
     * 
     * @param string $content
     * @return \Hynage\MVC\Layout
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
            throw new Layout\Exception('Layout "' . $layout . '" was not found or could not be read at base path "' . $basePath . '".');
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