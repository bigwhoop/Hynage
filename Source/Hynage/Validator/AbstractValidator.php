<?php
/**
 * This file is part of Hynage.
 *
 * (c) Philippe Gerber <philippe@bigwhoop.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Hynage\Validator;
use Hynage\I18n\Translator;

abstract class AbstractValidator implements ValidatorInterface
{
    /**
     * @var string
     */
    protected $_error = '';

    /**
     * @var Translator|null
     */
    private $translator = null;

    
    /**
     * @param string $e
     * @return AbstractValidator
     */
    public function addError($e)
    {
        $this->_error = (string)$e;
        return $this;
    }
    
    
    /**
     * @return string
     */
    public function getError()
    {
        return $this->_error;
    }
    
    
    /**
     * @param \Hynage\I18n\Translator $translator
     * @return AbstractValidator
     */
    public function setTranslator(Translator $translator)
    {
        $this->translator = $translator;
        return $this;
    }


    /**
     * Returns the translation of the given string
     *
     * @param string $string [more arguments for printf-like placeholders)
     * @return string
     */
    public function _($string)
    {
        if (!$this->translator) {
            throw new \RuntimeException('No translator available.'); 
        }
        
        $args = func_get_args();
        return call_user_func_array(array($this->translator, 'translate'), $args);
    }
}