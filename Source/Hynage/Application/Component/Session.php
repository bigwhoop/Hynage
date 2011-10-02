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

class Session extends AbstractComponent
{
    /**
     * @var int|null
     */
    private $lifetime = null;


    /**
     * @param int|null $lifetime
     */
    public function __construct($lifetime = null)
    {
        $this->lifetime = $lifetime;
    }


    public function bootstrap()
    {
        list($lifetime, $path, $domain, $secure, $httponly) = array_values(session_get_cookie_params());

        if (null !== $lifetime) {
            $lifetime = (int)$lifetime;
        }

        session_set_cookie_params($lifetime, $path, $domain, $secure, $httponly);

        session_start();
    }
}
