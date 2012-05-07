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
     * @var array
     */
    static protected $instances = array();
    
    /**
     * @var string
     */
    protected $namespace = '';


    /**
     * @static
     * @return Session
     */
    final static public function getInstance()
    {
        $class = get_called_class();
        
        if (!array_key_exists($class, self::$instances)) {
            self::$instances[$class] = new $class();
        }

        return self::$instances[$class];
    }


    private function __construct()
    {}
    

    public function __destruct()
    {
        session_write_close();
    }
    

    private function __clone()
    {}

    
    /**
     * @return bool
     */
    public function isStarted()
    {
        return isset($_SESSION);
    }
    

    /**
     * @throws \LogicException
     * @param string $sessionName
     * @param string $sessionId
     * @return Session
     */
    public function start($sessionName = null, $sessionId = null)
    {
        if ($this->isStarted()) {
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
        if (!$this->isStarted()) {
            $this->start();
        }

        session_regenerate_id();

        return $this;
    }


    /**
     * @param mixed $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->has($key);
    }


    /**
     * @param mixed $key
     * @return bool
     */
    public function has($key)
    {
        if (!$this->isStarted()) {
            $this->start();
        }
        
        $key = $this->normalizeKey($key);
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
        if (!$this->isStarted()) {
            $this->start();
        }
        
        if (!$this->offsetExists($key)) {
            throw new \OutOfBoundsException('Invalid key. No data set.');
        }

        $key = $this->normalizeKey($key);
        return $_SESSION[$key];
    }


    /**
     * @param mixed $key
     * @param mixed $value
     */
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }


    /**
     * @param mixed $key
     * @param mixed $value
     * @return Session
     */
    public function set($key, $value)
    {
        if (!$this->isStarted()) {
            $this->start();
        }
        
        $key = $this->normalizeKey($key);
        $_SESSION[$key] = $value;

        return $this;
    }


    /**
     * @param mixed $key
     */
    public function offsetUnset($key)
    {
        $this->remove($key);
    }
    
    
    /**
     * @param mixed $key
     * @return Session
     */
    public function remove($key)
    {
        if (!$this->isStarted()) {
            $this->start();
        }
        
        $key = $this->normalizeKey($key);
        unset($_SESSION[$key]);
        
        return $this;
    }
    
    
    /**
     * @param mixed $key
     * @return string
     */
    private function normalizeKey($key)
    {
        return '_' . $this->namespace . '_' . $key;
    }
}