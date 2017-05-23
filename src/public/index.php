<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

$app = new \Slim\App;
$container = $app->getContainer();

$container['logger'] = function($c) {
    $logger = new \Monolog\Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler("../logs/app.log");
    $logger->pushHandler($file_handler);
    return $logger;
};

// Register component on container
$container['view'] = function ($container) {
    $view = new \Slim\Views\Twig("../templates/", ['cache' => false]);

    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));

    return $view;
};



$app->get('/hello/{name}', function (Request $request, Response $response, $args) {
    $this->logger->addInfo("/hello route started");

    return $this->view->render($response, 'hello.html', [
        'name' => $args['name'],
        'title' => 'Hello'
    ]);

})->setName('hello');

$app->get('/compare', function (Request $request, Response $response, $args) {
    $this->logger->addInfo("/compare route started");

    return $this->view->render($response, 'compare.html', [
    ]);

})->setName('compare');

$app->post('/compare', function (Request $request, Response $response, $args) {

    $response->getBody()->write('ok');

})->setName('compare');


$app->run();
