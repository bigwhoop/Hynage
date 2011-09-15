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

class MultiElement extends InputElement
{
    /**
     * @var array
     */
    protected $_values = array();


    /**
     * @param array $values
     * @return \Hynage\Form\Element\RadioElement
     */
    public function setValues(array $values)
    {
        $this->_values = $values;
        return $this;
    }


    /**
     * @return array
     */
    public function getValues()
    {
        return $this->_values;
    }
}
