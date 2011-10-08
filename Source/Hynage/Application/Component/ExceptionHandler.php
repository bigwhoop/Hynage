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
use Hynage\HTTP,
    Hynage\Config,
    Hynage\Application\WebApplication;

class ExceptionHandler extends AbstractComponent
{
    /**
     * @var \Hynage\Application\WebApplication|null
     */
    private $webApp = null;

    /**
     * @var \Hynage\Config|null
     */
    private $config = null;


    /**
     * @param \Hynage\Application\WebApplication $webApp|null
     * @param \Hynage\Config|null $config
     */
    public function __construct(WebApplication $webApp = null, Config $config = null)
    {
        $this->webApp = $webApp;

        if (!$config) {
            $config = new Config();
        }
        $this->config = $config;
    }


    public function bootstrap()
    {
        if (php_sapi_name() == 'cli' || !$this->webApp) {
            $callback = array($this, 'onExceptionAbort');
        } else {
            $callback = array($this, 'onExceptionShowErrorPage');
        }

        set_exception_handler($callback);
    }


    /**
     * @param \Exception $e
     */
    public function onExceptionAbort(\Exception $e)
    {
        echo 'Sorry, something went terribly wrong. :/';

        if (ini_get('display_errors')) {
            echo (string)$e;
        }

        exit();
    }


    /**
     * @param \Exception $e
     */
    public function onExceptionShowErrorPage(\Exception $e)
    {
        try {
            $errorUrl = sprintf(
                "/%s/%s",
                $this->config->get('errors.controller', 'errors'),
                $this->config->get('errors.action', 'error')
            );

            $request = new HTTP\Request($errorUrl);
            $request->setParam('exception', $e);
            
            $this->webApp->dispatch($request);
        } catch (\Exception $e2) {
            $this->onExceptionAbort($e);
        }
    }
}
