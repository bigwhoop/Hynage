<?php
/**
 * This file is part of Hynage.
 *
 * (c) Philippe Gerber <philippe@bigwhoop.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Hynage\ORM\Model\ExportStrategy;
use Hynage\ORM\Model\Record,
    Hynage\ORM\Model\RecordCollection;

interface Exporting
{
    public function exportRecord(Record $obj);
    public function exportCollection(RecordCollection $coll);
}