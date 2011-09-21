<?php
/**
 * This file is part of Hynage.
 *
 * (c) Philippe Gerber <philippe@bigwhoop.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Hynage\Test\Data;
use Hynage\Data\DataContainer;

class DataContainerTest extends \PHPUnit_Framework_TestCase
{
    public function testAppend()
    {
        $container = new DataContainer(array('foo', 'bar'));
        $container->append('baz');

        $this->assertEquals('foo bar baz', $container->getString());
    }

    public function testPrepend()
    {
        $container = new DataContainer(array('foo', 'bar'));
        $container->prepend('baz');

        $this->assertEquals('baz foo bar', $container->getString());
    }

    public function testSeparator()
    {
        $container = new DataContainer(array('foo', 'bar'));
        $container->setSeparator(' - ');

        $this->assertEquals('foo - bar', $container->getString());
    }
}