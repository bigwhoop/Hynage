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
use Hynage\Filter\String\CapWords;

class CapWordsTest extends \PHPUnit_Framework_TestCase
{
    public function testFilter()
    {
        $filter = new CapWords();

        $this->assertEquals('test', $filter->filter('TEsT'));
        $this->assertEquals('secondTest', $filter->filter('Second-tesT'));
        $this->assertEquals('theThirdTest', $filter->filter('the_third-test'));
        $this->assertEquals('theFinalForthTest', $filter->filter('the_final forth-test'));
    }
}
