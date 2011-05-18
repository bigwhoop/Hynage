<?php
namespace Hynage\Validator;

interface ValidatorInterface
{
    public function getError();
    public function isValid($v);
}
