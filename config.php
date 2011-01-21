<?php
$config['ports']          = array(
    '80' => 'Http',
    '22' => 'SSH'
);
$config['timeout']        = 3;
$config['disks']          = 1;
$config['load_threshold'] = 2;

header('Content-Type: text/html; charset=utf-8');
ob_start("ob_gzhandler");

function serverinfo_autoload($class)
{
    $file = 'lib/' . $class . '.php';
    if (file_exists($file)) {
        return require $file;
    }
    throw new Exception('The class ' . $file . ' could not be loaded');
}

spl_autoload_register('serverinfo_autoload');