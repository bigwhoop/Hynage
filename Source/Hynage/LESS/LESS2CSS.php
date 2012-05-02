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

class LESS2CSS
{
    /**
     * @var string
     */
    private $lessBasePath = '';

    /**
     * @var string
     */
    private $cssBasePath = '';

    /**
     * @var string
     */
    private $cssBaseURL = '';

    /**
     * @var Parser|null
     */
    private $parser = null;


    /**
     * @param string $lessBasePath
     * @param string $cssBasePath
     * @param string $cssBaseURL
     * @param Parser|null $parser
     */
    public function __construct($lessBasePath, $cssBasePath, $cssBaseURL, Parser $parser = null)
    {
        $this->lessBasePath = $lessBasePath;
        $this->cssBasePath  = $cssBasePath;
        $this->cssBaseURL   = $cssBaseURL;
        $this->parser       = $parser;
    }


    /**
     * @return Parser|null
     */
    public function getParser()
    {
        if (!$this->parser) {
            $this->parser = new Parser();
        }

        return $this->parser;
    }


    /**
     * @param string $name
     * @return string
     */
    public function parse($name)
    {
        $lessPath = $this->lessBasePath . "/$name.less";
        $cssPath  = $this->cssBasePath  . "/$name.css";
        $cssURL   = $this->cssBaseURL   . "/$name.css";

        // No need to parse if .css is newer
        if (file_exists($lessPath) && file_exists($cssPath) && filemtime($lessPath) < filemtime($cssPath)) {
            return $cssURL;
        }

        $parser = $this->getParser();
        $css = $parser->parseFile($lessPath);

        if (!is_dir(dirname($cssPath))) {
            mkdir(dirname($cssPath), 0777, true);
        }

        file_put_contents($cssPath, $css);

        return $cssURL;
    }
}