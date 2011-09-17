<?php
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

        $this->assertType('string', $config->get('str'));
        $this->assertType('int', $config->get('int'));
        $this->assertType('float', $config->get('float'));
        $this->assertType('bool', $config->get('true'));
        $this->assertTrue($config->get('true'));
        $this->assertType('bool', $config->get('false'));
        $this->assertFalse($config->get('false'));
        $this->assertType('array', $config->get('array')->getData());
        $this->assertType('\Closure', $config->get('func'));
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
}
