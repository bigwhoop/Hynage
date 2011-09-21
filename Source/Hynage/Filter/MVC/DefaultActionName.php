<?php
/**
 * This file is part of Hynage.
 *
 * (c) Philippe Gerber <philippe@bigwhoop.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Hynage\Filter\MVC;
use Hynage\Filter\String\CapWords;

class DefaultActionName extends CapWords
{
    public function filter($v)
    {
        if (empty($v)) {
            return '';
        }

        $v = parent::filter($v);
        $v[0] = mb_strtolower($v[0]);

        return $v . 'Action';
    }
}
