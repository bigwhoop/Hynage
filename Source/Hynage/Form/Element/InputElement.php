<?php
namespace Hynage\Form\Element;

class InputElement extends HtmlElement
{
    protected function _renderInputElement($type)
    {
        $attribs = $this->getAttributes()->toArray();
        $attribs['name']  = $this->getName();
        $attribs['id']    = $this->getId();
        $attribs['value'] = $this->getValue();
        $attribs['type']  = $type;

        $s = '<input';
        foreach ($attribs as $k => $v) {
            $s .= " $k=\"$v\"";
        }
        $s .= '>';
        
        return $s;
    }
}
