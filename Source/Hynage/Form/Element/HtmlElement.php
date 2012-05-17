<?php
/**
 * This file is part of Hynage.
 *
 * (c) Philippe Gerber <philippe@bigwhoop.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Hynage\Form\Element;
use Hynage\Config as Config,
    Hynage\Validator\ValidatorInterface as ValidatorInterface,
    Hynage\Filter\FilterInterface as FilterInterface,
    Hynage\I18n\Translator;

class HtmlElement implements ElementInterface
{
    /**
     * @var string
     */
    protected $_name = '';
    
    /**
     * @var string
     */
    protected $_id = '';

    /**
     * @var string
     */
    protected $_label = '';

    /**
     * @var string
     */
    protected $_value = '';

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
    protected $_filters = null;

    /**
     * @var array
     */
    protected $_errors = array();

    /**
     * @var Translator|null
     */
    private $translator = null;
    

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


    /**
     * @param string $v
     * @return HtmlElement
     */
    public function setLabel($v)
    {
        $this->_label = $v;
        return $this;
    }


    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->_label;
    }


    /**
     * @param string $v
     * @return HtmlElement
     */
    public function setValue($v)
    {
        $this->_value = $v;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->_value;
    }


    /**
     * @param \Hynage\Config $v
     * @return HtmlElement
     */
    public function setAttributes(Config $v)
    {
        $this->_attributes = $v;
        return $this;
    }


    /**
     * @return \Hynage\Config|null
     */
    public function getAttributes()
    {
        return $this->_attributes;
    }


    /**
     * @param string $key
     * @param mixed $val
     * @return HtmlElement
     */
    public function setAttribute($key, $val)
    {
        $this->_attributes->set($key, $val);
        return $this;
    }


    /**
     * @param string $key
     * @return HtmlElement
     */
    public function removeAttribute($key)
    {
        $this->_attributes->remove($key);
        return $this;
    }


    /**
     * @param \Hynage\Validator\ValidatorInterface $v
     * @return HtmlElement
     */
    public function addValidator(ValidatorInterface $v)
    {
        $this->_validators->attach($v);
        return $this;
    }


    /**
     * @param \Hynage\Validator\ValidatorInterface $v
     * @return HtmlElement
     */
    public function removeValidator(ValidatorInterface $v)
    {
        $this->_validators->detach($v);
        return $this;
    }


    /**
     * @param \Hynage\Filter\FilterInterface $v
     * @return HtmlElement
     */
    public function addFilter(FilterInterface $v)
    {
        $this->_filters->attach($v);
        return $this;
    }


    /**
     * @param \Hynage\Filter\FilterInterface $v
     * @return HtmlElement
     */
    public function removeFilter(FilterInterface $v)
    {
        $this->_filters->detach($v);
        return $this;
    }
    

    /**
     * @return bool
     */
    public function isValid()
    {
        $value = $this->getValue();

        foreach ($this->_filters as $filter) {
            $value = $filter->filter($value);
        }
        
        $this->setValue($value);
        
        $this->clearErrors();

        foreach ($this->_validators as $validator) {
            $validator->setTranslator($this->translator);
            
            if (!$validator->isValid($value)) {
                $this->addError($validator->getError());
            }
        }

        return 0 == count($this->getErrors());
    }


    /**
     * @param string $v
     * @return HtmlElement
     */
    public function addError($v)
    {
        $this->_errors[] = $v;
        return $this;
    }


    /**
     * @return HtmlElement
     */
    public function clearErrors()
    {
        $this->_errors = array();
        return $this;
    }


    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }
    
    
    /**
     * @param \Hynage\I18n\Translator $translator
     * @return HtmlElement
     */
    public function setTranslator(Translator $translator)
    {
        $this->translator = $translator;
        return $this;
    }


    /**
     * @return string
     */
    public function renderElement()
    {
        return '';
    }


    /**
     * @return string
     */
    public function renderLabel()
    {
        return sprintf('<label for="%s">%s</label>', $this->getId(), $this->getLabel());
    }
    

    /**
     * @return string
     */
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
