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

class Callback implements Loadable
{
    /**
     * @var string
     */
    protected $_namespace = '';

    /**
     * @var string|array|\Closure
     */
    protected $_callback = null;


    /**
     * @throws \InvalidArgumentException
     * @param string $namespace
     * @param string|array|Closure $callback
     */
    public function __construct($namespace, $callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('Uncallable callback passed.');
        }

        $this->_namespace = trim($namespace, '\\');
        $this->_callback = $callback;
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
     * @param  $class
     * @return bool
     */
    public function load($class)
    {
        return (bool)call_user_func($this->_callback, $class);
    }
}