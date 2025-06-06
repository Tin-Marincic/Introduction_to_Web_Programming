<?php

// CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

ini_set('display_errors', 1);
error_reporting(E_ALL);


require __DIR__ . '/../../../vendor/autoload.php';


define('LOCALSERVER', 'http://localhost/TinMarincic/Introduction_to_Web_Programming/backend');
define('PRODSERVER', 'https://unisport-9kjwi.ondigitalocean.app/index.php/');


if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1') {
    define('BASE_URL', LOCALSERVER);
} else {
    define('BASE_URL', PRODSERVER);
}


$openapi = \OpenApi\Generator::scan([
    __DIR__ . '/doc_setup.php',
    __DIR__ . '/../../../routes'
]);


header('Content-Type: application/json');
echo $openapi->toJson();
