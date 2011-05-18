<?php
namespace Hynage\HTTP;

class Response
{
    /**
     * @var array
     */
    protected $_headers = array();
    
    /**
     * @var string
     */
    protected $_body = '';
    
    
    /**
     * Clear all headers
     * 
     * @return \Hynage\HTTP\Respone
     */
    public function clearHeaders()
    {
        $this->_headers = array();
        
        return $this;
    }

    
    /**
     * Set a specific header value
     * 
     * @param string $key
     * @param string $value
     * @param int $code
     * @param bool $replace
     * @return \Hynage\HTTP\Respone
     */
    public function setHeader($key, $value, $code = 200, $replace = true)
    {
        $this->_headers[$key] = array(
            'value'   => $value,
            'replace' => (bool)$replace,
            'code'    => (int)$code,
        );
        
        return $this;
    }
    
    
    /**
     * Clear the body content
     * 
     * @return \Hynage\HTTP\Respone
     */
    public function clearBody()
    {
        $this->_body = '';
        
        return $this;
    }

    
    /**
     * Set the body content
     * 
     * @param string $content
     * @return \Hynage\HTTP\Respone
     */
    public function setBody($content)
    {
        $this->clearBody()
             ->appendBody($content);
        
        return $this;
    }
    
    
    /**
     * Return the body content
     * 
     * @return string
     */
    public function getBody()
    {
        return $this->_body;
    }
    
    
    /**
     * Append content to the body
     * 
     * @param string $content
     * @return \Hynage\HTTP\Respone
     */
    public function appendBody($content)
    {
        $this->_body .= $content;
        
        return $this;
    }
    
    
    /**
     * Send the response
     *  
     * 1. Set headers
     * 2. Echo content
     * 
     * @param bool $exit
     * @return \Hynage\HTTP\Respone
     */
    public function send($exit = false)
    {
        foreach ($this->_headers as $key => $data) {
            header(sprintf('%s: %s', $key, $data['value']), $data['replace'], $data['code']);
        }
        
        echo $this->getBody();
        
        if ($exit) {
            exit();
        }
        
        return $this;
    }
}