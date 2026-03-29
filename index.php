<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use App\Http\Middleware\RequestLoggingMiddleware;
use App\Http\Middleware\TrailingSlashMiddleware;
use App\Http\Routes;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Slim\Factory\AppFactory;
use db\connection;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

connection::createConn();

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['formStarted'])) {
    $_SESSION['formStarted'] = true;
}

if (!isset($_SESSION['token'])) {
    $_SESSION['token'] = md5(uniqid((string) random_int(1, 999999), true));
    $_SESSION['token_time'] = time();
}

$loader = new FilesystemLoader(__DIR__ . '/template');
$twig = new Environment($loader);

$app = AppFactory::create();

$logDir = __DIR__ . '/var/log';
if (!is_dir($logDir)) {
    mkdir($logDir, 0775, true);
}

$logger = new Logger('http');
$logger->pushHandler(new StreamHandler($logDir . '/http.log', Logger::INFO));

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->add(new RequestLoggingMiddleware($logger));
$app->add(new TrailingSlashMiddleware($app->getResponseFactory()));
$app->addErrorMiddleware(true, true, true);

Routes::register($app, $twig);

$app->run();
