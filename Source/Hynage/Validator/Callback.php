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

class Callback extends AbstractValidator
{
    /**
     * @var \callable
     */
    private $callback;
    
    /**
     * @var string
     */
    private $message = '';
    
    
    /**
     * @param \callable $callable
     * @param string $message
     */
    public function __construct($callable, $message)
    {
        if (!is_callable($callable)) {
            throw new \InvalidArgumentException('Argument must be callable.');
        }
        
        $this->callback = $callable;
        $this->message  = (string)$message;
    }
    
    
    /**
     * @param mixed $v
     * @return bool
     */
    public function isValid($v)
    {
        if (!call_user_func($this->callback, $v)) {
            $this->addError($this->message);
            return false;
        }

        return true;
    }
}