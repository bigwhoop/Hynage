<?php
namespace Hynage\Session;

class AuthCookie
{
    /**
     * @static
     * @param int $days
     * @return void
     */
    static public function setLifeTimeInDays($days)
    {
        self::setLifeTime(60 * 60 * 24 * $days);
    }


    /**
     * @static
     * @param int $seconds
     * @return void
     */
    static public function setLifeTime($seconds)
    {
        $cookieParams = session_get_cookie_params();
        
        session_set_cookie_params(
            $seconds,
            $cookieParams['path'],
            $cookieParams['domain'],
            $cookieParams['secure']
        );
    }
}
