<?php
/**
 * This file is part of Hynage.
 *
 * (c) Philippe Gerber <philippe@bigwhoop.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Hynage\Form\Element;

class FileElement extends InputElement
{
    public function renderElement()
    {
        return $this->_renderInputElement('file');
    }


    /**
     * @return array|null
     */
    public function getValue()
    {
        if (isset($_FILES[$this->getName()])) {
            return $_FILES[$this->getName()];
        }

        return null;
    }


    /**
     * @return bool
     */
    public function isValid()
    {
        if (!isset($_FILES[$this->getName()])) {
            $this->addError('Die Datei wurde nicht übertragen.');
            return false;
        }

        $file = $_FILES[$this->getName()];

        if ($file['error'] !== 0) {
            $this->addError('Die Datei nicht fehlerfrei übertragen.');
            return false;
        }

        if (!file_exists($file['tmp_name'])) {
            $this->addError('Die übertragene Datei wurde lokal nicht gefunden.');
            return false;
        }

        return true;
    }
}