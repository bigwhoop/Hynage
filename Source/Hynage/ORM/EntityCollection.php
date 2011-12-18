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


    public function getFirst($default = false)
    {
        if (!$this->count()) {
            return $default;
        }

        return array_pop($this->data);
    }


    /**
     * Export this Entity collection. Default is array.
     *
     * @param \Hynage\ORM\ExportStrategy\Exporting $strategy
     * @return mixed
     */
    public function export(ExportStrategy\Exporting $strategy = null)
    {
        if (!$strategy) {
            $strategy = new ExportStrategy\ArrayStrategy();
        }

        return $strategy->exportCollection($this);
    }


    public function toKeyValueArray($keyFieldName, $valueFieldName)
    {
        return $this->export(new ExportStrategy\KeyValueArrayStrategy($keyFieldName, $valueFieldName));
    }


    public function count()
    {
        return count($this->data);
    }


    public function rewind()
    {
        reset($this->data);
    }


    public function next()
    {
        return next($this->data);
    }


    public function valid()
    {
        return null !== $this->key();
    }


    public function key()
    {
        return key($this->data);
    }


    public function current()
    {
        return current($this->data);
    }
}