<?php
ob_start();
define('TEST_EXIT_SCRIPT', './support/test_exit.php');
include_once 'support/constants.php';

function in_include_path($filename) {
    $paths = explode(PATH_SEPARATOR, get_include_path());
    foreach ($paths as $path) {
        if (substr($path, -1) != DIRECTORY_SEPARATOR) {
            $path .= DIRECTORY_SEPARATOR;
        }
        if (file_exists($path . $filename)) {
            return TRUE;
        }
    }
    return FALSE;
}

// PHPUnit 3.5 complains about including PHPUnit/Framework.php, but
// 3.4 doesn't have PHPUnit/Autoload.php
if (in_include_path('PHPUnit/Autoload.php')) {
	require_once 'PHPUnit/Autoload.php';
} else {
	require_once 'PHPUnit/Framework.php';
}
require_once 'PHPUnit/TextUI/TestRunner.php';
require_once 'PHPUnit/Extensions/OutputTestCase.php';

date_default_timezone_set('America/New_York');
error_reporting(E_ALL | E_STRICT);

$_SERVER['SERVER_NAME'] = 'example.com';
$_SERVER['REQUEST_URI'] = '/index.php';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SERVER_PORT'] = 80;

if (empty($_SERVER['DOCUMENT_ROOT'])) {
	$_SERVER['DOCUMENT_ROOT'] = realpath(dirname(__FILE__) . '/../');
}

function flourish_autoload($class_name)
{
	$file = '../classes/' . $class_name . '.php';
	if (file_exists($file)) {
		require_once($file);
		return;
	}
}
spl_autoload_register('flourish_autoload');

function __cache()
{
	static $cache = NULL;
	if (!$cache) {
		$cache = new fCache('file', 'output/db.cache');
	}
	return $cache;	
}

/**
 * This cleans up all class configurations by calling static reset methods
 * 
 * @param  array $ignore_classes  These classes will not be reset
 * @return void
 */
function __reset($ignore_classes=array())
{
	$classes = scandir('../classes/');
	$classes = array_diff($classes, array('.', '..'));
	
	foreach ($classes as $class) {
		$class = str_replace('.php', '', $class);
		if (!class_exists($class, FALSE)) {
			continue;
		}
		if (in_array($class, $ignore_classes)) {
			continue;	
		}
		if (method_exists($class, 'reset')) {
			call_user_func(array($class, 'reset'));	
		}
	}	
}