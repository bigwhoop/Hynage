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
    Hynage\ORM\EntityCollection;

interface Exporting
{
    public function exportEntity(Entity $obj);
    public function exportCollection(EntityCollection $coll);
}