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
use Hynage\HTTP;

class ExceptionHandler extends AbstractComponent
{
    public function bootstrap()
    {
        set_exception_handler(array($this, 'handleException'));
    }


    /**
     * @param \Exception $e
     */
    public function handleException(\Exception $e)
    {
        if (php_sapi_name() == 'cli') {
            exit((string)$e);
        }

        try {
            $config = $this->getConfig();

            $errorUrl = sprintf(
                "/%s/%s",
                $config->get('frontController.errors.controller', 'errors'),
                $config->get('frontController.errors.action', 'error')
            );

            $request = new HTTP\Request($errorUrl);
            $request->setParam('exception', $e);
            $this->bootstrap('Frontcontroller')->dispatch($request);
        } catch (\Exception $e2) {
            exit($e);
        }
    }
}
