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
use Hynage\I18n\Translator;

class I18n extends AbstractComponent
{
    /**
     * @var \Hynage\I18n\Translator|null
     */
    private $translator = null;
    
    
    /**
     * @param \Hynage\I18n\Translator|null $translator
     */
    public function __construct(Translator $translator = null)
    {
        $this->translator = $translator;
    }
    
    
    /**
     * @return \Hynage\I18n\Translator|null
     */
    public function bootstrap()
    {
        mb_internal_encoding('utf-8');
        
        return $this->translator;
    }
}
