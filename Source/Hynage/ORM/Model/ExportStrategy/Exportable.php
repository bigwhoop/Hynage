<?php
namespace Hynage\ORM\Model\ExportStrategy;

interface Exportable
{
    public function export(Exporting $strategy);
}