<?php
/**
 * This file is part of Hynage.
 *
 * (c) Philippe Gerber <philippe@bigwhoop.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Hynage\I18n;

class Translator
{
    /**
     * @var array
     */
    private $strings = array();
    
    /**
     * @var null|string
     */
    private $currentLanguage = null;
    
    
    /**
     * @param string $language
     * @param array $strings
     * @return Translator
     */
    public function setTranslation($language, array $strings)
    {
        $this->strings[(string)$language] = $strings;
        
        if (!$this->currentLanguage) {
            $this->setCurrentLanguage($language);
        }
        
        return $this;
    }
    
    
    /**
     * @param string $language
     * @return Translator
     */
    public function setCurrentLanguage($language)
    {
        $this->currentLanguage = (string)$language;
        return $this;
    }
    
    
    /**
     * @param string $string
     * @return string
     */
    public function translate($string)
    {
        $args = func_get_args();
        
        $string = array_shift($args);
        if (array_key_exists($this->currentLanguage, $this->strings)) {
            if (array_key_exists($string, $this->strings[$this->currentLanguage])) {
                $string = $this->strings[$this->currentLanguage][$string];
            }
        }
        
        return vsprintf($string, $args);
    }
}
