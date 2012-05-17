<?php
/**
 * This file is part of Hynage.
 *
 * (c) Philippe Gerber <philippe@bigwhoop.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Hynage\MVC\Controller;
use Hynage\MVC\View\View,
    Hynage\HTTP\Request,
    Hynage\HTTP\Response,
    Hynage\MVC\Controller\Front as FrontController;

abstract class Action
{
    /**
     * @var \Hynage\MVC\View\View
     */
    protected $_view = null;
    
    /**
     * @var \Hynage\HTTP\Request
     */
    protected $_request = null;
    
    /**
     * @var \Hynage\HTTP\Response
     */
    protected $_response = null;

    /**
     * @var \Hynage\MVC\Controller\Front
     */
    protected $_front = null;
    
    
    /**
     * @param Front $front
     * @param \Hynage\MVC\View\View $view
     * @param \Hynage\HTTP\Request $request
     * @param \Hynage\HTTP\Response $response
     */
    public function __construct(FrontController $front, View $view, Request $request, Response $response)
    {
        $this->_front    = $front;
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
     * Return the front controller
     *
     * @return \Hynage\MVC\Controller\Front
     */
    public function getFrontController()
    {
        return $this->_front;
    }
    
    
    /**
     * Return the view object
     * 
     * @return \Hynage\MVC\View\View
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
     * @return \Hynage\HTTP\Response
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
     * @return \Hynage\MVC\Controller\Action
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
    

    /**
     * @param string $script
     * @param bool   $includeViewParams
     * @param string $contentVarName        Is only used when $includeViewParams is true
     * @return Action
     */
    public function renderViewScriptAndSendJson($script, $includeViewParams = false, $contentVarName = 'html')
    {
        $params = $content = $this->getView()->render($script, false);
        
        if ($includeViewParams) {
            $params = $this->getView()->getParams();
            $params[$contentVarName] = $content;
        }
        
        return $this->sendJson($params, 200, $includeViewParams);
    }
    
    
    /**
     * @param array $data
     * @return Action
     */
    public function sendJsonSuccess(array $data = array())
    {
        return $this->sendJson(array(
            'status' => 'ok',
            'data'   => $data,
        ));
    }
    
    
    /**
     * @param string $message
     * @param int $statusCode
     * @return Action
     */
    public function sendJsonError($message, $statusCode = 500)
    {
        return $this->sendJson(
            array(
                'status'  => 'error',
                'message' => $message,
                'code'    => $statusCode,
            )
        );
    }
    

    /**
     * @param string $content
     * @param int $code
     * @param bool $encode
     * @return Action
     */
    public function sendJson($content, $code = 200, $encode = true)
    {
        if ($encode) {
            $content = json_encode($content);
        } elseif (!is_scalar($content)) {
            throw new \LogicException('Non-scalar variable must be (JSON) encoded manually.');
        }
        
    	$response = $this->getResponse();
    	
    	$response->appendBody($content)
    	         ->setHeader('content-type', 'application/json', $code, true)
    	         ->send(true);

        return $this;
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


    /**
     * @param bool $exit
     * @return string
     */
    public function reload($exit = true)
    {
        $url = $this->getRequest()->getHeader('REQUEST_URI', '/');
        return $this->redirect($url, 302, $exit);
    }
    
    
    /**
     * @return \Hynage\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->getFrontController()->getApplication()->bootstrap('Database');
    }


    /**
     * Returns the translation of the given string
     *
     * @param string $string [more arguments for printf-like placeholders)
     * @return string
     */
    public function _($string)
    {
        $args = func_get_args();
        $translator = \Hynage\I18n\Translator::getInstance();
        return call_user_func_array(array($translator, 'translate'), $args);
    }
}