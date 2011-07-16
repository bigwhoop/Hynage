<?php
namespace Hynage\MVC\Controller;
use Hynage,
    Hynage\Application as App,
    Hynage\MVC\Controller,
    Hynage\MVC\View as View,
    Hynage\MVC\Layout as Layout,
    Hynage\HTTP\Request as Request,
    Hynage\HTTP\Response as Response,
    Hynage\Filter;

class Front
{
    /**
     * \Hynage\Application
     */
    protected $_app = null;

    /**
     * @var string
     */
    protected $_controller = null;
    
    /**
     * @var string
     */
    protected $_action = null;
    
    /**
     * @var \Hynage\HTTP\Request
     */
    protected $_request = null;
    
    /**
     * @var \Hynage\HTTP\Response
     */
    protected $_response = null;

    /**
     * @var bool
     */
    protected $_renderLayout = true;


    /**
     * Constructor.
     *
     * @param \Hynage\Application $app
     */
    public function __construct(App $app)
    {
        $this->_app = $app;
    }


    /**
     * @return \Hynage\Application
     */
    public function getApplication()
    {
        return $this->_app;
    }
    
    
    /**
     * Set the controller name
     * 
     * @param string $controller
     * @return \Hynage\MVC\Controller\Front
     */
    public function setController($controller)
    {
        $this->_controller = (string)$controller;
        
        return $this;
    }
    
    
    /**
     * Return the controller name
     * 
     * @return string
     */
    public function getController()
    {
    	return $this->_controller;
    }
    
    
    /**
     * Format the class name according to the specified formatter.
     * 
     * @return string
     */
    public function getFormattedControllerName()
    {
        $config = $this->_app->getConfig();

        $formatter = $config->get('frontController.formatters.controllerName');
        if (!is_callable($formatter)) {
            throw new Exception('Invalid controller name formatter.');
        }

        return call_user_func($formatter, $this->getController());
    }


    /**
     * Format the class path according to the specified formatter.
     *
     * @return string
     */
    public function getFormattedControllerPath()
    {
        $config = $this->_app->getConfig();

        $formatter = $config->get('frontController.formatters.controllerPath');
        if (!is_callable($formatter)) {
            throw new Exception('Invalid controller path formatter.');
        }

        return call_user_func($formatter, $this->getController());
    }
    
    
    /**
     * Set the action
     * 
     * @param string $action
     * @return \Hynage\MVC\Controller\Front
     */
    public function setAction($action)
    {
        $this->_action = (string)$action;
        
        return $this;
    }
    
    
    /**
     * Return the action name
     * 
     * @return string
     */
    public function getAction()
    {
        return $this->_action;
    }
    
    
    /**
     * Format the method of the given action name.
     * 
     * @return string
     */
    public function getActionMethod()
    {
        $config = $this->_app->getConfig();

        $formatter = $config->get('frontController.formatters.actionName')->getData();
        if (!is_callable($formatter)) {
            throw new Exception('Invalid action name formatter.');
        }

        return call_user_func($formatter, $this->getAction());
    }
    
    
    /**
     * Set the request used for dispatching
     * 
     * @param \Hynage\HTTP\Request $request
     * @return \Hynage\MVC\Controller\Front
     */
    public function setRequest(Request $request)
    {
        $this->_request = $request;
        
        return $this;
    }
    
    
    /**
     * Return the request. If none was set, a request is assemble
     * by the given HTTP data.
     * 
     * @return \Hynage\HTTP\Request
     */
    public function getRequest()
    {
        if (null === $this->_request) {
            $request = Request::getCurrent();
            $this->setRequest($request);
        }
        
        return $this->_request;
    }
    
    
    /**
     * Set the response
     * 
     * @param \Hynage\HTTP\Response $response
     * @return \Hynage\MVC\Controller\Front
     */
    public function setResponse(Response $response)
    {
        $this->_response = $response;
        
        return $this;
    }
    
    
    /**
     * Return the response. If none was set, a default response
     * object is created.
     * 
     * @return \Hynage\HTTP\Response
     */
    public function getResponse()
    {
        if (null === $this->_response) {
            $this->_response = new Response();
        }
        
        return $this->_response;
    }
    
    
    /**
     * Dispatch a request and send the response
     * 
     * @param \Hynage\HTTP\Request $request
     * @param \Hynage\HTTP\Response $response
     * @return \Hynage\MVC\Controller\Front
     */
    public function dispatch(Request $request = null, Response $response = null)
    {
        $config = App::getInstance()->getConfig();
        
        if ($request) {
            $this->setRequest($request);
        }
        
        if ($response) {
            $this->setResponse($response);
        }
        
        $request = $this->getRequest();
        $parts = explode('/', trim($request->getPath(), '/'));
        
        if (count($parts) && !empty($parts[0])) {
            // Set the given controller
            $this->setController(array_shift($parts));
            
            if (count($parts)) {
                // Set the given action
                $this->setAction(array_shift($parts));
                
                // Set additional $_GET params
                if (2 <= count($parts)) {
                    $count = count($parts);
                    if (1 == $count % 2) {
                        $parts[] = '';
                    }
                    
                    for ($i = 0; $i < $count; $i += 2) {
                        $request->setParam($parts[$i], $parts[$i + 1], Request::METHOD_GET);
                    }
                }
            }
        }

        // Load controller class
        $controllerClass = $this->getFormattedControllerName();
        $controllerPath =  $this->getFormattedControllerPath();
        require $controllerPath;

        // Check if the controller class is loadable
        if (!@class_exists($controllerClass, true)) {
            throw new Exception('Invalid controller "' . $this->getController() . '". Tried class "' . $controllerClass . '".');
        }
        
        // Prepare view
        $viewConfig = $config->get('view', new Hynage\Config());
        $view = new View($viewConfig);
        
        $response = $this->getResponse();

        // Create the controller
        $controller = new $controllerClass($this, $view, $request, $response);

        // Check if the controller class extends the base action controller class
        if (!in_array('Hynage\\MVC\\Controller\\Action', class_parents($controllerClass))) {
            throw new Exception('Controller class "' . $controllerClass . '" must extend the base action controller "\Hynage\MVC\Controller\Action".');
        }

        $action = $this->getActionMethod();
        
        // Check if the action method exists
        if (!method_exists($controllerClass, $action)) { 
            throw new Exception('Invalid action "' . $this->getAction() . '". Tried method "' . $controllerClass . '->' . $action . '()".');
        }
        
        // Call the action
        $controller->preDispatch();
        $controller->$action();
        $controller->postDispatch();
        
        // Prepare the layout
        if ($this->_renderLayout) {
            $layoutConfig = $config->get('layout', new Hynage\Config());
            $layout = new Layout($layoutConfig);

            // Wrap the layout around the content
            $layout->setContent($response->getBody());
            $response->setBody($layout->render(false));
        }
        
        // Send the response
        $response->send();
        
        return $this;
    }


    /**
     * Enable rendering of the layout
     *
     * @return \Hynage\MVC\Controller\Front
     */
    public function enableLayout()
    {
        $this->_renderLayout = true;

        return $this;
    }


    /**
     * Disable rendering of the layout
     *
     * @return \Hynage\MVC\Controller\Front
     */
    public function disableLayout()
    {
        $this->_renderLayout = false;

        return $this;
    }
}