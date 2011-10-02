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
use Hynage\Config;

class PHPUnitApplication extends AbstractApplication
{
    public function setUp()
    {
        $config = $this->getConfig();

        $errorHandler     = new Component\ErrorHandler();
        $exceptionHandler = new Component\ExceptionHandler();
        $pathConstants    = new Component\PathConstants();
        $i18n             = new Component\I18n();

        $autoloader = new Component\Autoloader();
        foreach ($config->get('autoloaders', new Config()) as $userAutoloader) {
            $autoloader->addAutoloader($userAutoloader);
        }

        $includePath = new Component\IncludePath();
        foreach ($config->get('includePaths', new Config()) as $userPath) {
            $includePath->addIncludePath($userPath);
        }

        $phpSettings = new Component\PHPSettings();
        foreach ($config->get('phpSettings', new Config()) as $key => $value) {
            $phpSettings->addSetting($key, $value);
        }

        $this->addComponent('autoloader', $autoloader)
             ->addComponent('errorHandler', $errorHandler)
             ->addComponent('exceptionHandler', $exceptionHandler)
             ->addComponent('pathConstants', $pathConstants)
             ->addComponent('includePath', $includePath, array('pathConstants', 'phpSettings'))
             ->addComponent('phpSettings', $phpSettings)
             ->addComponent('i18n', $i18n);
    }


    /**
     * Bootstrap everything
     */
    public function dispatch()
    {
        $this->bootstrap();
    }
}
