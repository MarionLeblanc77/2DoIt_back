<?php

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

if (isset($_SERVER['APP_ENV'])) {
    if ($_SERVER['APP_ENV'] === 'prod') {
        header('Access-Control-Allow-Origin: https://2doit.mlnc-dev.fr');
        // header('Access-Control-Allow-Credentials: true');
    } else {
        header('Access-Control-Allow-Origin: http://localhost:5173');
    }
}

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
