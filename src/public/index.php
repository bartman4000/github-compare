<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

// Register service provider with the container
$container = new \Slim\Container;
$container['cache'] = function () {
    return new \Slim\HttpCache\CacheProvider();
};

$app = new \Slim\App($container);
$app->add(new \Slim\HttpCache\Cache('public', 86400));

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
    $this->logger->addDebug("PostedVars:".implode(',',$allPostVars));

    $repo1 = $allPostVars['repo1'];
    $repo2 = $allPostVars['repo2'];

//    $Comparer = new \SchibstedApp\Comparer();
//    $obj1 = $Comparer->buildRepoObject($repo1);
//    $obj2 = $Comparer->buildRepoObject($repo2);
//    $data = $Comparer->compareStatistics($obj1,$obj2);

    $ApiClient = new \SchibstedApp\InternalApiClient();
    $data = $ApiClient->get("/compare", array('query' => array('repo1' => $repo1, 'repo2' => $repo2)));


    $newResponse = $response->withHeader('Access-Control-Allow-Origin','*')
        ->withStatus(200);
    $resWithEtag = $this->cache->withEtag($newResponse, md5(serialize($data)));
    $resWithExpires = $this->cache->withExpires($resWithEtag, time() + 3600);
    $resWithLastMod = $this->cache->withLastModified($resWithExpires, time() - 3600);

    return $this->view->render($resWithLastMod, 'compared.html', [
        'winner' => $data['winner'],
        'title' => 'Results',
        'repo1' => $data['comparison']['repo1'],
        'repo2' => $data['comparison']['repo2']
    ]);
})->setName('compared');


$app->run();
