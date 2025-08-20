<?php

use App\Kernel;

use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

if (env('APP_ENV') === 'prod') {
    header('Access-Control-Allow-Origin: https://nx0dwjbucbs.preview.infomaniak.website');
}

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
