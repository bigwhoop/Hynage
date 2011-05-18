<?php
namespace HynageExampleApp\Config;
use Hynage\Filter;

$config = array();

// PHP settings
$config['phpSettings'] = array(
    'error_reporting' => E_ALL,
    'display_errors'  => true,
    'log_errors'      => false,
);

// Database configuration
$config['database'] = array(
    'dsn'  => 'mysql:dbname=hynage;host=localhost;port=3306',
    'user' => 'usr',
    'pass' => '',
);

// Front controller
$capWordsFilter = new Filter\String\CapWords();
$config['frontController'] = array(
    'formatters' => array(
        'controllerName' => function($class) use ($capWordsFilter) {
            return '\\HynageExampleApp\\Controllers\\' . $capWordsFilter->filter($class);
        },
        'controllerPath' => function($class) use ($capWordsFilter) {
            return realpath(__DIR__ . '/../Controllers/' . $capWordsFilter->filter($class) . '.php');
        },
        'actionName' => array('\\Hynage\\Filter\\MVC\\DefaultActionName', 'filter'),
    ),
    'defaults'  => array(
        'controller' => 'index',
        'action'     => 'index',
    ),
);

// View
$config['view'] = array(
    'basePath' => realpath(__DIR__ . '/../Views'),
);

// Layout
$config['layout'] = array(
    'basePath' => realpath(__DIR__ . '/../Layouts'),
    'layout'   => 'Default.phtml',
);

// Security
$config['security'] = array(
    'secretkey' => 'mysupersecretkey',
);

return $config;