<?php
/**
 * This file is part of Hynage.
 *
 * (c) Philippe Gerber <philippe@bigwhoop.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Hynage\Application\Component;

class I18n extends AbstractComponent
{
    public function bootstrap()
    {
        mb_internal_encoding('utf-8');
    }
}
