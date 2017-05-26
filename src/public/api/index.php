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
 *     ),
 *     @SWG\Definition(
 *         definition="ErrorModel",
 *         type="object",
 *         required={"code", "message"},
 *         @SWG\Property(
 *             property="code",
 *             type="integer",
 *             format="int32"
 *         ),
 *         @SWG\Property(
 *             property="message",
 *             type="string"
 *         )
 *     ),
 *     @SWG\Definition(
 *         definition="Hello",
 *         type="object",
 *         required={"info", "message"},
 *         @SWG\Property(
 *             property="info",
 *             type="string"
 *         ),
 *         @SWG\Property(
 *             property="message",
 *             type="string"
 *         )
 *     ),
 *     @SWG\Definition(
 *         definition="Comparison",
 *         type="object",
 *         required={"comparison", "winner"},
 *         @SWG\Property(
 *             property="comparison",
 *             type="object",
 *                  @SWG\Property(
 *                      property="repo1",
 *                      type="array",
 *                      @SWG\Items(ref="#/definitions/Stats")
 *                  ),
 *                  @SWG\Property(
 *                      property="repo2",
 *                      type="array",
 *                      @SWG\Items(ref="#/definitions/Stats")
 *                  )
 *         ),
 *         @SWG\Property(
 *             property="winner",
 *             type="string"
 *         )
 *     ),
 *     @SWG\Definition(
 *         definition="Stats",
 *         type="object",
 *           @SWG\Property(property="name", type="string"),
 *           @SWG\Property(property="forks", format="int64", type="integer"),
 *           @SWG\Property(property="stars", format="int64", type="integer"),
 *           @SWG\Property(property="watchers", format="int64", type="integer"),
 *           @SWG\Property(property="latestRelease", type="string"),
 *           @SWG\Property(property="pullRequestOpen", type="string"),
 *           @SWG\Property(property="pullRequestClosed", type="string"),
 *           @SWG\Property(property="lastMerge", type="string"),
 *           @SWG\Property(property="updateDate", type="string"),
 *           @SWG\Property(property="points", format="int64", type="integer"),
 *           @SWG\Property(property="percent", format="int64", type="integer")
 *      )
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
 *         @SWG\Schema(ref="#/definitions/Hello")
 *     ),
 *     @SWG\Response(
 *         response="default",
 *         description="unexpected error",
 *         @SWG\Schema(ref="#/definitions/ErrorModel")
 *     )
 * )
 */

$app->get('/hello', function (Request $request, Response $response) {

    $params = $request->getQueryParams();
    $name = isset($params['name']) ? $params['name'] : "World";

    $data = array('info' => 'Github Repositories Comparison Api','message' => "Hello {$name}");

    $newResponse = $response
        ->withHeader('Content-type', 'application/json')
        ->withHeader('Access-Control-Allow-Origin','*')
        ->withStatus(200);
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
 *         @SWG\Schema(ref="#/definitions/Comparison")
 *     ),
 *     @SWG\Response(
 *         response=400,
 *         description="Not defined repo1 or repo2 params",
 *         @SWG\Schema(ref="#/definitions/ErrorModel")
 *     ),
 *     @SWG\Response(
 *         response=404,
 *         description="One of repos is not found",
 *         @SWG\Schema(ref="#/definitions/ErrorModel")
 *     ),
 *     @SWG\Response(
 *         response="default",
 *         description="unexpected error",
 *     )
 * )
 */

$app->get('/compare', function (Request $request, Response $response) {

    $params = $request->getQueryParams();

    if(!isset($params['repo1']) || !isset($params['repo1']))
    {
        $response->getBody()->write(json_encode(array(
            'code' => 400,
            'message' => 'Missing repo1 or repo2 params'
        )));

        $errorResponse = $response->withHeader('Content-type', 'application/json')
            ->withHeader('Access-Control-Allow-Origin','*')
            ->withHeader('X-Status-Reason','Missing repo1 or repo2 params')
            ->withStatus(400);
        return $errorResponse;
    }

    $repo1 = $params['repo1'];
    $repo2 = $params['repo2'];

    $Comparer = new \GitHubCompare\Comparer();
    $obj1 = $Comparer->buildRepoObject($repo1);
    $obj2 = $Comparer->buildRepoObject($repo2);

    if(!$obj1 || !$obj2)
    {
        $response->getBody()->write(json_encode(array(
            'code' => 404,
            'message' => 'One of repos is not found'
        )));

        $errorResponse = $response->withHeader('Content-type', 'application/json')
            ->withHeader('Access-Control-Allow-Origin','*')
            ->withHeader('X-Status-Reason','One of repos is not found')
            ->withStatus(404);
        return $errorResponse;
    }

    $data = $Comparer->compareStatistics($obj1,$obj2);

    $newResponse = $response->withHeader('Content-type', 'application/json')
        ->withHeader('Access-Control-Allow-Origin','*')
        ->withStatus(200);
    $resWithEtag = $this->cache->withEtag($newResponse, md5(serialize($data)));
    $resWithExpires = $this->cache->withExpires($resWithEtag, time() + 3600);
    $resWithLastMod = $this->cache->withLastModified($resWithExpires, time() - 3600);
    $resWithLastMod->getBody()->write(json_encode($data));

    return $resWithLastMod;

});
$app->run();