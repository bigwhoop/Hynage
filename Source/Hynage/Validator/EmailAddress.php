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

class EmailAddress extends AbstractValidator
{
    public function isValid($v)
    {
        if (!filter_var($v, FILTER_VALIDATE_EMAIL)) {
            $this->addError($this->_('Value must be a valid email address.'));
            return false;
        }

        return true;
    }
}