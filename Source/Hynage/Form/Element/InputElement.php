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

class InputElement extends HtmlElement
{
    protected function _renderInputElement($type)
    {
        $attribs = $this->getAttributes()->toArray();
        $attribs = array_map('htmlentities', $attribs);
        $attribs['name']  = $this->getName();
        $attribs['id']    = $this->getId();
        $attribs['value'] = htmlentities($this->getValue(), ENT_COMPAT, 'utf-8');
        $attribs['type']  = $type;

        $s = '<input';
        foreach ($attribs as $k => $v) {
            if (null === $v) {
                $s .= " $k";
            } else {
                $s .= " $k=\"$v\"";
            }
        }
        $s .= '>';
        
        return $s;
    }
}
