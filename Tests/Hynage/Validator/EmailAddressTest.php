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
use Hynage\Validator\EmailAddress;

class EmailAddressTest extends \PHPUnit_Framework_TestCase
{
    public function testValid()
    {
        $validator = new EmailAddress();

        $addresses = array(
            'philippe@bigwhoop.ch',
            'Tom_Jones@test-com.co.uk',
        );
        
        foreach ($addresses as $address) {
            $this->assertTrue($validator->isValid($address), "Testing '$address'.");
        }
    }


    public function testInvalid()
    {
        $validator = new EmailAddress();
        
        $addresses = array(
            'foo',
            '',
            'asd  asdasd@asdasd.de',
            'aasd@',
            '@asd.com',
            '-@-.-',
        );
        
        foreach ($addresses as $address) {
            $this->assertTrue($validator->isValid($address), "Testing '$address'.");
        }
    }
}