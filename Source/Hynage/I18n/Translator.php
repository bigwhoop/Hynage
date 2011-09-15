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
    static public function translate($string)
    {
        // @TODO: Implement the actual translation adapter
        
        $args   = func_get_args();
        $string = array_shift($args);

        return vsprintf($string, $args);
    }
}
