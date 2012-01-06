<?php
/**
 * This file is part of Hynage.
 *
 * (c) Philippe Gerber <philippe@bigwhoop.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Hynage\ORM\Entity;
use Hynage\ORM\Entity,
    Hynage\ORM\EntityManager,
    Hynage\ORM\EntityCollection;

class Proxy
{
    const TYPE_ENTITY     = 'entity';
    const TYPE_COLLECTION = 'collection';


    /**
     * @var \Hynage\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Hynage\ORM\Entity
     */
    private $entity;

    /**
     * @var string
     */
    private $className;

    /**
     * @var string
     */
    private $localFieldName;

    /**
     * @var string
     */
    private $foreignFieldName;

    /**
     * @var string
     */
    private $type;

    /**
     * @var \Hynage\ORM\Entity|\Hynage\ORM\EntityCollection
     */
    private $data = null;
    

    /**
     * @param \Hynage\ORM\EntityManager $em
     * @param \Hynage\ORM\Entity $entity
     * @param string $className
     * @param string $localFieldName
     * @param string $foreignFieldName
     * @param string $type
     */
    public function __construct(EntityManager $em, Entity $entity, $className, $localFieldName, $foreignFieldName, $type)
    {
        $this->em               = $em;
        $this->entity           = $entity;
        $this->className        = $className;
        $this->localFieldName   = $localFieldName;
        $this->foreignFieldName = $foreignFieldName;
        $this->type             = $type;
    }
    

    /**
     * @return \Hynage\ORM\EntityCollection|\Hynage\ORM\Entity
     */
    public function load()
    {
        if ($this->data) {
            return $this->data;
        }

        $constraints = array(
            $this->foreignFieldName => $this->entity->getValue($this->localFieldName),
        );

        $type = $this->type;

        switch ($type)
        {
            case self::TYPE_ENTITY:
                $this->data = $this->em->findEntityBy($this->className, $constraints);
                break;

            case self::TYPE_COLLECTION:
                $this->data = $this->em->findEntitiesBy($this->className, $constraints);
                break;

            default:
                throw new \InvalidArgumentException("Invalid proxy type '$type'.");
        }

        return $this->data;
    }
}