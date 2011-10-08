<?php
/**
 * This file is part of Hynage.
 *
 * (c) Philippe Gerber <philippe@bigwhoop.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Hynage\Data;

class Session implements \ArrayAccess
{
    /**
     * @var bool
     */
    private $isStarted = false;

    /**
     * @var null|Session
     */
    static protected $instance = null;


    /**
     * @static
     * @return Session
     */
    static public function getInstance()
    {
        if (!static::$instance) {
            $class = get_called_class();
            static::$instance = new $class();
        }

        return static::$instance;
    }


    private function __construct()
    {}
    

    private function __clone()
    {}


    /**
     * @throws \LogicException
     * @param string $sessionName
     * @param string $sessionId
     * @return Session
     */
    public function start($sessionName = null, $sessionId = null)
    {
        if ($this->isStarted) {
            throw new \LogicException('Session already started.');
        }

        if (session_id()) {
            throw new \LogicException('PHP session already started.');
        }

        if (!empty($sessionName)) {
            session_name($sessionName);
        }

        if (!empty($sessionId)) {
            session_id($sessionId);
        }

        session_start();

        $this->isStarted = true;

        return $this;
    }

    /**
     * @param int $days
     * @return Session
     */
    public function setLifeTimeInDays($days)
    {
        $this->setLifeTime(60 * 60 * 24 * $days);

        return $this;
    }


    /**
     * @param int $seconds
     * @return Session
     */
    public function setLifeTime($seconds)
    {
        $cookieParams = session_get_cookie_params();
        
        session_set_cookie_params(
            $seconds,
            $cookieParams['path'],
            $cookieParams['domain'],
            $cookieParams['secure']
        );
        
        return $this;
    }


    /**
     * @throws \LogicException
     * @return Session
     */
    public function regenerateId()
    {
        if (!$this->isStarted) {
            $this->start();
        }

        session_regenerate_id();

        return $this;
    }


    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }


    /**
     * @param mixed $key
     * @return bool
     */
    public function has($key)
    {
        if (!$this->isStarted) {
            $this->start();
        }

        return array_key_exists($key, $_SESSION);
    }


    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }


    /**
     * @throws \OutOfBoundsException
     * @param mixed $key
     * @return mixed
     */
    public function get($key)
    {
        if (!$this->isStarted) {
            $this->start();
        }

        if (!$this->offsetExists($key)) {
            throw new \OutOfBoundsException('Invalid key. No data set.');
        }

        return $_SESSION[$key];
    }


    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }


    /**
     * @param mixed $key
     * @param mixed $value
     * @return Session
     */
    public function set($key, $value)
    {
        if (!$this->isStarted) {
            $this->start();
        }

        $_SESSION[$key] = $value;

        return $this;
    }


    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        if (!$this->isStarted) {
            $this->start();
        }

        if ($this->offsetExists($offset)) {
            unset($_SESSION[$offset]);
        }
    }
}