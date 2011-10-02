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

class Composite implements Loadable
{
    /**
     * @var array
     */
    private $autoloaders = array();


    /**
     * @param Loadable $autoloader
     * @return Composite
     */
    public function addAutoloader(Loadable $autoloader)
    {
        $this->autoloaders[] = $autoloader;
        return $this;
    }


    /**
     * @param $string $class
     * @return bool
     */
    public function canLoad($class)
    {
        foreach ($this->autoloaders as $autoloader) {
            if ($autoloader->canLoad($class)) {
                return true;
            }
        }

        return false;
    }


    /**
     * @param string $class
     * @return bool
     */
    public function load($class)
    {
        foreach ($this->autoloaders as $autoloader) {
            if ($autoloader->canLoad($class)) {
                return $autoloader->load($class);
            }
        }

        return false;
    }


    /**
     * @return Composite
     */
    public function register()
    {
        spl_autoload_register(array($this, 'load'));
        return $this;
    }
}