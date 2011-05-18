<?php
namespace Hynage\Validator;

abstract class AbstractValidator implements ValidatorInterface
{
    protected $_error = '';

    public function addError($e)
    {
        $this->_error = $e;
        return $this;
    }
    
    public function getError()
    {
        return $this->_error;
    }


    /**
     * Returns the translation of the given string
     *
     * @param string $string
     * @param mixed Arguments for printf
     * @return string
     */
    public function _($string)
    {
        $args = func_get_args();
        return call_user_func_array(array('\Hynage\I18n\Translator', 'translate'), $args);
    }
}