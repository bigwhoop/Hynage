<?php
/**
 * This file is part of Hynage.
 *
 * (c) Philippe Gerber <philippe@bigwhoop.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Hynage\Test\I18n;
use Hynage\I18n\Translator;

class TranslatorTest extends \PHPUnit_Framework_TestCase
{
    public function testReplacement()
    {
        $translator = new Translator();

        $this->assertEquals('abc', $translator->translate('abc'));
        $this->assertEquals('abc', $translator->translate('a%sc', 'b'));
        $this->assertEquals('abc1', $translator->translate('a%sc%d', 'b', 1));
    }
}