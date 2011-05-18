<?php
namespace Hynage\MVC\Controller;
use Hynage\MVC\View as View;
use Hynage\HTTP\Request as Request;
use Hynage\HTTP\Response as Response;

abstract class Action
{
    /**
     * @var \Hynage\MVC\View
     */
    protected $_view = null;
    
    /**
     * @var \Hynage\HTTP\Request
     */
    protected $_request = null;
    
    /**
     * @var \Hynage\HTTP\Respone
     */
    protected $_response = null;
    
    
    /**
     * Create a new controller object
     * 
     * @param \Hynage\MVC\View $view
     */
    public function __construct(View $view, Request $request, Response $response)
    {
        $this->_view     = $view;
        $this->_request  = $request;
        $this->_response = $response;
    }
    
    
    /**
     * This method is called right before dispatching
     */
    public function preDispatch()
    {}
    
    
    /**
     * This method is called right after dispatching
     */
    public function postDispatch()
    {}
    
    
    /**
     * Return the view object
     * 
     * @return \Hynage\MVC\View
     */
    public function getView()
    {
        return $this->_view;
    }
    
    
    /**
     * Return the request object
     * 
     * @return \Hynage\HTTP\Request
     */
    public function getRequest()
    {
        return $this->_request;
    }
    
    
    /**
     * Return the response object
     * 
     * @return \Hynage\HTTP\Respone
     */
    public function getResponse()
    {
        return $this->_response;
    }
    
    
    /**
     * Set multiple view params
     * 
     * @param array $values
     * @return \Hynage\MVC\Controller\Front
     */
    public function setViewParams(array $values)
    {
        foreach ($values as $key => $value) {
            $this->setViewParam($key, $value);
        }
        
        return $this;
    }
    
    
    /**
     * Set a param to the view
     * 
     * @param string $key
     * @param mixed $value
     * @return \Hynage\MVC\Controller\Front
     */
    public function setViewParam($key, $value)
    {
        $this->getView()->setParam($key, $value);
        
        return $this;
    }
    
    
    /**
     * Shortcut to the view's render() method which captures
     * the output and appends it to the response object
     * 
     * @param string $script
     * @return \Hynage\MVC\Controller\Action
     */
    public function renderViewScript($script)
    {
        $content = $this->getView()->render($script, false);
        $this->getResponse()->appendBody($content);
        
        return $this;
    }
    
    
    public function renderViewScriptAndSendJson($script)
    {
        $content = $this->getView()->render($script, false);
        
        $this->sendJson($content, 200, false);
    }
    
    
    public function sendJson($content, $code = 200, $encode = true)
    {
        if ($encode) {
            $content = json_encode($content);
        }
        
    	$response = $this->getResponse();
    	
    	$response->appendBody($content)
    	         ->setHeader('content-type', 'application/json', $code, true)
    	         ->send(true);
    }

    
    /**
     * Set a "location" header and optionally halt the script
     * 
     * @param string $url
     * @param int $httpStatusCode
     * @param bool $exit
     * @return string
     */
    public function redirect($url, $httpStatusCode = 302, $exit = true)
    {
        $response = $this->getResponse();
        $response->setHeader('location', $url, $httpStatusCode);
        
        if ($exit) {
            $response->send();
            exit();
        }
        
        return $this;
    }


    public function _($string)
    {
        $args = func_get_args();
        return call_user_func_array(array('\Hynage\I18n\Translator', 'translate'), $args);
    }
}