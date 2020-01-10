<?php
spl_autoload_register(function($class) {
    $prefix = 'App\\';
    if(strpos($class, $prefix) === 0) {
        $className = substr($class, strlen($prefix));
        $classFilePath = '../lib/' . str_replace('\\', '/', $className) . '.php';
        if(file_exists($classFilePath)) {
            require $classFilePath;
        } else {
            return true;
        }
    }
});
