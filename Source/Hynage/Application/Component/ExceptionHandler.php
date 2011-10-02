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
    Hynage\Application\ApplicationInterface;

class ExceptionHandler extends AbstractComponent
{
    /**
     * @var \Hynage\Application\ApplicationInterface|null
     */
    private $application = null;

    /**
     * @var \Hynage\Config|null
     */
    private $config = null;


    /**
     * @param \Hynage\Application\ApplicationInterface $application
     * @param \Hynage\Config|null $config
     */
    public function __construct(ApplicationInterface $application, Config $config = null)
    {
        $this->application = $application;

        if (!$config) {
            $config = new Config();
        }
        $this->config = $config;
    }


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
            $errorUrl = sprintf(
                "/%s/%s",
                $this->config->get('errors.controller', 'errors'),
                $this->config->get('errors.action', 'error')
            );

            $request = new HTTP\Request($errorUrl);
            $request->setParam('exception', $e);
            $this->application->bootstrap('frontController')->dispatch($request);
        } catch (\Exception $e2) {
            exit($e);
        }
    }
}
