<?php
/**
 * This file is part of Hynage.
 *
 * (c) Philippe Gerber <philippe@bigwhoop.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Hynage\Test\Filter\String;
use Hynage\Filter\String\WebNameify;

class WebNameifyTest extends \PHPUnit_Framework_TestCase
{
    public function testUpperLowerCase()
    {
        $filter = new WebNameify();
        $this->assertEquals('tests', $filter->filter('TEsTs'));
    }

    public function testSpaces()
    {
        $filter = new WebNameify();
        $this->assertEquals('test', $filter->filter('test'));
        $this->assertEquals('test-test', $filter->filter('test test'));
        $this->assertEquals('test-test', $filter->filter('test   test'));
    }

    public function testUmlauts()
    {
        $filter = new WebNameify();
        $this->assertEquals('oeaeue', $filter->filter('ÖÄÜ'));
        $this->assertEquals('oeaeue', $filter->filter('öäü'));
    }

    public function testDashes()
    {
        $filter = new WebNameify();
        $this->assertEquals('tom-jones', $filter->filter('tom --- jones'));
        $this->assertEquals('tom-jones', $filter->filter('tom _-_-_ jones'));
    }

    public function testBadCharacters()
    {
        $filter = new WebNameify();
        $this->assertEquals('tom-jones-1239', $filter->filter('tom $!:,.+"*ç%&/()=?``\'#@¦°§¬|¢^; jones 1239'));
    }
}
