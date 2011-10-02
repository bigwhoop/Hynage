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
use Hynage\Application\PHPUnitApplication,
    Hynage\Autoloader;

// Define project path
define('HYNAGE_PATH', __DIR__ . '/../Source');

// Register class loader
require HYNAGE_PATH . '/Hynage/Autoloader/Loadable.php';
require HYNAGE_PATH . '/Hynage/Autoloader/Composite.php';
require HYNAGE_PATH . '/Hynage/Autoloader/NamespaceToDirectory.php';
$autoloader = new Autoloader\Composite();
$autoloader->addAutoloader(new Autoloader\NamespaceToDirectory('Hynage', HYNAGE_PATH));
$autoloader->register();

// Assemble path to config file
$config = realpath(__DIR__ . '/TestConfig.php');

// Start application
$app = new PHPUnitApplication($config);
$app->dispatch();