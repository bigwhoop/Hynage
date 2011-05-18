<?php
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
