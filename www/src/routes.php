<?php

declare(strict_types=1);

use Slim\App;
use App\Controller\HomeController;
use App\Controller\FileController;
use App\Controller\SearchController;
use App\Controller\SystemController;

return function (App $app) {
    $app->get('/', HomeController::class);

    $app->get('/robots.txt', [SystemController::class, 'robots']);
    $app->get('/sitemap.xml', [SystemController::class, 'sitemap']);

    $app->get('/__search/{query}', [SearchController::class, 'search']);

    $app->get('/{file}/__raw', [FileController::class, 'raw']);
    $app->get('/{file}/__functions', [FileController::class, 'functions']);
    $app->get('/{file}/{function}', [FileController::class, 'function']);
    $app->get('/{file}', [FileController::class, 'view']);
};
