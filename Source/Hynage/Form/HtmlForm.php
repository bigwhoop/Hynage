<?php
/**
 * This file is part of Hynage.
 *
 * (c) Philippe Gerber <philippe@bigwhoop.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Hynage\Form;
use Hynage\Config as Config,
    Hynage\Filter,
    Hynage\HTTP\Request as Request,
    Hynage\Form\Element\ElementInterface as ElementInterface,
    Hynage\MVC\View\View;


class HtmlForm
{
    const METHOD_POST = 'post';
    const METHOD_GET  = 'get';

    const ENCTYPE_URLENCODED = 'application/x-www-form-urlencoded';
    const ENCTYPE_MULTIPART  = 'multipart/form-data';
    const ENCTYPE_PLAINTEXT  = 'text/plain';

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
    protected $_enctype = self::ENCTYPE_URLENCODED;

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
        foreach ($this->getElements() as $element) {
            if ($element instanceof Element\FileElement) {
                $this->setEnctype(self::ENCTYPE_MULTIPART);
                break;
            }
        }

        ob_start();
        ?>

        <?php if (count($this->getErrors())): ?>
            <div class="form-errors">
                <p><?php echo $this->_('The following errors occurred:'); ?></p>
                <ul>
                    <?php foreach ($this->getErrors() as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?php echo $this->getAction(); ?>" method="<?php echo $this->getMethod(); ?>" enctype="<?php echo $this->getEnctype(); ?>">
            <dl class="form">
                <?php foreach ($this->getElements() as $e): ?>
                    <?php echo $e->render(); ?>
                <?php endforeach; ?>
            </dl>
        </form>

        <?php
        $content = trim(ob_get_clean());
        return $content;
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


    public function getElementValues()
    {
        $a = array();

        foreach ($this->getElements() as $e) {
            $a[$e->getName()] = $e->getValue();
        }

        return $a;
    }


    public function getElementValue($name, $default = null)
    {
        $e = $this->getElementByName($name);
        if ($e) {
            return $e->getValue();
        }

        return $default;
    }


    /**
     * @param \Hynage\HTTP\Request $request
     * @return bool
     */
    public function isValid(Request $request)
    {
        if (!$request->isPost()) {
            return false;
        }

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
     * @param string $string [more arguments for printf-like placeholders)
     * @return string
     */
    public function _($string)
    {
        $args = func_get_args();
        $translator = \Hynage\I18n\Translator::getInstance();
        return call_user_func_array(array($translator, 'translate'), $args);
    }


    /**
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }
}
