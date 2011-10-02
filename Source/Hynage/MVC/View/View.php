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

class View extends AbstractView
{
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
            throw new InvalidViewScriptException('Script "' . $script . '" was not found or could not be read at base path "' . $basePath . '".');
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