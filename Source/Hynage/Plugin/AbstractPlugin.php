<?php
namespace Hynage\Plugin;
use Hynage\Config as Config;

abstract class AbstractPlugin implements PluginInterface
{
    protected $_config = null;


    public function __construct(Config $config)
    {
        $this->setConfig($config);
    }


    public function setConfig(Config $config)
    {
        $this->_config = $config;
        return $this;
    }


    /**
     * Returns the translation of the given string
     *
     * @param string $string
     * @param mixed Arguments for printf
     * @return string
     */
    public function _($string)
    {
        $args = func_get_args();
        return call_user_func_array(array('\Hynage\I18n\Translator', 'translate'), $args);
    }
}
