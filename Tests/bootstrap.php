<?php
/**
 * This file is part of Hynage.
 *
 * (c) Philippe Gerber <philippe@bigwhoop.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Hynage\Test;
use Hynage\Application as Application;

// Define project path
define('SOURCE_PATH', realpath(__DIR__ . '/../Source'));

// Include Hynage
require SOURCE_PATH . '/Hynage/Application.php';

// Assemble path to config file
$config = realpath(__DIR__ . '/TestConfig.php');

// Start application
$app = Application::getInstance($config);
$app->bootstrap('cli');