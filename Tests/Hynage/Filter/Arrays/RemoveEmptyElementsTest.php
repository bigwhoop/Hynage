<?php
/**
 * This file is part of Hynage.
 *
 * (c) Philippe Gerber <philippe@bigwhoop.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Hynage\Test\Filter\Arrays;
use Hynage\Filter\Arrays\RemoveEmptyElements;

class RemoveEmptyElementsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private $testData = array();


    public function setUp()
    {
        $this->testData = array('foo', '', false, 0, true, '0', 0.0, 0.3, 1, false);
    }


    public function testNonArray()
    {
        $filter = new RemoveEmptyElements();

        $this->setExpectedException('Hynage\Exception\InvalidArgumentException', 'Argument 1 must be an array.');
        $filter->filter('foo');
    }


    public function testRemoving()
    {
        $filter = new RemoveEmptyElements();

        $filtered = $filter->filter($this->testData);

        $this->assertEquals(4, count($filtered));
        $this->assertEquals('foo', $filtered[0]);
        $this->assertEquals(true, $filtered[1]);
        $this->assertEquals(0.3, $filtered[2]);
        $this->assertEquals(1, $filtered[3]);
    }


    public function testRemovingWithoutReindexing()
    {
        $filter = new RemoveEmptyElements();
        $filter->setResetKeys(false);

        $filtered = $filter->filter($this->testData);

        $this->assertEquals(4, count($filtered));
        $this->assertEquals('foo', $filtered[0]);
        $this->assertEquals(true, $filtered[4]);
        $this->assertEquals(0.3, $filtered[7]);
        $this->assertEquals(1, $filtered[8]);
    }
}