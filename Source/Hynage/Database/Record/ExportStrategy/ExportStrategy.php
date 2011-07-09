<?php
namespace Hynage\Database\Record\ExportStrategy;
use Hynage\Database\Record,
    Hynage\Database\RecordCollection;

interface ExportStrategy
{
    public function exportRecord(Record $obj);
    public function exportCollection(RecordCollection $coll);
}