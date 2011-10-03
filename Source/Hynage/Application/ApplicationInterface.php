<?php
/**
 * This file is part of Hynage.
 *
 * (c) Philippe Gerber <philippe@bigwhoop.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Hynage\Application;

interface ApplicationInterface
{
    public function setUp();
    public function bootstrap($componentNames = null);
    public function setConfig($config);
    public function getConfig();
}