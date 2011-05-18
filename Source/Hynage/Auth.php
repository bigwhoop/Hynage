<?php
namespace Hynage;
use App\Models\User as User;
use Hynage\Session as Session;

class Auth
{
    /**
     * @var \Hynage\Auth|null
     */
    protected static $_instance = null;
    
    
    /**
     * Singleton implementation to return the only instance of Hynage\Auth
     * 
     * @return \Hynage\Auth
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
    
    
    /**
     * Return whether the current user is logged in
     * 
     * @param bool $loadUser
     * @return bool
     */
    public function hasIdentity($loadUser = false)
    {
        if ($loadUser) {
            return (bool)User::getCurrent();
        }
        
        return Session::getInstance()->getUserId();
    }
    
    
    /**
     * Return the current user's id
     * 
     * @return int|false
     */
    public function getIdentity()
    {
        return Session::getInstance()->getUserId();
    }
    
    
    /**
     * Set the current user
     * 
     * @param int $id
     * @return \Hynage\Auth
     */
    public function setIdentity($id)
    {
        Session::getInstance()->setUserId($id)
                              ->rememberMe();
        
        return $this;
    }
    
    
    /**
     * Clear the current user's session
     * 
     * @return \Hynage\Auth
     */
    public function clearIdentity()
    {
        Session::getInstance()->clearUserId();
        
        return $this;
    }
    
    
    protected function __construct()
    {}
    
    
    protected function __clone()
    {}
}