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

class ErrorHandler extends AbstractComponent
{
    public function bootstrap()
    {
        set_error_handler(array($this, 'handleError'));
    }


    /**
     * Put errors into an ErrorException object an throw it at the clown.
     *
     * @throws \ErrorException
     * @param int $severity
     * @param string $message
     * @param string $filename
     * @param int $line
     */
    public function handleError($severity, $message, $filename, $line)
    {
        if (error_reporting() === 0) {
            return;
        }

        if (error_reporting() & $severity) {
            throw new \ErrorException($message, 0, $severity, $filename, $line);
        }
    }
}
