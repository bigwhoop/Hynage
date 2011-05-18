<?php
namespace Hynage\Form\Element;

class TextareaElement extends HtmlElement
{
    public function renderElement()
    {
        $attribs = $this->getAttributes()->toArray();
        $attribs['name'] = $this->getName();
        $attribs['id']   = $this->getId();

        $s = '<textarea';
        foreach ($attribs as $k => $v) {
            $s .= " $k=\"$v\"";
        }
        $s .= '>' . $this->getValue() . '</textarea>';
        
        return $s;
    }
}
