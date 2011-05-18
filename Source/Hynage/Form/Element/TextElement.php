<?php
namespace Hynage\Form\Element;

class TextElement extends InputElement
{
    public function renderElement()
    {
        return $this->_renderInputElement('text');
    }
}
