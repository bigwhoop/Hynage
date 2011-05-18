<?php
namespace Hynage\Form\Element;

class PasswordElement extends InputElement
{
    public function renderElement()
    {
        return $this->_renderInputElement('password');
    }
}
