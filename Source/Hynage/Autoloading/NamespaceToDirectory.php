<?php
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