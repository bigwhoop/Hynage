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
use Hynage\Exception\InvalidArgumentException;

class Generic implements \Iterator, \Countable
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var array
     */
    protected $data = array();


    /**
     * @param string $type
     * @param array $data
     */
    public function __construct($type, array $data = array())
    {
        $this->type = (string)$type;
        $this->setData($data);
    }


    /**
     * @param array $data
     * @return Generic
     */
    public function setData(array $data)
    {
        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }

        return $this;
    }


    public function set($key, $obj)
    {
        $this->checkType($obj);

        $this->data[(string)$key] = $obj;
        return $this;
    }


    public function add($obj)
    {
        $this->checkType($obj);

        $this->data[] = $obj;
        return $this;
    }


    public function get($key, $default = null)
    {
        $key = (string)$key;
        
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }

        return $default;
    }


    private function checkType($obj, $escalate = true)
    {
        switch ($this->type)
        {
            case 'string':
                $valid = is_string($obj);
                break;

            case 'array':
                $valid = is_array($obj);
                break;

            case 'bool':
            case 'boolean':
                $valid = is_bool($obj);
                break;

            case 'double':
                $valid = is_double($obj);
                break;

            case 'real':
                $valid = is_real($obj);
                break;

            case 'float':
                $valid = is_float($obj);
                break;

            case 'int':
            case 'integer':
                $valid = is_int($obj);
                break;

            case 'object':
                $valid = is_object($obj);
                break;

            default:
                $valid = $obj instanceof $this->type;
                break;
        }

        if (!$valid && $escalate) {
            throw new InvalidArgumentException('Argument must be of type "' . $this->type . '".');
        }

        return $valid;
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
