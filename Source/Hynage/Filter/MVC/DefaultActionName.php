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
use Hynage\Filter\FilterInterface;

class DefaultActionName implements FilterInterface
{
    public function filter($v)
    {
        $chunks = preg_split('/[-.]/', $v);
        $chunks = array_map('ucfirst', array_map('strtolower', $chunks));

        return lcfirst(join('', $chunks) . 'Action');
    }
}
