<?php
namespace Hynage\Annotation;

class Parser
{
    protected $_values = array();
    
    
    public function __construct($comment)
    {
        $this->_values = $this->_parse($comment);
    }
    
    
    public function hasAnnotation($key)
    {
        return array_key_exists($key, $this->_values);
    }
    
    
    public function getAnnotation($key, $defaultValue = null)
    {
        if ($this->hasAnnotation($key)) {
            return $this->_values[$key];
        }
        
        return $defaultValue;
    }
    
    
    protected function _parse($comment)
    {
        $commentParts = explode('@', $comment);
        
        if (count($commentParts) <= 1) {
            return array();
        }
        
        // First element is just the start of the comment block
        array_shift($commentParts);
        
        $values = array();
        foreach ($commentParts as $line) {
            $annotation = trim($line, " \t\n\r\0\x0B*/");
            
            $result = $this->_parseAnnotation($annotation);
            $values[$result['key']] = $result['value'];
        }
        
        return $values;
    }
    
    
    protected function _parseAnnotation($string)
    {
        // Simple annotation
        if (false === strpos($string, '(')) {
            $key = $string;
            $value = null;
        }
        
        // More complex annotation
        else {
            list($key, $value) = explode('(', $string);
            $value = rtrim($value, ')');
            $value = $this->_parseAnnotationValue($value);
        } 
        
        return array('key' => $key, 'value' => $value);
    }
    
    
    protected function _parseAnnotationValue($value)
    {
        // Empty annotation
        if (!strlen($value)) {
            return null;
        }
        
        // Double quoted string
        if ('"' == $value[0]) {
            return trim($value, '"');
        }
        
        // Single quoted string
        if ("'" == $value[0]) {
            return trim($value, "'");
        }
        
        // Numberic value
        if (is_numeric($value)) {
            // Integer
            if (false === strpos($value, '.')) {
                return (int)$value;
            }
            
            // Float
            return (float)$value;
        }
        
        // True
        if ('true' === $value) {
            return true;
        }
        
        // False
        if ('false' === $value) {
            return false;
        }
        
        // Null
        if ('null' === $value) {
            return null;
        }
        
        // Pair annotation
        $values = explode(', ', $value);
        $values = array_map('trim', $values);

        $value = array();
        foreach ($values as $string) {
            if (false === strpos($string, '=')) {
                $value[] = $string;
            } else {
                list($key, $val) = explode('=', $string, 2);
                $value[$key] = $this->_parseAnnotationValue($val);
            }
        }
        
        return $value;
    }
}