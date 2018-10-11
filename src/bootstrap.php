<?php
/**
 * @md
 * Bootstrap file to be required in base index.php for your app
 * for slim framework and h40
 *
 */

namespace Xklid101\H40\PhpApiSlimBoostrapware;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

if(!class_exists("Composer\\Autoload\\ClassLoader"))
    require __DIR__ . '/../vendor/autoload.php';

$container = new \Slim\Container;

//some config
$container['settings']['displayErrorDetails'] = true;
//database setup
$container['pgsql'] = function($cont) {
    $client = new \PDO("pgsql:" . getenv('H40_PGSQL_DSN'));
    $client->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    return $client;
};


//php error handler
set_error_handler(function(int $errno, string $errstr, string $errfile, int $errline, array $errcontext) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' =>
                            [
                                'code' => $errno,
                                'message' => "Error: $errstr in file $errfile on line $errline",
                                'context' => $errcontext
                            ]
                    ]);
    exit;
}, E_ALL);
//framework error handlers
$container['errorHandler'] = function($cont) {
    return function(Request $request, Response $response, \Exception $exception) use ($cont) {
        return $response->withStatus(500)
                            ->withJson(['error' =>
                                            [
                                                'code' => $exception->getCode(),
                                                'message' => $exception->getMessage()
                                            ]
                                        ]);
        };
};
$container['notFoundHandler'] = function($cont) {
    return function(Request $request, Response $response) use ($cont) {
        return $response->withStatus(404)
                            ->withJson(['error' =>
                                            [
                                                'code' => 404,
                                                'message' => 'Endpoint not Found!'
                                            ]
                                        ]);
    };
};
$container['notAllowedHandler'] = function($cont) {
    return function(Request $request, Response $response, array $methods) use ($cont) {
        return $response->withStatus(405)
                            ->withHeader('Allow', implode(', ', $methods))
                            ->withJson(['error' =>
                                            [
                                                'code' => 405,
                                                'message' => 'Method must be one of: ' . implode(', ', $methods),
                                            ]
                                        ]);
    };
};


$app = new \Slim\App($container);

/**
 * middleware to accept only application/json mimetype
 */
$app->add(function ($request, $response, $next) {
    if(strpos($request->getHeaderLine('Content-Type'), 'application/json') === false) {
        return $response->withStatus(415)
                            ->withJson(['error' =>
                                            [
                                                'code' => 415,
                                                'message' => 'Unsupported Media Type (need application/json)',
                                            ]
                                        ]);
    }
    $response = $next($request, $response);
    return $response;
});


return $app;
