<?php
/**
 * This file is part of Hynage.
 *
 * (c) Philippe Gerber <philippe@bigwhoop.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Hynage\Test\Crypto;
use Hynage\Crypto\Bcrypt;

class BcryptTest extends \PHPUnit_Framework_TestCase
{
    const PLAIN  = 'test1234';

    public function testHashing()
    {
        $bcrypt = new Bcrypt();

        $hash = $bcrypt->hashPassword(self::PLAIN, 8);

        $this->assertType('string', $hash);
        $this->assertStringStartsWith('$2a$08$', $hash);
        $this->assertEquals(60, mb_strlen($hash));

        return $hash;
    }

    /**
     * @depends testHashing
     */
    public function testVerifying($hash)
    {
        $bcrypt = new Bcrypt();

        $this->assertTrue($bcrypt->verifyPassword(self::PLAIN, $hash));
    }
}