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
use Hynage\Config,
    Hynage\MVC\Controller\Front;

class FrontController extends AbstractComponent
{
    /**
     * @var \Hynage\Config|null
     */
    private $config = null;


    /**
     * @param \Hynage\Config|null $config
     */
    public function __construct(Config $config = null)
    {
        if (!$config) {
            $config = new Config();
        }
        
        $this->config = $config;
    }


    /**
     * @return \Hynage\MVC\Controller\Front
     */
    public function bootstrap()
    {
        $front = new Front($this->application);
        $front->setController($this->config->get('defaults.controller', 'index'))
              ->setAction($this->config->get('defaults.action', 'index'));

        return $front;
    }
}
