<?php
/**
 * This file is part of Hynage.
 *
 * (c) Philippe Gerber <philippe@bigwhoop.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Hynage\Filter\Arrays;
use Hynage\Filter\FilterInterface,
    Hynage\Exception\InvalidArgumentException;

class RemoveEmptyElements implements FilterInterface
{
    /**
     * @var bool
     */
    private $resetKeys = true;


    /**
     * @param bool $b
     * @return RemoveEmptyElements
     */
    public function setResetKeys($b)
    {
        $this->resetKeys = (bool)$b;
        return $this;
    }


    /**
     * @throws \Hynage\Exception\InvalidArgumentException
     * @param array $v
     * @return array
     */
    public function filter($v)
    {
        if (!is_array($v)) {
            throw new InvalidArgumentException('Argument 1 must be an array.');
        }

        $v = array_filter($v, function($e) {
            return !empty($e);
        });

        if ($this->resetKeys) {
            $v = array_values($v);
        }

        return $v;
    }
}
