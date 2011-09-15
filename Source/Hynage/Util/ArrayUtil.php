<?php
/**
 * This file is part of Hynage.
 *
 * (c) Philippe Gerber <philippe@bigwhoop.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Hynage\Util;

class ArrayUtil
{
    static public function stripEmptyElements(array $a)
    {
        foreach ($a as $k => $v) {
            if (is_scalar($v)) {
                $v = trim($v);
            }

            if (empty($v)) {
                unset($a[$k]);
            }
        }

        return $a;
    }
}
