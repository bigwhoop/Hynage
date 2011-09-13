<?php
namespace Hynage\Form\Element;
use Hynage\Config as Config;
use Hynage\Validator\ValidatorInterface as ValidatorInterface;
use Hynage\Filter\FilterInterface as FilterInterface;


class HtmlElement implements ElementInterface
{
    /**
     * @var string
     */
    protected $_name;
    
    /**
     * @var string
     */
    protected $_id;

    /**
     * @var string
     */
    protected $_label;

    /**
     * @var string
     */
    protected $_value;

    /**
     * @var \Hynage\Config
     */
    protected $_attributes = null;

    /**
     * @var \SplObjectStorage
     */
    protected $_validators = null;

    /**
     * @var \SplObjectStorage
     */
    protected $_filters    = null;

    /**
     * @var array
     */
    protected $_errors     = array();
    

    /**
     * Constructor.
     *
     * @param string $name
     * @param null $label
     * @param null $value
     * @param \Hynage\Config|null $attributes
     */
    public function __construct($name, $label = null, $value = null, Config $attributes = null)
    {
        $this->setName($name);
        $this->setId($name);
        $this->setLabel($label);
        $this->setValue($value);

        if (!$attributes) {
            $attributes = new Config();
        }
        $this->setAttributes($attributes);

        $this->_validators = new \SplObjectStorage();
        $this->_filters    = new \SplObjectStorage();
    }


    public function setName($v)
    {
        $this->_name = $v;
        return $this;
    }


    public function getName()
    {
        return $this->_name;
    }


    public function setId($v)
    {
        $this->_id = $v;
        return $this;
    }
    
    
    public function getId()
    {
        return $this->_id;
    }


    public function setLabel($v)
    {
        $this->_label = $v;
        return $this;
    }


    public function getLabel()
    {
        return $this->_label;
    }


    public function setValue($v)
    {
        $this->_value = $v;
        return $this;
    }


    public function getValue()
    {
        return $this->_value;
    }


    public function setAttributes(Config $v)
    {
        $this->_attributes = $v;
        return $this;
    }


    public function getAttributes()
    {
        return $this->_attributes;
    }


    public function setAttribute($key, $val)
    {
        $this->_attributes->set($key, $val);
        return $this;
    }
    
    public function removeAttribute($key)
    {
        $this->_attributes->remove($key);
        return $this;
    }


    public function addValidator(ValidatorInterface $v)
    {
        $this->_validators->attach($v);
        return $this;
    }


    public function removeValidator(ValidatorInterface $v)
    {
        $this->_validators->detach($v);
        return $this;
    }


    public function addFilter(FilterInterface $v)
    {
        $this->_filters->attach($v);
        return $this;
    }


    public function removeFilter(FilterInterface $v)
    {
        $this->_filters->detach($v);
        return $this;
    }
    
    
    public function isValid()
    {
        $value = $this->getValue();

        $this->clearErrors();

        foreach ($this->_validators as $validator) {
            if (!$validator->isValid($value)) {
                $this->addError($validator->getError());
            }
        }

        return 0 == count($this->getErrors());
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

    
    public function renderElement()
    {
        return '';
    }


    public function renderLabel()
    {
        return sprintf('<label for="%s">%s</label>', $this->getId(), $this->getLabel());
    }
    
    
    public function render()
    {
        $id = $this->getId();
        return sprintf(
            '<dt id="%s">%s</dt><dd id="%s">%s</dd>',
            "dt-$id",
            $this->renderLabel(),
            "dd-$id",
            $this->renderElement()
        );
    }
}
