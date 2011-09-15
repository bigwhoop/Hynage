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

class SelectElement extends MultiElement
{
    public function setMultiSelectable($bool)
    {
        if ($bool) {
            $this->setAttribute('multiple', null);
        } else {
            $this->removeAttribute('multiple');
        }

        return $this;
    }


    public function isMultiSelectable()
    {
        return $this->getAttributes()->has('multiple');
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

        if ($this->isMultiSelectable()) {
            $attribs['name'] .= '[]';
        }

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
