<?php
/**
 * This file is part of Hynage.
 *
 * (c) Philippe Gerber <philippe@bigwhoop.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Hynage\Validator;
use Hynage\ORM\EntityManager;

class UniqueDatabaseRow extends AbstractValidator
{
    /**
     * @var \Hynage\ORM\EntityManager
     */
    private $em;

    /**
     * @var string
     */
    private $entityType = '';

    /**
     * @var string
     */
    private $fieldName = '';

    /**
     * @var null|string
     */
    private $currentValue = null;
    
    
    /**
     * @param \Hynage\ORM\EntityManager $em
     * @param string $entityType
     * @param string $fieldName
     * @param string|null $currentValue
     */
    public function __construct(EntityManager $em, $entityType, $fieldName, $currentValue = null)
    {
        $this->em           = $em;
        $this->entityType   = $entityType;
        $this->fieldName    = $fieldName;
        $this->currentValue = $currentValue;
    }

    
    /**
     * @param string $v
     * @return bool
     */
    public function isValid($v)
    {
        if ($v == $this->currentValue) {
            return true;
        }
        
        $entity = $this->em->findEntityBy($this->entityType, array($this->fieldName => $v));
        
        if ($entity) {
            $this->addError($this->_("'%s' was already used. Please enter a different value.", $v));
            return false;
        }

        return true;
    }
}