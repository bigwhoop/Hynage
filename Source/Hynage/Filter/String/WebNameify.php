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

class WebNameify implements FilterInterface
{
    /**
     * @param string $v
     * @return string
     */
    public function filter($v)
    {
        $v = mb_strtolower($v);

        $v = str_ireplace(
            array(' ', '_', 'ä', 'ö', 'ü'),
            array('-', '-', 'ae', 'oe', 'ue'),
            $v
        );

        $v = preg_replace('|[^a-zA-Z0-9\-]|i', '', $v);

        $v = str_replace('--', '-', $v);

        return $v;
    }
}
