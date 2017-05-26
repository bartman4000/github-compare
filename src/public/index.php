<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

$app = new \Slim\App;
$container = $app->getContainer();
$container['logger'] = function($c) {
    $logger = new \Monolog\Logger('Slim');
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

//Hello
$app->get('/hello/{name}', function (Request $request, Response $response, $args) {
    $this->logger->addInfo("GET /hello route");

    return $this->view->render($response, 'hello.html', [
        'name' => $args['name'],
        'title' => 'Hello'
    ]);

})->setName('hello');

//Get root
$app->get('/', function (Request $request, Response $response, $args) {
    $this->logger->addInfo("GET / route");

    return $this->view->render($response, 'compare.html', [
        'title' => 'Hello'
    ]);

})->setName('compare');


//Post root
$app->post('/', function (Request $request, Response $response, $args) {

    $this->logger->addInfo("POST / route");
    $allPostVars = $request->getParsedBody();
    $this->logger->addDebug("PostedVars:".$allPostVars);

    $repo1 = $allPostVars['repo1'];
    $repo2 = $allPostVars['repo2'];

    $Comparer = new \SchibstedApp\Comparer();
    $obj1 = $Comparer->buildRepoObject($repo1);
    $obj2 = $Comparer->buildRepoObject($repo2);
    $data = $Comparer->compareStatistics($obj1,$obj2);

    return $this->view->render($response, 'compared.html', [
        'winner' => $data['winner'],
        'title' => 'Results',
        'repo1' => $data['comparison']['repo1'],
        'repo2' => $data['comparison']['repo2']
    ]);
})->setName('compared');


$app->run();
