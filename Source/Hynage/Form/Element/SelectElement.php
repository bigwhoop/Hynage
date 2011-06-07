<?php
namespace Hynage\Form\Element;

class SelectElement extends HtmlElement
{
    protected $_values = array();


    public function setValues(array $values)
    {
        $this->_values = $values;
        return $this;
    }


    public function getValues()
    {
        return $this->_values;
    }


    public function renderElement()
    {
        $values = $this->getValues();
        $value  = $this->getValue();
        if (count($values) && !array_key_exists($value, $values)) {
            $value = reset($values);
        }

        $attribs = $this->getAttributes()->toArray();
        $attribs['name'] = $this->getName();
        $attribs['id']   = $this->getId();

        $s = '<select';
        foreach ($attribs as $k => $v) {
            $s .= " $k=\"$v\"";
        }
        $s .= '>';

        foreach ($values as $optionValue => $label) {
            $s .= '<option value="' . $optionValue . '"';
            if ($optionValue == $value) {
                $s .= ' selected="selected"';
            }
            $s .= '>' . $label . '</option>';
        }

        $s .= '</select>';

        return $s;
    }
}
