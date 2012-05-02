<?php
/**
 * This file is part of Hynage.
 *
 * (c) Philippe Gerber <philippe@bigwhoop.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Hynage\LESS;

class Parser extends \lessc
{
    /**
     * @var null
     */
    protected $buffer = null;


    /**
     * @throws \InvalidArgumentException
     * @param string $lessPath
     * @return string
     */
    public function parseFile($lessPath)
    {
        if (!file_exists($lessPath)) {
            throw new \InvalidArgumentException('LESS file not found at "' . $lessPath . '"');
        }

        $this->fileName  = $lessPath;
        $this->importDir = dirname($lessPath) . '/';
        $this->buffer    = file_get_contents($lessPath);

        $this->addParsedFile($lessPath);

        return $this->parse();
    }
}
