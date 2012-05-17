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
    /**
     * @var null|Translator
     */
    private $translator = null;
    
    
    public function setUp()
    {
        $translator = Translator::getInstance();
        
        $translator->setTranslation('de', array('hello' => 'hallo'));
        $translator->setTranslation('fr', array('hello' => 'salu'));
        
        $this->translator = $translator;
    }
    
    
    public function testReplacement()
    {
        $this->assertEquals('abc', $this->translator->translate('abc'));
        $this->assertEquals('abc', $this->translator->translate('a%sc', 'b'));
        $this->assertEquals('abc1', $this->translator->translate('a%sc%d', 'b', 1));
    }
    
    
    public function testDefaultLanguage()
    {
        $this->assertEquals('hallo', $this->translator->translate('hello'));
    }
    
    
    public function testSpecificLanguage()
    {
        $this->translator->setCurrentLanguage('fr');
        $this->assertEquals('salu', $this->translator->translate('hello'));
    }
}