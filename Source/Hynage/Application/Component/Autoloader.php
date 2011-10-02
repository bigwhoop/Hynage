<?php
/**
 * This file is part of Hynage.
 *
 * (c) Philippe Gerber <philippe@bigwhoop.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Hynage\Application\Component;
use Hynage\Autoloader\Loadable,
    Hynage\Autoloader\Composite;

class Autoloader extends AbstractComponent
{
    /**
     * @var \Hynage\Autoloader\Composite
     */
    private $autoloader = null;


    public function __construct()
    {
        $this->autoloader = new Composite();
    }


    /**
     * @param \Hynage\Autoloader\Loadable $autoloader
     * @return Autoloader
     */
    public function addAutoloader(Loadable $autoloader)
    {
        $this->autoloader->addAutoloader($autoloader);
        return $this;
    }


    /**
     * @return \Hynage\Database\Connection
     */
    public function bootstrap()
    {
        $this->autoloader->register();
    }
}
