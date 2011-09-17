<?php
/**
 * This file is part of Hynage.
 *
 * (c) Philippe Gerber <philippe@bigwhoop.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Hynage\Test\Filter\MVC;
use Hynage\Filter\MVC\DefaultActionName;

class DefaultActionNameTest extends \PHPUnit_Framework_TestCase
{
    public function testFilter()
    {
        $filter = new DefaultActionName();

        $this->assertEquals('testAction', $filter->filter('test'));
        $this->assertEquals('secondTestAction', $filter->filter('second-test'));
    }
}