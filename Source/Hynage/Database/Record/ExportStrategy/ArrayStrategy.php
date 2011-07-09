<?php
namespace Hynage\Database\Record\ExportStrategy;
use Hynage\Database\Record,
    Hynage\Database\RecordCollection;

class ArrayStrategy implements ExportStrategy
{
    public function exportRecord(Record $obj)
    {
        $class = get_class($obj);

        $a = array();

        foreach ($class::getFieldDefinitions() as $field) {
            $a[$field->getName()] = $obj->getValue($field);
        }

        return $a;
    }


    public function exportCollection(RecordCollection $coll)
    {
        $a = array();
        
        foreach ($coll as $obj) {
            $a[] = $obj->export($this);
        }
        
        return $a;
    }
}