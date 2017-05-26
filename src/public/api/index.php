<?php

/**
 * @SWG\Swagger(
 *     basePath="/api",
 *     host=APP_HOST,
 *     schemes={"http"},
 *     produces={"application/json"},
 *     consumes={"application/json"},
 *     @SWG\Info(
 *         version="1.0.0",
 *         title="Github Repositories Comparison",
 *         description="A small API to compare 2 github repositories",
 *         termsOfService="http://swagger.io/terms/",
 *         @SWG\Contact(name="Swagger API Team"),
 *         @SWG\License(name="MIT")
 *     )
 * )
 */


use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../../vendor/autoload.php';

// Register service provider with the container
$container = new \Slim\Container;
$container['cache'] = function () {
    return new \Slim\HttpCache\CacheProvider();
};

$app = new \Slim\App($container);
$app->add(new \Slim\HttpCache\Cache('public', 86400));


/**
 * @SWG\Get(
 *     path="/hello",
 *     description="Just says hello",
 *     produces={"application/json"},
 *     @SWG\Parameter(
 *         name="name",
 *         in="query",
 *         description="your name",
 *         required=false,
 *         type="string"
 *     ),
 *     @SWG\Response(
 *         response=200,
 *         description="Expected hello response",
 *         @SWG\Schema(
 *             type="array",
 *             @SWG\Items(ref="#/definitions/hello")
 *         ),
 *     ),
 *     @SWG\Response(
 *         response="default",
 *         description="unexpected error",
 *         @SWG\Schema(
 *             ref="#/definitions/ErrorModel"
 *         )
 *     )
 * )
 */

$app->get('/hello', function (Request $request, Response $response) {

    $params = $request->getQueryParams();
    $name = isset($params['name']) ? $params['name'] : "World";

    $data = array('info' => 'Github Repositories Comparison Api','message' => "Hello {$name}");

    $newResponse = $response->withHeader('Content-type', 'application/json')->withHeader('Access-Control-Allow-Origin','*')->withStatus(200);
    $resWithEtag = $this->cache->withEtag($newResponse, md5(serialize($data)));
    $resWithExpires = $this->cache->withExpires($resWithEtag, time() + 3600);
    $resWithLastMod = $this->cache->withLastModified($resWithExpires, time() - 3600);
    $resWithLastMod->getBody()->write(json_encode($data));

    return $resWithLastMod;
});

/**
* @SWG\Get(
 *     path="/compare",
 *     description="Compare 2 github repositories",
 *     produces={"application/json"},
 *     @SWG\Parameter(
 *         name="repo1",
 *         in="query",
 *         description="Repository #1",
 *         required=true,
 *         type="string"
 *     ),
 *     @SWG\Parameter(
 *         name="repo2",
 *         in="query",
 *         description="Repository #2",
 *         required=true,
 *         type="string"
 *     ),
 *     @SWG\Response(
 *         response=200,
 *         description="Expected repositories comparison",
 *         @SWG\Schema(
 *             type="array",
 *             @SWG\Items(ref="#/definitions/compare")
*         ),
 *     ),
 *     @SWG\Response(
 *         response="default",
 *         description="unexpected error",
 *         @SWG\Schema(
 *             ref="#/definitions/ErrorModel"
    *         )
 *     )
 * )
 */

$app->get('/compare', function (Request $request, Response $response) {

    $params = $request->getQueryParams();
    $repo1 = $params['repo1'];
    $repo2 = $params['repo2'];

    $Comparer = new \SchibstedApp\Comparer();
    $obj1 = $Comparer->buildRepoObject($repo1);
    $obj2 = $Comparer->buildRepoObject($repo2);
    $data = $Comparer->compareStatistics($obj1,$obj2);

    $newResponse = $response->withHeader('Content-type', 'application/json')->withHeader('Access-Control-Allow-Origin','*')->withStatus(200);
    $resWithEtag = $this->cache->withEtag($newResponse, md5(serialize($data)));
    $resWithExpires = $this->cache->withExpires($resWithEtag, time() + 3600);
    $resWithLastMod = $this->cache->withLastModified($resWithExpires, time() - 3600);
    $resWithLastMod->getBody()->write(json_encode($data));

    return $resWithLastMod;

});
$app->run();