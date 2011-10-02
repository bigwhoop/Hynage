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
    Hynage\ORM\Model\Record;

class Database extends AbstractComponent
{
    /**
     * @var \Hynage\Config|null
     */
    private $config = null;


    /**
     * @param \Hynage\Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }


    /**
     * @return \Hynage\Database\Connection
     */
    public function bootstrap()
    {
        $connection = new Connection($this->config->get('uri'));
        Record::setConnection($connection);

        return $connection;
    }
}
