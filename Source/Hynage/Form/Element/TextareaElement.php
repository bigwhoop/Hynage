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
