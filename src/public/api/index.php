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

$app->get('/hello', function (Request $request, Response $response) {
    $name = $request->getAttribute('name');

    $data = array('info' => 'Github Repositories Comparison Api');

    $newResponse = $response->withHeader('Content-type', 'application/json')->withStatus(200);
    $newResponse->getBody()->write(json_encode($data));

    return $newResponse;
});
$app->run();