<?php
namespace Hynage\Form\Element;

class RadioElement extends MultiElement
{
    public function renderElement()
    {
        $values = $this->getValues();
        $value  = $this->getValue();
        if (count($values) && !array_key_exists($value, $values)) {
            $value = reset($values);
        }

        $attribs = $this->getAttributes()->toArray();

        $s = '';
        foreach ($values as $optionValue => $label) {
            $attribs['name'] = $this->getName();
            $attribs['id']   = $this->getId() . '-' . $optionValue;

            $s .= ' <input type="radio" value="' . $optionValue . '"';

            foreach ($attribs as $k => $v) {
                $s .= " $k=\"$v\"";
            }
            
            if ($optionValue == $value) {
                $s .= ' checked="checked"';
            }
            $s .= '> <label for="' . $attribs['id'] . '">' . $label . '</label>';
        }

        return $s;
    }
}
