<?php
/**
 * This file is part of Hynage.
 *
 * (c) Philippe Gerber <philippe@bigwhoop.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Hynage\ORM;

class EntityRepository
{
    /**
     * @var \Hynage\ORM\EntityManager
     */
    private $em;


    /**
     * @param \Hynage\ORM\EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }


    /**
     * @return \Hynage\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->em;
    }
}