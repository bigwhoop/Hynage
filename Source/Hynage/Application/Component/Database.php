<?php
/**
 * This file is part of Hynage.
 *
 * (c) Philippe Gerber <philippe@bigwhoop.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Hynage\Application\Component;
use Hynage\Config,
    Hynage\Database\Connection,
    Hynage\ORM\Entity,
    Hynage\ORM\Persistence\DatabasePersistence,
    Hynage\ORM\EntityManager;

class Database extends AbstractComponent
{
    /**
     * @var \Hynage\Config|null
     */
    private $config = null;

    /**
     * @var null|\Hynage\ORM\EntityManager
     */
    private $em = null;


    /**
     * @param \Hynage\Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }


    /**
     * @return \Hynage\ORM\EntityManager
     */
    public function bootstrap()
    {
        $connection  = new Connection($this->config->get('uri'));
        $dbPersister = new DatabasePersistence($connection);

        $em = new EntityManager();
        $em->addPersister('database', $dbPersister);

        if ($this->config->has('entityNameFormatter')) {
            $em->setEntityNameFormatter($this->config->get('entityNameFormatter'));
        }

        if ($this->config->has('repositoryNameFormatter')) {
            $em->setRepositoryNameFormatter($this->config->get('repositoryNameFormatter'));
        }

        $this->em = $em;

        return $em;
    }


    /**
     * @return \Hynage\ORM\EntityManager|null
     */
    public function getEntityManager()
    {
        return $this->em;
    }
}
