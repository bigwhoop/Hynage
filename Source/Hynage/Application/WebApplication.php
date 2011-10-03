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
use Hynage\Config,
    Hynage\HTTP\Request;

class WebApplication extends AbstractApplication
{
    public function setUp()
    {
        $config = $this->getConfig();

        $errorHandler     = new Component\ErrorHandler();
        $exceptionHandler = new Component\ExceptionHandler($this, $config->get('frontController'));
        $pathConstants    = new Component\PathConstants();
        $database         = new Component\Database($config->get('database'));
        $session          = new Component\Session($config->get('session.lifetime', 0));
        $frontController  = new Component\FrontController($this, $config->get('frontController'));
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

        $this->setComponent('autoloader', $autoloader)
             ->setComponent('errorHandler', $errorHandler)
             ->setComponent('exceptionHandler', $exceptionHandler)
             ->setComponent('pathConstants', $pathConstants)
             ->setComponent('includePath', $includePath, array('pathConstants', 'phpSettings'))
             ->setComponent('session', $session, array('phpSettings'))
             ->setComponent('phpSettings', $phpSettings)
             ->setComponent('database', $database)
             ->setComponent('i18n', $i18n)
             ->setComponent('frontController', $frontController, array('database', 'autoloader', 'includePath', 'session', 'errorHandler', 'exceptionHandler'));
    }


    /**
     * Dispatch a request
     *
     * @param \Hynage\HTTP\Request|null $request
     */
    public function dispatch(Request $request = null)
    {
        $this->bootstrap('frontController')->dispatch($request);
    }
}
