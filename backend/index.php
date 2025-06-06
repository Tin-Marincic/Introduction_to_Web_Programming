<?php

require 'vendor/autoload.php';
require_once __DIR__ . '/middleware/AuthMiddleware.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// === CORS SETUP ===
$allowedOrigins = [
    "https://unisport-frontend-rg53w.ondigitalocean.app"
];

Flight::before('start', function () use ($allowedOrigins) {
    if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowedOrigins)) {
        header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, Authentication");
        header("Access-Control-Allow-Credentials: true");
    }

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit();
    }
});

// === ERROR REPORTING ===
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// === REGISTER SERVICES ===
require_once __DIR__ . '/services/AuthService.php';
Flight::register('auth_service', 'AuthService');

require_once __DIR__ . '/services/UserService.php';
Flight::register('userService', 'UserService');

require_once __DIR__ . '/services/BookingService.php';
Flight::register('bookingService', 'BookingService');

require_once __DIR__ . '/services/ReviewsService.php';
Flight::register('reviewService', 'ReviewService');

require_once __DIR__ . '/services/AvailabilityCalendarService.php';
Flight::register('availabilityService', 'AvailabilityCalendarService');

require_once __DIR__ . '/services/ServicesService.php';
Flight::register('servicesService', 'ServicesService');

// === MIDDLEWARE ===
Flight::register('auth_middleware', 'AuthMiddleware');

// === JWT GLOBAL AUTH ===
Flight::route('/*', function () {
    $url = Flight::request()->url;
    $method = Flight::request()->method;

    // Public routes
    if (
        $url === '/auth/login' ||
        $url === '/auth/register' ||
        $url === '/test-connection' ||
        $url === '/check-env' ||
        ($url === '/reviews' && $method === 'GET') ||
        ($url === '/api/services' && $method === 'GET') ||
        (strpos($url, '/users/instructor') === 0)
    ) {
        return true;
    }


    try {
        $headers = getallheaders();
        $token = $headers['Authorization']
            ?? $headers['authorization']
            ?? $headers['Authentication']
            ?? $headers['authentication']
            ?? null;

        if (!$token) {
            throw new Exception("Missing Authorization or Authentication header");
        }

        Flight::auth_middleware()->verifyToken($token);
        return true;
    } catch (\Exception $e) {
        Flight::halt(401, "Unauthorized: " . $e->getMessage());
    }
});

// === ROUTES ===
require_once __DIR__ . '/routes/AuthRoutes.php';
require_once __DIR__ . '/routes/UserRoutes.php';
require_once __DIR__ . '/routes/BookingRoutes.php';
require_once __DIR__ . '/routes/ReviewsRoutes.php';
require_once __DIR__ . '/routes/AvailabilityCalendarRoutes.php';
require_once __DIR__ . '/routes/ServicesRoutes.php';

// === TEST CONNECTION ===
Flight::route('GET /test-connection', function () {
    try {
        $db = Database::connect();
        Flight::json(["status" => "success", "message" => "Database connection successful!"]);
    } catch (Exception $e) {
        Flight::json(["status" => "error", "message" => $e->getMessage()], 500);
    }
});

Flight::route('GET /check-env', function () {
    Flight::json([
        "DB_HOST" => Config::DB_HOST(),
        "DB_PORT" => Config::DB_PORT(),
        "DB_USER" => Config::DB_USER(),
        "DB_NAME" => Config::DB_NAME(),
        "DB_PASSWORD" => Config::DB_PASSWORD()
    ]);
});


Flight::start();
