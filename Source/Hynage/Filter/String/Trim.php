<?php
/**
 * This file is part of Hynage.
 *
 * (c) Philippe Gerber <philippe@bigwhoop.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Hynage\Filter\String;
use Hynage\Filter\FilterInterface;

class Trim implements FilterInterface
{
    public function filter($v)
    {
        return trim($v);
    }
}
