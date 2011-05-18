<?php
namespace HynageExampleApp;
use Hynage\Application as Application;

define('HYNAGE_LIBRARY_PATH', realpath(__DIR__ . '/../../Source'));

// Include Hynage by source
require_once realpath(__DIR__ . '/../../source/Hynage/Application.php');

// Assemble path to config file
$config = realpath(__DIR__ . '/../Application/Config/Default.php');

// Start application
$app = Application::getInstance($config);
$app->dispatch();