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
use Hynage\Application\ApplicationInterface as App,
    Hynage\Config,
    Hynage\MVC\View\View,
    Hynage\MVC\View\Layout,
    Hynage\HTTP\Request,
    Hynage\HTTP\Response;

class Front
{
    /**
     * \Hynage\Application\ApplicationInterface
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
     * @var null|\Hynage\MVC\View\View
     */
    protected $_view = null;

    /**
     * @var null|\Hynage\MVC\View\Layout
     */
    protected $_layout = null;

    /**
     * @var bool
     */
    protected $_renderLayout = true;


    /**
     * Constructor.
     *
     * @param \Hynage\Application\ApplicationInterface $app
     */
    public function __construct(App $app)
    {
        $this->_app = $app;
    }


    /**
     * @return \Hynage\Application\ApplicationInterface
     */
    public function getApplication()
    {
        return $this->_app;
    }


    /**
     * @param string $path
     * @return string
     */
    private function formatPath($path)
    {
        $config = $this->_app->getConfig();
        
        $formatter = $config->get('frontController.formatters.requestPath');
        if (is_callable($formatter)) {
            $path = call_user_func($formatter, $path);
        }

        return $path;
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
     * @param \Hynage\MVC\View\View $view
     * @return Front
     */
    public function setView(View $view)
    {
        $view->setFrontController($this);
        $this->_view = $view;

        return $this;
    }


    /**
     * @return \Hynage\MVC\View\View
     */
    public function getView()
    {
        if (!$this->_view) {
            $config = $this->getApplication()->getConfig()->get('view', new Config());
            $this->setView(new View($config));
        }

        return $this->_view;
    }


    /**
     * @param \Hynage\MVC\View\Layout $layout
     * @return Front
     */
    public function setLayout(Layout $layout)
    {
        $layout->setFrontController($this);
        $this->_layout = $layout;

        return $this;
    }


    /**
     * @return \Hynage\MVC\View\Layout
     */
    public function getLayout()
    {
        if (!$this->_layout) {
            $config = $this->getApplication()->getConfig()->get('layout', new Config());
            $this->setLayout(new Layout($config));
        }

        return $this->_layout;
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
        $config = $this->getApplication()->getConfig();

        if ($request) {
            $this->setRequest($request);
        }
        
        if ($response) {
            $this->setResponse($response);
        }
        
        $request = $this->getRequest();
        $path    = $this->formatPath($request->getPath());
        $parts   = explode('/', ltrim($path, '/'));
        
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
        $controllerPath  = $this->getFormattedControllerPath();
        $controllerClass = $this->getFormattedControllerName();

        // Check if the controller class is loadable
        $loadable = false;
        if (file_exists($controllerPath)) {
            require_once $controllerPath;

            if (class_exists($controllerClass, false)) {
                $loadable = true;
            }
        }

        if (!$loadable) {
            throw new Exception('Invalid controller "' . $this->getController() . '". Tried class "' . $controllerClass . '" in file "' . $controllerPath . '".');
        }
        
        // Get view
        $view = $this->getView();

        // Get response
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
            $layout = $this->getLayout();

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