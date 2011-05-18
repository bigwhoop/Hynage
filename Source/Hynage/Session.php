<?php
namespace Hynage;

class Session
{
    /**
     * @var \Hynage\Session
     */
    protected static $_instance = null;
    
    
    /**
     * Return the only instance of Hynage\Session
     * 
     * @return \Hynage\Session
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            $class = get_called_class();
            self::$_instance = new $class;
        }
        
        return self::$_instance;
    }
    
    
    /**
     * Set the current user's id
     * 
     * @param int $id
     * @return \Hynage\Session
     */
    public function setUserId($id)
    {
        $_SESSION['userId'] = (int)$id;
        
        return $this;
    }
    
    
    /**
     * Return the current user's id
     * 
     * @return int|null
     */
    public function getUserId()
    {
        return isset($_SESSION['userId']) ? (int)$_SESSION['userId'] : null;
    }
    
    
    /**
     * Clear the current user's id
     * 
     * @return \Hynage\Session
     */
    public function clearUserId()
    {
        unset($_SESSION['userId']);
        
        return $this;
    }
    
    
    public function rememberMe()
    {
        $cookieParams = session_get_cookie_params();

        session_set_cookie_params(
            60 * 60 * 24 * 31, // 31 days
            $cookieParams['path'],
            $cookieParams['domain'],
            $cookieParams['secure']
        );

        session_regenerate_id();

        return $this;
    }
    
    
    /**
     * Protected constructor (singleton)
     */
    protected function __construct()
    {}
    
    
    /**
     * Protected cloner (singleton)
     */
    protected function __clone()
    {}
}