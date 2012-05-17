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
use Hynage\Application\ApplicationInterface;

abstract class AbstractComponent implements ComponentInterface
{
    /**
     * @var null|\Hynage\Application\ApplicationInterface
     */
    protected $application = null;
    
    
    /**
     * @param \Hynage\Application\ApplicationInterface $application
     */
    public function setApplication(ApplicationInterface $application)
    {
        $this->application = $application;
    }
}
