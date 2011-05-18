<?php
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