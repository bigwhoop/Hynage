<?php
namespace Hynage\Form\Element;

class EmailElement extends InputElement
{
    public function renderElement()
    {
        return $this->_renderInputElement('email');
    }
}
