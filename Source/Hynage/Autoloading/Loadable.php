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