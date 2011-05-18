<?php
namespace Hynage\I18n;

class Translator
{
    static public function translate($string)
    {
        // @TODO: Implement the actual translation adapter
        
        $args   = func_get_args();
        $string = array_shift($args);

        return vsprintf($string, $args);
    }
}
