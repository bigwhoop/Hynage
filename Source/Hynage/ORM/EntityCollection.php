<?php
/**
 * This file is part of Hynage.
 *
 * (c) Philippe Gerber <philippe@bigwhoop.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Hynage\ORM;
use Hynage\Data\Generic;

class EntityCollection extends Generic implements ExportStrategy\Exportable
{
    /**
     * @param array $data
     * @param string $type
     */
    public function __construct(array $data = array(), $type = '\\Hynage\\ORM\\Entity')
    {
        parent::__construct($type, $data);
    }


    /**
     * @param \Hynage\ORM\Entity|mixed $default
     * @return null
     */
    public function getRandom($default = null)
    {
        if (!$this->count()) {
            return $default;
        }

        return $this->get(array_rand($this->data), $default);
    }
    
    
    /**
     * @return EntityCollection
     */
    public function randomize()
    {
        shuffle($this->data);
        return $this;
    }


    /**
     * @param mixed $default
     * @return mixed
     */
    public function getFirst($default = false)
    {
        if (!$this->count()) {
            return $default;
        }

        return array_pop($this->data);
    }
    
    
    /**
     * @return array
     */
    public function toArray()
    {
        return $this->export(new ExportStrategy\ArrayStrategy());
    }


    /**
     * Export this Entity collection. Default is array.
     *
     * @param \Hynage\ORM\ExportStrategy\Exporting $strategy
     * @return mixed
     */
    public function export(ExportStrategy\Exporting $strategy)
    {
        return $strategy->exportCollection($this);
    }


    /**
     * @param string $keyFieldName
     * @param string $valueFieldName
     * @return array
     */
    public function toKeyValueArray($keyFieldName, $valueFieldName)
    {
        return $this->export(new ExportStrategy\KeyValueArrayStrategy($keyFieldName, $valueFieldName));
    }


    /**
     * @return int
     */
    public function count()
    {
        return count($this->data);
    }


    public function rewind()
    {
        reset($this->data);
    }


    /**
     * @return mixed
     */
    public function next()
    {
        return next($this->data);
    }


    /**
     * @return bool
     */
    public function valid()
    {
        return null !== $this->key();
    }


    /**
     * @return string|int|null
     */
    public function key()
    {
        return key($this->data);
    }


    /**
     * @return mixed
     */
    public function current()
    {
        return current($this->data);
    }
}