<?php

require_once 'vendor/autoload.php';

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Middleware\TokenAuthentication;

$config = [
    'settings' => [
        'displayErrorDetails' => true
    ]
];

$app = new App($config);

$authenticator = function ($request, TokenAuthentication $tokenAuth) {

    /**
     * Try find authorization token via header, parameters, cookie or attribute
     * If token not found, return response with status 401 (unauthorized)
     */
    $token = $tokenAuth->findToken($request);

    /**
     * Call authentication logic class
     */
    $auth = new \app\Auth();

    /**
     * Verify if token is valid on database
     * If token isn't valid, must throw an UnauthorizedExceptionInterface
     */
    $user = $auth->getUserByToken($token);

    return $user["id"];
};

/**
 * Add token authentication middleware
 */
$app->add(new TokenAuthentication([
    'path' => '/restrict',
    'authenticator' => $authenticator
]));

/**
 * Public route example
 */
$app->get('/', function ($request, Response $response) {
    $output = ['msg' => 'It is a public area'];
    $response
        ->withStatus(200)
        ->write(json_encode($output));
});

/**
 * Restrict route example
 * Our token is "usertokensecret"
 */
$app->get('/restrict', function (Request $request, Response $response) {
    $output = ['msg' => 'It\'s a restrict area. Token authentication works!'];
    $response
        ->withStatus(200)
        ->write(json_encode($request->getAttribute('userId')));
});

$app->run();