<?php
namespace Hynage\ORM\Model;

class RecordCollection implements ExportStrategy\Exportable, \Iterator, \Countable
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
    

    public function get($key, $default = null)
    {
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }
        
        return $default;
    }


    public function getRandom($default = null)
    {
        if (!$this->count()) {
            return $default;
        }

        return $this->get(array_rand($this->data), $default);
    }


    /**
     * Export this record collection. Default is array.
     *
     * @param \Hynage\ORM\Model\ExportStrategy\Exporting $strategy
     * @return mixed
     */
    public function export(ExportStrategy\Exporting $strategy = null)
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