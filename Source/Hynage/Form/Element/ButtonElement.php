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
