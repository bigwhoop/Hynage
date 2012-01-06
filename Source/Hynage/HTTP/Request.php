<?php
/**
 * This file is part of Hynage.
 *
 * (c) Philippe Gerber <philippe@bigwhoop.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Hynage\HTTP;

class Request
{
    const METHOD_INTERNAL = 'INTERNAL';
    const METHOD_GET      = 'GET';
    const METHOD_POST     = 'POST';
    
    
    /**
     * @var string
     */
    protected $_url = null;
    
    /**
     * @var array
     */
    protected $_params = array(
        self::METHOD_GET  => array(),
        self::METHOD_POST => array(),
    );
    
    
    /**
     * Assemble a new Request object by parsing the data in the $_SERVER array
     * 
     * @return \Hynage\HTTP\Request
     */
    public static function getCurrent()
    {
        $scheme = isset($_SERVER['HTTPS']) && 'on' == $_SERVER['HTTPS']
                ? 'https'
                : 'http';
        
        $host = $_SERVER['HTTP_HOST'];
        $port = (int)$_SERVER['SERVER_PORT'];
        $path = $_SERVER['REQUEST_URI'];
        
        $url = sprintf(
            '%s://%s%s%s',
            $scheme,
            $host,
            80 == $port ? '' : ':' . $port,
            $path
        );
        
        $url = new self($url);
        
        foreach ($_GET as $key => $value) {
            $url->setParam($key, $value, self::METHOD_GET);
        }
        
        foreach ($_POST as $key => $value) {
            $url->setParam($key, $value, self::METHOD_POST);
        }
        
        return $url;
    }
    
    
    /**
     * Create new Hynage\HTTP\Request object which represents
     * a unique web resource
     * 
     * @param string $url
     */
    public function __construct($url)
    {
        $this->_url = $url;
    }
    
    
    /**
     * Set a specific param
     * 
     * @param string $key
     * @param mixed $value
     * @param string $type Either 'INTERNAL', 'GET' or 'POST'.
     * @return \Hynage\HTTP\Request
     */
    public function setParam($key, $value, $type = self::METHOD_INTERNAL)
    {
        if (!in_array($type, array(self::METHOD_INTERNAL, self::METHOD_GET, self::METHOD_POST))) {
            throw new \Exception('Invalid parameter type given: ' . $type);
        }
        
        $this->_params[$type][$key] = $this->normalizeValue($value);
        
        return $this;
    }


    /**
     * @param string $value
     * @return string
     */
    private function normalizeValue($value)
    {
        if (!is_scalar($value)) {
            return $value;
        }

        // Convert %252f to /
        $value = str_ireplace('%252F', '/', $value);

        return $value;
    }
    
    
    public function hasParam($key)
    {
        return array_key_exists($key, $this->_params[self::METHOD_INTERNAL]);
    }
    

    /**
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function getParam($key, $default = null)
    {
        if (!$this->hasParam($key)) {
            return $default;
        }

        return $this->_params[self::METHOD_INTERNAL][$key];
    }


    public function hasGet($key)
    {
        return array_key_exists($key, $this->_params[self::METHOD_GET]);
    }


    public function isPost()
    {
        return isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == self::METHOD_POST;
    }
    

    /**
     * @param string|null $key
     * @param mixed $default
     * @param int $filter
     * @return mixed
     */
    public function getGet($key = null, $default = null, $filter = FILTER_SANITIZE_STRING)
    {
        if (!$key) {
            return filter_var_array($this->_params[self::METHOD_GET], $filter);
        }

        if (array_key_exists($key, $this->_params[self::METHOD_GET])) {
            return filter_var($this->_params[self::METHOD_GET][$key], $filter);
        }
        
        return $default;
    }


    public function hasPost($key)
    {
        return array_key_exists($key, $this->_params[self::METHOD_POST]);
    }


    /**
     * @param string|null $key
     * @param mixed $default
     * @param int $filter
     * @return mixed
     */
    public function getPost($key = null, $default = null, $filter = FILTER_SANITIZE_STRING)
    {
        if (!$key) {
            return filter_var_array($this->_params[self::METHOD_POST], $filter);
        }

        if ($this->hasPost($key)) {
            return filter_var($this->_params[self::METHOD_POST][$key], $filter);
        }
        
        return $default;
    }
    
    
    /**
     * Return the host name
     * 
     * @return string
     */
    public function getHost()
    {
        return '/' . trim(parse_url($this->_url, PHP_URL_HOST), '/');
    }
    
    
    /**
     * Return the URL's path
     * 
     * @return string
     */
    public function getPath()
    {
        return '/' . trim(parse_url($this->_url, PHP_URL_PATH), '/');
    }
    
    
    public function getHeader($key, $default = null)
    {
        if (array_key_exists($key, $_SERVER)) {
            return $_SERVER[$key];
        }

        return $default;
    }
}