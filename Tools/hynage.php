<?php
namespace Hynage\Tools;
use Hynage\Application as Application;

$commands = array(
    'model-to-sql' => array(
        'description' => 'Generates the SQL to create the table structure of a specific model.',
        'params' => array(
            '--class' => 'Name of the model (incl. namespace)',
        ),
        'callback' => 'model_to_sql',
    ),
);

$configPath = trim(@$argv[1]);
if (empty($configPath)) {
    help_exit('Missing path to config file.');
}

if (!file_exists($configPath)) {
    help_exit("No config file found at path '$configPath'.");
}

// Include Hynage
require __DIR__ . '/../Source/Hynage/Application.php';

// Bootstrap autoloader
$app = Application::getInstance($configPath);
$app->bootstrap(array('autoloader', 'errorHandler', 'exceptionHandler'));

$commandKey = trim(@$argv[2]);

if (array_key_exists($commandKey, $commands)) {
    $command = $commands[$commandKey];

    // No additional params
    if ($argc <= 3 && !count($command['params'])) {
        call_user_func($command['callback']);
    }

    // Additional params
    else {
        $params = array();
        foreach (array_keys($command['params']) as $key) {
            $params[$key] = null;
        }

        for ($i = 3; $i < $argc; $i++) {
            if (array_key_exists($argv[$i], $params) && isset($argv[$i + 1])) {
                $params[$argv[$i]] = $argv[$i + 1];
            }
        }

        call_user_func(__NAMESPACE__ . '\\' . $command['callback'], $params);
    }
} else {
    help_exit('Invalid command.');
}


function model_to_sql(array $args)
{
    $class = $args['--class'];

    if (empty($class)) {
        help_exit("Missing argument '--class'.");
    }
    
    if (!class_exists($class, true)) {
        help_exit("Could not load class '$class'.");
    }

    if (!method_exists($class, 'generateCreateTableStatement')) {
        help_exit("Class does not implement static method 'generateCreateTableStatement()'.");
    }

    $definition = call_user_func(array($class, 'generateCreateTableStatement'));
    echo $definition;
}


function help_exit($reason)
{
    echo "ERROR: $reason\n\n";
    print_usage_message();
    exit();
}


function print_usage_message()
{
    global $commands;

    $colWidth = 25;

    foreach ($commands as $name => $definition) {
        printf("hynage $name\n");
        foreach ($definition['params'] as $paramName => $paramDescription) {
            echo "  $paramName"
                 . str_repeat(" ", $colWidth - (mb_strlen($paramName) + 2))
                 . $paramDescription . "\n";
        }
    }
}