<?php
/**
 * This file is part of Hynage.
 *
 * (c) Philippe Gerber <philippe@bigwhoop.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Hynage\Autoloader;

class NamespaceToDirectory implements Loadable
{
    /**
     * @var string
     */
    private $namespace = '';

    /**
     * @var string|null
     */
    private $path = null;


    /**
     * @param string $namespace
     * @param string $path
     */
    public function __construct($namespace, $path = null)
    {
        $this->namespace = trim($namespace, '\\');
        $this->path      = $path;
    }


    /**
     * @param $string $class
     * @return bool
     */
    public function canLoad($class)
    {
        $class = trim($class, '\\');
        return 0 === strpos($class, $this->namespace);
    }


    /**
     * @param string $class
     * @return bool
     */
    public function load($class)
    {
        $path = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
        
        if ($this->path) {
            $path = $this->path . DIRECTORY_SEPARATOR . $path;
        }

        return (bool)require_once $path;
    }
}