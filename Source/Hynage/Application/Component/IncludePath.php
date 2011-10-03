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

class IncludePath extends AbstractComponent
{
    /**
     * @var array
     */
    private $includePaths = array();


    /**
     * @param array $includePaths
     */
    public function __construct(array $includePaths = array())
    {
        foreach ($includePaths as $includePath) {
            $this->addIncludePath($includePath);
        }
    }


    /**
     * @param $includePath
     * @return IncludePath
     */
    public function addIncludePath($includePath)
    {
        $this->includePaths[] = $includePath;
        return $this;
    }


    public function bootstrap()
    {
        $paths = explode(PATH_SEPARATOR, get_include_path());
        
        foreach ($this->includePaths as $userPath) {
            $paths[] = $userPath;
        }
        
        set_include_path(join(PATH_SEPARATOR, $paths));
    }
}
