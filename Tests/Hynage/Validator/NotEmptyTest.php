<?php
/**
 * This file is part of Hynage.
 *
 * (c) Philippe Gerber <philippe@bigwhoop.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Hynage\Test\Validator;
use Hynage\Validator\NotEmpty;

class NotEmptyTest extends \PHPUnit_Framework_TestCase
{
    public function testEmpty()
    {
        $validator = new NotEmpty();

        $this->assertFalse($validator->isValid(null), "Testing null");
        $this->assertFalse($validator->isValid(0), "Testing 0");
        $this->assertFalse($validator->isValid(''), "Testing ''");
        $this->assertFalse($validator->isValid(0.0), "Testing 0.0");
        $this->assertFalse($validator->isValid('0'), "Testing '0'");
        $this->assertFalse($validator->isValid(false), "Testing false");
        $this->assertFalse($validator->isValid(array()), "Testing array()");
    }


    public function testNotEmpty()
    {
        $validator = new NotEmpty();

        $this->assertTrue($validator->isValid('foo'), "Testing 'foo'");
        $this->assertTrue($validator->isValid(array('foo')), "Testing array('foo')");
        $this->assertTrue($validator->isValid(true), "Testing true");
        $this->assertTrue($validator->isValid(1), "Testing 1");
        $this->assertTrue($validator->isValid(1.5), "Testing 1.5");
    }
}