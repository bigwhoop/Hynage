<?php
/**
 * This file is part of Hynage.
 *
 * (c) Philippe Gerber <philippe@bigwhoop.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Hynage\Test\Annotation;
use Hynage\Annotation\Parser;

class ParserTest extends \PHPUnit_Framework_TestCase
{
    private $parser = null;

    public function setUp()
    {
        $comment = <<<CMT
/**
 * @ConstantAnnotation
 * @SingleValueAnnotation('foo')
 * @MultiValueAnnotation(foo='bar', baz=12)
 */
CMT;
        $this->parser = new Parser($comment);
    }

    public function testConstants()
    {
        $this->assertTrue($this->parser->hasAnnotation('ConstantAnnotation'));
        $this->assertFalse($this->parser->hasAnnotation('MissingConstantAnnotation'));
    }

    public function testSingleValueAnnotations()
    {
        $value = $this->parser->getAnnotation('SingleValueAnnotation');

        $this->assertType('string', $value);
        $this->assertEquals('foo', $value);
    }

    public function testMultipleValueAnnotations()
    {
        $value = $this->parser->getAnnotation('MultiValueAnnotation');

        $this->assertType('array', $value);

        $this->assertArrayHasKey('foo', $value);
        $this->assertType('string', $value['foo']);
        $this->assertEquals('bar', $value['foo']);


        $this->assertArrayHasKey('baz', $value);
        $this->assertType('int', $value['baz']);
        $this->assertEquals(12, $value['baz']);
    }
}