<?php
/**
 * This file is part of Hynage.
 *
 * (c) Philippe Gerber <philippe@bigwhoop.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Hynage\Test;
use Hynage\Config;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    private $configData = array();
    

    public function setUp()
    {
        $this->configData = array(
            'str'   => 'string',
            'int'   => 5,
            'float' => 12.534,
            'true'  => true,
            'false' => false,
            'array' => array(),
            'func'  => function($x) { return $x * 2; }
        );
    }


    public function testTypes()
    {
        $config = new Config($this->configData);

        $this->assertInternalType('string', $config->get('str'));
        $this->assertInternalType('int', $config->get('int'));
        $this->assertInternalType('float', $config->get('float'));
        $this->assertInternalType('bool', $config->get('true'));
        $this->assertTrue($config->get('true'));
        $this->assertInternalType('bool', $config->get('false'));
        $this->assertFalse($config->get('false'));
        $this->assertInternalType('array', $config->get('array')->getData());
        $this->assertInstanceOf('\Closure', $config->get('func'));
    }


    public function testSetterAndGetter()
    {
        $config = new Config();

        $config->set('foo', 'bar');
        $this->assertEquals('bar', $config->get('foo'));
    }


    public function testMagicSetterAndGetter()
    {
        $config = new Config();

        $config->foo = 'baz';
        $this->assertEquals('baz', $config->foo);
    }


    public function testArraySettingAndGetting()
    {
        $config = new Config();

        $config->set('foo', array('bar' => 'baz'));
        $this->assertEquals('baz', $config->get('foo')->get('bar'));
        $this->assertEquals('baz', $config->get('foo.bar'));
        $this->assertEquals('baz', $config->get('foo_bar', false, '_'));
    }


    public function testDefaultValues()
    {
        $config = new Config();

        $this->assertNull($config->get('foo'));
        $this->assertNull($config->foo);

        $this->assertFalse($config->get('foo', false));
        $this->assertEquals('bar', $config->get('foo', 'bar'));
    }


    public function testIteration()
    {
        $config = new Config(array('one', 'two', 'three'));

        $all = '';
        foreach ($config as $value) {
            $all .= $value;
        }

        $this->assertEquals('onetwothree', $all);
    }
}