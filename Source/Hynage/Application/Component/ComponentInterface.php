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
    Hynage\Application\ApplicationInterface;

interface ComponentInterface
{
    public function bootstrap();
    
    /**
     * @param \Hynage\Application\ApplicationInterface $application
     */
    public function setApplication(ApplicationInterface $application);
}