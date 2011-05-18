<?php
namespace Hynage\Validator;

class NotEmpty extends AbstractValidator
{
    public function isValid($v)
    {
        if (empty($v)) {
            $this->addError($this->_('Value must not be empty.'));
            return false;
        }

        return true;
    }
}