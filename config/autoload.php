<?php

spl_autoload_register(function (string $class): void {
    $directories = [
        APP_ROOT . DIRECTORY_SEPARATOR . 'models',
        APP_ROOT . DIRECTORY_SEPARATOR . 'controllers',
        APP_ROOT . DIRECTORY_SEPARATOR . 'config',
    ];

    foreach ($directories as $directory) {
        $file = $directory . DIRECTORY_SEPARATOR . $class . '.php';
        if (is_file($file)) {
            require_once $file;
            return;
        }
    }
});
