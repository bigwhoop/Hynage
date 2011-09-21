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
use Hynage\Data\Generic;

class GenericTest extends \PHPUnit_Framework_TestCase
{
    public function testLength()
    {
        $data = new Generic('int', array(1, 12, 32));

        $this->assertEquals(3, count($data));
        $this->assertEquals(3, $data->count());
    }

    public function testInt()
    {
        $data = new Generic('int', array(1, 12, 32));
        $this->assertType('int', $data->get(0));
        $this->assertEquals(32, $data->get(2));
    }

    public function testBadInt()
    {
        $this->setExpectedException(
            '\Hynage\Exception\InvalidArgumentException',
            'Argument must be of type "int".'
        );

        new Generic('int', array(1, 12, '32'));
    }

    public function testString()
    {
        $data = new Generic('string', array('foo', 'bar', 'baz'));
        $this->assertType('string', $data->get(0));
        $this->assertEquals('bar', $data->get(1));
    }

    public function testBadString()
    {
        $this->setExpectedException(
            '\Hynage\Exception\InvalidArgumentException',
            'Argument must be of type "string".'
        );

        new Generic('string', array('foo', 12, 'bar'));
    }

    public function testArray()
    {
        $data = new Generic(
            'array',
            array(
                array(1, 12, 32),
                array('foo', 'bar'),
            )
        );

        $this->assertType('array', $data->get(0));
        $this->assertType('array', $data->get(1));
    }

    public function testBadArray()
    {
        $this->setExpectedException(
            '\Hynage\Exception\InvalidArgumentException',
            'Argument must be of type "array".'
        );

        new Generic('array', array('foo'));
    }

    public function testObject()
    {
        $data = new Generic(
            '\Hynage\Data\Generic',
            array(
                new Generic('int'),
                new Generic('string'),
            )
        );

        $this->assertType('\Hynage\Data\Generic', $data->get(0));
        $this->assertType('\Hynage\Data\Generic', $data->get(1));
    }

    public function testBadObject()
    {
        $this->setExpectedException(
            '\Hynage\Exception\InvalidArgumentException',
            'Argument must be of type "\Hynage\Data\Generic".'
        );

        new Generic('\Hynage\Data\Generic', array('foo'));
    }


    public function testSetters()
    {
        $data = new Generic('string');
        $data->set('foo', 'bar');

        $this->assertEquals('bar', $data->get('foo'));
    }


    public function testInteration()
    {
        $data = new Generic('string', array('foo', 'bar'));

        $s = '';
        foreach ($data as $val) {
            $s .= $val;
        }

        $this->assertEquals('foobar', $s);
    }
}