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

abstract class AbstractValidator implements ValidatorInterface
{
    /**
     * @var string
     */
    protected $_error = '';

    
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
     * Returns the translation of the given string
     *
     * @param string $string [more arguments for printf-like placeholders)
     * @return string
     */
    public function _($string)
    {
        $args = func_get_args();
        $translator = \Hynage\I18n\Translator::getInstance();
        return call_user_func_array(array($translator, 'translate'), $args);
    }
}