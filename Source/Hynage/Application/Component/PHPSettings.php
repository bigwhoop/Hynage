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

class PHPSettings extends AbstractComponent
{
    /**
     * @var array
     */
    private $settings = array();


    /**
     * @param array $settings
     */
    public function __construct(array $settings = array())
    {
        foreach ($settings as $key => $value) {
            $this->addSetting($key, $value);
        }
    }


    /**
     * @param string $key
     * @param mixed $value
     * @return PHPSettings
     */
    public function addSetting($key, $value)
    {
        $this->settings[$key] = $value;
        return $this;
    }


    /**
     * @return array
     */
    public function bootstrap()
    {
        $allowedKeys = array_keys(ini_get_all());
        $changedSettings = array();
        
        foreach ($this->settings as $key => $value) {
            if (in_array($key, $allowedKeys)) {
                ini_set($key, $value);
                $changedSettings[$key] = $value;
            }
        }

        return $changedSettings;
    }
}
