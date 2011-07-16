<?php
namespace Hynage\ORM\Model\ExportStrategy;
use Hynage\ORM\Model\Record,
    Hynage\ORM\Model\RecordCollection,
    Hynage\Exception;

class KeyValueArrayStrategy implements Exporting
{
    private $keyFieldName = '';

    private $valueFieldName = '';


    public function __construct($keyFieldName, $valueFieldName)
    {
        $this->keyFieldName   = $keyFieldName;
        $this->valueFieldName = $valueFieldName;
    }


    public function exportRecord(Record $obj)
    {
        throw new Exception\NotImplementedException('This export does only work with record collections.');
    }


    public function exportCollection(RecordCollection $coll)
    {
        $a = array();
        
        foreach ($coll as $obj) {

            $a[$obj->getValue($this->keyFieldName)] = $obj->getValue($this->valueFieldName);
        }
        
        return $a;
    }
}