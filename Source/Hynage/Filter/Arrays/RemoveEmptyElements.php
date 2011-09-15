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
    public function filter($v)
    {
        if (!is_array($v)) {
            throw new InvalidArgumentException('Argument 1 must be an array.');
        }

        return array_filter($v, function($e) {
            return !empty($e);
        });
    }
}
