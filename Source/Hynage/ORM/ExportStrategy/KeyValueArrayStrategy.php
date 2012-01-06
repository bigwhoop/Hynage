<?php
/**
 * This file is part of Hynage.
 *
 * (c) Philippe Gerber <philippe@bigwhoop.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Hynage\ORM\ExportStrategy;
use Hynage\ORM\Entity,
    Hynage\ORM\EntityCollection,
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


    public function exportEntity(Entity $obj)
    {
        throw new Exception\NotImplementedException('This export does only work with Entity collections.');
    }


    public function exportCollection(EntityCollection $coll)
    {
        $a = array();
        
        foreach ($coll as $obj) {
            $a[$obj->getValue($this->keyFieldName)] = $obj->getValue($this->valueFieldName);
        }
        
        return $a;
    }
}