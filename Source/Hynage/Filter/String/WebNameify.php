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
        $v = str_replace(
            array(' ', '_', 'ä', 'ö', 'ü', 'Ä', 'Ö', 'Ü'),
            array('-', '-', 'ae', 'oe', 'ue', 'ae', 'oe', 'ue'),
            $v
        );
        
        $v = mb_strtolower($v);
        
        $v = preg_replace('|[^a-zA-Z0-9\-]|i', '', $v);

        while (false !== strpos($v, '--')) {
            $v = str_replace('--', '-', $v);
        }

        return $v;
    }
}
