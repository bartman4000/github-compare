<?php

/**
 * @SWG\Swagger(
 *     basePath="/api",
 *     host="localhost:8080",
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

$app = new \Slim\App;



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
 *         description="hello response",
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
    $name = $params['name'];

    $data = array('info' => 'Github Repositories Comparison Api','message' => "Hello {$name}");

    $newResponse = $response->withHeader('Content-type', 'application/json')->withHeader('Access-Control-Allow-Origin','*')->withStatus(200);
    $newResponse->getBody()->write(json_encode($data));

    return $newResponse;
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
 *         description="Repositories comparison",
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
    $newResponse->getBody()->write(json_encode($data));

    return $newResponse;

});
$app->run();