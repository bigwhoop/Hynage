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

class ImmutableValueObject
{
    /**
     * @var array
     */
    private $data = array();

    /**
     * @var array
     */
    protected $definition = array();


    /**
     * @throws \OutOfBoundsException
     * @param array $data
     */
    public function __construct(array $data)
    {
        if (empty($this->definition)) {
            $this->data = $data;
        } else {
            foreach ($this->definition as $key) {
                if (!array_key_exists($key, $data)) {
                    throw new \OutOfBoundsException('Missing property "' . $key . '".');
                }

                $this->data[$key] = $data[$key];
            }
        }
    }


    /**
     * @throws \OutOfBoundsException
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        if (!array_key_exists($key, $this->data)) {
            throw new \OutOfBoundsException('Invalid property "' . $key . '".');
        }

        return $this->data[$key];
    }


    /**
     * @throws \LogicException
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        throw new \LogicException('This object is immutable.');
    }
}
