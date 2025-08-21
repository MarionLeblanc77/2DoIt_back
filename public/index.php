<?php

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

if (isset($_SERVER['APP_ENV'])) {
    if ($_SERVER['APP_ENV'] === 'prod') {
        header('Access-Control-Allow-Origin: https://nx0dwjbucbs.preview.infomaniak.website');
    } else {
        header('Access-Control-Allow-Origin: http://localhost:5173');
    }
    if (!headers_sent() && !in_array('Access-Control-Allow-Credentials: true', headers_list())) {
        header('Access-Control-Allow-Credentials: true');
    }
}

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
