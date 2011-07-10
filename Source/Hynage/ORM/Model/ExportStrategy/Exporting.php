<?php
namespace Hynage\ORM\Model\ExportStrategy;
use Hynage\ORM\Model\Record,
    Hynage\ORM\Model\RecordCollection;

interface Exporting
{
    public function exportRecord(Record $obj);
    public function exportCollection(RecordCollection $coll);
}