<?php
namespace Hynage\Filter\String;
use Hynage\Filter\FilterInterface;

class CapWords implements FilterInterface
{
    public function filter($v)
    {
        $chunks = preg_split('/[-_. ]/', $v);
        $chunks = array_map('ucfirst', array_map('strtolower', $chunks));

        return join('', $chunks);
    }
}
