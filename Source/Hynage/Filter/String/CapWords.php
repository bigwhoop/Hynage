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

class CapWords implements FilterInterface
{
    public function filter($v)
    {
        $chunks = preg_split('/[-_. ]/', $v);
        $chunks = array_map('ucfirst', array_map('mb_strtolower', $chunks));

        $string = join('', $chunks);
        if (!empty($string)) {
            $string[0] = mb_strtoupper($string[0]);
        }
        
        return $string;
    }
}
