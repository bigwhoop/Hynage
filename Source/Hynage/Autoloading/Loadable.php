<?php
namespace Hynage\Autoloading;

interface Loadable
{
    /**
     * @param string $class
     * @return bool
     */
    public function canLoad($class);

    /**
     * @param string $class
     * @return bool
     */
    public function load($class);
}