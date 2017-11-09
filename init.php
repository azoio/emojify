<?php

if (!defined('MY_ABSPATH')) {
    define('MY_ABSPATH', dirname(__FILE__) . '/');
}

spl_autoload_register(function ($class_name) {
    $class_name = str_replace(array('\\', '_'), '/', $class_name);
    $file_name  = MY_ABSPATH . 'classes/' . $class_name . '.php';

    if (is_file($file_name)) {
        require_once $file_name;
    }
});
