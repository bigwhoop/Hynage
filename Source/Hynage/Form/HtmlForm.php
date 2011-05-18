<?php
namespace Hynage\Form;
use Hynage\Config as Config;
use Hynage\Filter;
use Hynage\HTTP\Request as Request;
use Hynage\Form\Element\ElementInterface as ElementInterface;
use Hynage\MVC\View as View;


class HtmlForm
{
    /**
     * @var string
     */
    protected $_action = '';

    /**
     * @var string
     */
    protected $_method = 'post';

    /**
     * @var string
     */
    protected $_enctype = 'application/x-www-form-urlencoded';

    /**
     * @var \SplObjectStorage
     */
    protected $_elements = null;

    /**
     * @var array
     */
    protected $_errors = array();


    /**
     * __construct
     *
     * @param \Hynage\Config|null $config
     */
    public function __construct(Config $config = null)
    {
        $this->_elements = new \SplObjectStorage();

        if ($config) {
            $this->setConfig($config);
        }
        
        $this->init();
    }


    /**
     * Set the config
     *
     * @param \Hynage\Config $config
     * @return \Hynage\Form\HtmlForm
     */
    public function setConfig(Config $config)
    {
        foreach ($config as $key => $value) {
            $filter = new Filter\String\CapWords();
            $method = 'set' . $filter->filter($key);

            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }

        return $this;
    }
    
    
    public function setAction($v)
    {
        $this->_action = $v;
        return $this;
    }
    
    
    public function getAction()
    {
        return $this->_action;
    }


    public function setMethod($v)
    {
        $this->_method = $v;
        return $this;
    }


    public function getMethod()
    {
        return $this->_method;
    }


    public function setEnctype($v)
    {
        $this->_enctype = $v;
        return $this;
    }


    public function getEnctype()
    {
        return $this->_enctype;
    }


    public function addElement(ElementInterface $e)
    {
        $this->_elements->attach($e);
        return $this;
    }


    public function removeElement(ElementInterface $e)
    {
        if ($this->_elements->contains($e)) {
            $this->_elements->detach($e);
        }

        return $this;
    }


    public function getElements()
    {
        return $this->_elements;
    }


    public function getElementByName($name)
    {
        foreach ($this->_elements as $e) {
            if ($e->getName() == $name) {
                return $e;
            }
        }

        return false;
    }


    public function init()
    {}


    public function render()
    {
        $viewConfig = new Config();
        $viewConfig->set('basePath', realpath(HYNAGE_APP_PATH . '/Views/_Partial'));

        $view = new View($viewConfig);
        $view->setParam('form', $this);
        return $view->render('Form.phtml', false);
    }


    public function addError($v)
    {
        $this->_errors[] = $v;
        return $this;
    }


    public function clearErrors()
    {
        $this->_errors = array();
        return $this;
    }


    public function getErrors()
    {
        return $this->_errors;
    }


    public function setElementValue($name, $value)
    {
        $e = $this->getElementByName($name);
        if (!$e) {
            throw new NoSuchElementException($name);
        }

        $e->setValue($value);
        return $this;
    }


    public function getElementValue($name, $default = null)
    {
        $e = $this->getElementByName($name);
        if ($e) {
            return $e->getValue();
        }

        return $default;
    }


    public function isValid(Request $request)
    {
        // Set values
        foreach ($this->_elements as $e) {
            if ($request->hasPost($e->getName())) {
                $e->setValue($request->getPost($e->getName()));
            }
        }

        // Validate
        foreach ($this->_elements as $element) {
            if (!$element->isValid()) {
                foreach ($element->getErrors() as $error) {
                    $this->addError("<strong>{$element->getLabel()}:</strong> $error");
                }
            }
        }

        return 0 == count($this->getErrors());
    }


    /**
     * Returns the translation of the given string
     *
     * @param string $string
     * @param mixed Arguments for printf
     * @return string
     */
    public function _($string)
    {
        $args = func_get_args();
        return call_user_func_array(array('\Hynage\I18n\Translator', 'translate'), $args);
    }


    public function __toString()
    {
        return $this->render();
    }
}
