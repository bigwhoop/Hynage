<?php
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
