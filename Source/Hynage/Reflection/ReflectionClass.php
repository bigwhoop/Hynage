<?php
namespace Hynage\Reflection;
use Hynage\Annotation\Parser as AnnotationParser;

class ReflectionClass extends \ReflectionClass
{
    public function getProperties($filter = null)
    {
        if (null === $filter) {
            $filter = \ReflectionProperty::IS_STATIC
                    | \ReflectionProperty::IS_PUBLIC
                    | \ReflectionProperty::IS_PROTECTED
                    | \ReflectionProperty::IS_PRIVATE;
        }
        
        $properties = array();
        foreach (parent::getProperties($filter) as $property) {
            $properties[] = new ReflectionProperty($property->class, $property->name);
        }
        
        return $properties;
    }
    
    
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