<?php
/**
 * This file is part of Hynage.
 *
 * (c) Philippe Gerber <philippe@bigwhoop.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Hynage\Autoloading;

class NamespaceToDirectory implements Loadable
{
    /**
     * @var string
     */
    protected $_namespace = '';


    /**
     * @param string $namespace
     */
    public function __construct($namespace)
    {
        $this->_namespace = trim($namespace, '\\');
    }


    /**
     * @param $string $class
     * @return bool
     */
    public function canLoad($class)
    {
        $class = trim($class, '\\');
        return 0 === strpos($class, $this->_namespace);
    }


    /**
     * @param string $class
     * @return bool
     */
    public function load($class)
    {
        $path = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
        return (bool)require_once $path;
    }
}