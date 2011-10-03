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
use Hynage\Data;

class Session extends AbstractComponent
{
    /**
     * @var null|\Hynage\Data\Session
     */
    private $session = null;

    /**
     * @var int|null
     */
    private $lifetime = null;


    /**
     * @param int|null $lifetime    In seconds.
     */
    public function __construct($lifetime = null)
    {
        $this->lifetime = $lifetime;
    }


    /**
     * @param \Hynage\Data\Session $session
     * @return Session
     */
    public function setSession(Data\Session $session)
    {
        $this->session = $session;
        return $this;
    }


    /**
     * @return \Hynage\Data\Session
     */
    public function getSession()
    {
        if (!$this->session) {
            $this->session = new Data\Session();
        }

        return $this->session;
    }


    /**
     * @return \Hynage\Data\Session
     */
    public function bootstrap()
    {
        // Make sure session is not auto-started.
        ini_set('session.auto_start', 0);

        // The session is only started when used.
        $session = $this->getSession();
        $session->setLifeTime($this->lifetime);
        
        return $session;
    }
}
