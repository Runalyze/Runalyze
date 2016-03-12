<?php
/**
 * Default entry point for microframework based app
 */

use Symfony\Component\HttpFoundation\Request;

// require Composer's autoloader
require __DIR__.'/../app/autoload.php';
require __DIR__.'/../app/AppKernel.php';

$kernel = new AppKernel('dev', true);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);