<?php
namespace Hynage\Test\Config;

$config = array();

// PHP settings
$config['phpSettings'] = array(
    'error_reporting' => E_ALL,
    'display_errors'  => true,
    'log_errors'      => false,
);

// Include paths
$config['includePaths'] = array(
    HYNAGE_PATH,
);

return $config;