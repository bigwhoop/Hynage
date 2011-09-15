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

class PasswordElement extends InputElement
{
    public function renderElement()
    {
        return $this->_renderInputElement('password');
    }
}
