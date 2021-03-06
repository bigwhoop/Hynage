<?php
/**
 * This file is part of Hynage.
 *
 * (c) Philippe Gerber <philippe@bigwhoop.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Hynage\Database;
use Hynage;

class Connection
{
    /**
     * @var PDO
     */
    protected $_pdo = null;
    
    
    /**
     * Create a new PDO object by a given dns (URI-style)
     * 
     * @param string $dsn
     */
    public function __construct($dsn)
    {
        $parts = array(
            'scheme' => 'mysql',
            'host'   => 'localhost',
            'port'   => 3306,
            'user'   => 'root',
            'pass'   => '',
            'path'   => '',
        );
        
        $parts = parse_url($dsn) + $parts;
        
        $dsn = sprintf(
            '%s:dbname=%s;host=%s;port=%d',
            $parts['scheme'],
            ltrim($parts['path'], '/'),
            $parts['host'],
            $parts['port']
        );
        
        $this->_pdo = new \PDO($dsn, $parts['user'], $parts['pass']);
        $this->_pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->_pdo->query("SET NAMES 'UTF8'");
    }


    /**
     * @return \Hynage\Database\Connection
     */
    public function beginTransaction()
    {
        $this->getAdapter()->beginTransaction();
        return $this;
    }

    
    /**
     * @return bool
     */
    public function hasTransactionStarted()
    {
        return $this->getAdapter()->inTransaction();
    }
    

    /**
     * @return \Hynage\Database\Connection
     */
    public function rollBack()
    {
        $this->getAdapter()->rollBack();
        return $this;
    }


    /**
     * @return \Hynage\Database\Connection
     */
    public function commit()
    {
        $this->getAdapter()->commit();
        return $this;
    }
    
    
    /**
     * Query the database
     * 
     * @param string $sql
     * @return \PDOStatement
     */
    public function query($sql)
    {
        return $this->getAdapter()->query($sql);
    }
    
    
    /**
     * Prepare an SQL query
     * 
     * @param string $sql
     * @param array $options
     * @return \PDOStatement
     */
    public function prepare($sql, array $options = array())
    {
        return $this->getAdapter()->prepare($sql, $options);
    }
    
    
    /**
     * Return the actual database adapter
     * 
     * @return \PDO
     */
    public function getAdapter()
    {
        return $this->_pdo;
    }
}