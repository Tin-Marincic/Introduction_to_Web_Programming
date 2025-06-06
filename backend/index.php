<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authentication");


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit();
}

require 'vendor/autoload.php';
require_once __DIR__ . '/middleware/AuthMiddleware.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ==== Register Services ====
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

// ==== Register Middleware ====
Flight::register('auth_middleware', 'AuthMiddleware');

// ==== Global JWT Token Verification Middleware ====
Flight::route('/*', function () {
    $url = Flight::request()->url;
    $method = Flight::request()->method;

    if (
        ($url === '/auth/login') ||
        ($url === '/auth/register') ||
        ($url === '/test-connection') ||
        ($url === '/reviews' && $method === 'GET') || 
        ($url === '/api/services' && $method === 'GET') ||
        (strpos($url, '/users/instructor') === 0)
    ) {
        return true;
    }

    try {
        $token = Flight::request()->getHeader("Authentication");
        if (Flight::auth_middleware()->verifyToken($token)) {
            return true;
        }
    } catch (\Exception $e) {
        Flight::halt(401, $e->getMessage());
    }
});


require_once __DIR__ . '/routes/AuthRoutes.php';
require_once __DIR__ . '/routes/UserRoutes.php';
require_once __DIR__ . '/routes/BookingRoutes.php';
require_once __DIR__ . '/routes/ReviewsRoutes.php';
require_once __DIR__ . '/routes/AvailabilityCalendarRoutes.php';
require_once __DIR__ . '/routes/ServicesRoutes.php';

Flight::route('GET /test-connection', function(){
    try {
        $db = Database::connect();
        Flight::json(["status" => "success", "message" => "Database connection successful!"]);
    } catch (Exception $e) {
        Flight::json(["status" => "error", "message" => $e->getMessage()], 500);
    }
});


Flight::start();
