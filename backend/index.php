<?php
require 'vendor/autoload.php';


require_once __DIR__ . '/services/UserService.php';
Flight::register('userService', 'UserService');

require_once __DIR__ . '/routes/UserRoutes.php';


require_once __DIR__ . '/services/BookingService.php';
Flight::register('bookingService', 'BookingService');

require_once __DIR__ . '/routes/BookingRoutes.php';


require_once __DIR__ . '/services/ReviewsService.php';
Flight::register('reviewService', 'ReviewService'); 

require_once __DIR__ . '/routes/ReviewsRoutes.php';



require_once __DIR__ . '/services/AvailabilityCalendarService.php';
Flight::register('availabilityService', 'AvailabilityCalendarService');

require_once __DIR__ . '/routes/AvailabilityCalendarRoutes.php';  


require_once __DIR__ . '/services/ServicesService.php';
Flight::register('servicesService', 'ServicesService');

require_once __DIR__ . '/routes/ServicesRoutes.php';


Flight::start();
?>
