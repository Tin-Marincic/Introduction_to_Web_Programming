<?php

require 'vendor/autoload.php';
require_once __DIR__ . '/middleware/AuthMiddleware.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
// add this:
use Dotenv\Dotenv;

// === LOAD ENV (.env) ===
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad(); // won't crash if .env is missing (e.g. on DO where you use env vars)

// === CORS SETUP ===
$allowedOrigins = [
    "https://unisport-frontend-rg53w.ondigitalocean.app",
    "https://skiunisport.com",
    "https://www.skiunisport.com",
    "http://127.0.0.1",
    "http://localhost"
];


Flight::before('start', function () use ($allowedOrigins) {
    if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowedOrigins)) {
        header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization"); // ✅ removed Authentication
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
    // Public routes (no token required)
    //tryting to make it redeploy
    if (
        $url === '/auth/login' ||
        $url === '/auth/register' ||
        $url === '/auth/forgot-password' ||
        $url === '/auth/reset-password' ||
        $url === '/auth/verify-email' ||       
        $url === '/test-connection' ||
        $url === '/check-env' ||
        $url === '/cron/daily_private_report.php' ||  
        ($url === '/reviews' && $method === 'GET') ||
        ($url === '/api/services' && $method === 'GET') ||
        (strpos($url, '/users/instructor') === 0) ||
        (strpos($url, '/uploads/') === 0)          
    ) {
        return true;
    }


    try {
        $headers = getallheaders();
        $tokenHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

        if (!$tokenHeader) {
            throw new Exception("Missing Authorization header");
        }

        // Support "Bearer <token>" format
        if (strpos($tokenHeader, 'Bearer ') === 0) {
            $tokenHeader = substr($tokenHeader, 7); // strip "Bearer "
        }

        Flight::auth_middleware()->verifyToken($tokenHeader);
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
