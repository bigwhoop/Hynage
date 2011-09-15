<?php
/**
 * This file is part of Hynage.
 *
 * (c) Philippe Gerber <philippe@bigwhoop.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Hynage\Reflection;
use Hynage\Annotation\Parser as AnnotationParser;

class ReflectionProperty extends \ReflectionProperty
{
    public function getAnnotation($key)
    {
        return $this->_getAnnontationsParser()->getAnnotation($key);
    }
    
    
    public function hasAnnotation($key)
    {
        return $this->_getAnnontationsParser()->hasAnnotation($key);
    }
    
    
    protected function _getAnnontationsParser()
    {
        $comment = $this->getDocComment();
        return new AnnotationParser($comment);
    }
}