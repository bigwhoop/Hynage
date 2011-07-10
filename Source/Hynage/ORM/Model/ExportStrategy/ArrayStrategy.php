<?php
namespace Hynage\ORM\Model\ExportStrategy;
use Hynage\ORM\Model\Record,
    Hynage\ORM\Model\RecordCollection;

class ArrayStrategy implements Exportable
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