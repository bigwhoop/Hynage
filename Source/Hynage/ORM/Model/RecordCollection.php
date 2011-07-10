<?php
namespace Hynage\ORM\Model;

class RecordCollection implements \Iterator, \Countable
{
    private $data = array();

    public function __construct(array $data = array())
    {
        $this->data = $data;
    }


    public function add(Record $obj, $key = null)
    {
        if (null === $key) {
            $this->data[] = $obj;
        } else {
            $this->data[$key] = $obj;
        }

        return $this;
    }


    /**
     * Export this record collection. Default is array.
     *
     * @param \Hynage\ORM\Model\ExportStrategy\Exportable $strategy
     * @return mixed
     */
    public function export(ExportStrategy\Exportable $strategy = null)
    {
        if (!$strategy) {
            $strategy = new ExportStrategy\ArrayStrategy();
        }

        return $strategy->exportCollection($this);
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