<?php
namespace Hynage\Form\Element;

class ButtonElement extends InputElement
{
    public function renderLabel()
    {
        return '';
    }
    
    
    public function renderElement()
    {
        $this->setValue($this->getLabel());
        
        return $this->_renderInputElement('button');
    }
}
