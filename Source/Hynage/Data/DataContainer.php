<?php
/**
 * This file is part of Hynage.
 *
 * (c) Philippe Gerber <philippe@bigwhoop.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Hynage\Data;

class DataContainer
{
    /**
     * @var array
     */
    private $data = array();

    /**
     * @var string
     */
    private $separator = ' ';


    /**
     * @param array $values
     */
    public function __construct(array $values = array())
    {
        $this->appendMultiple($values);
    }


    /**
     * @param string $v
     * @return \Hynage\Data\DataContainer
     */
    public function prepend($v)
    {
        array_unshift($this->data, $v);
        return $this;
    }


    /**
     * @param string $v
     * @return \Hynage\Data\DataContainer
     */
    public function append($v)
    {
        $this->data[] = $v;
        return $this;
    }


    /**
     * @param array $values
     * @return \Hynage\Data\DataContainer
     */
    public function appendMultiple(array $values)
    {
        foreach ($values as $v) {
            $this->append($v);
        }

        return $this;
    }


    /**
     * @param string $v
     * @return \Hynage\Data\DataContainer
     */
    public function setSeparator($v)
    {
        $this->separator = $v;
        return $this;
    }


    /**
     * @return string
     */
    public function getString()
    {
        return join($this->separator, $this->data);
    }


    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getString();
    }
}
