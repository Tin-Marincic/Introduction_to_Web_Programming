<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../data/roles.php';

/**
 * @OA\Get(
 *     path="/bookings/detailed",
 *     tags={"Bookings"},
 *     summary="Admin: Get detailed upcoming bookings for all instructors",
 *     security={{"ApiKey": {}}},
 *     @OA\Response(response=200, description="Detailed grouped bookings per instructor")
 * )
 */
Flight::route('GET /bookings/detailed', function () {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    Flight::json(Flight::bookingService()->getDetailedUpcomingInstructorBookings());
});

/**
 * @OA\Get(
 *     path="/bookings/ski-school",
 *     tags={"Bookings"},
 *     summary="Admin: Get weekly ski school availability",
 *     security={{"ApiKey": {}}},
 *     @OA\Response(response=200, description="Available spots for each ski school week")
 * )
 */
Flight::route('GET /bookings/ski-school', function () {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    Flight::json(Flight::bookingService()->getSkiSchoolAvailability());
});

/**
 * @OA\Get(
 *     path="/bookings/instructor/{id}/hours",
 *     tags={"Bookings"},
 *     summary="Instructor: Get total hours worked this month",
 *     security={{"ApiKey": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Instructor ID",
 *         @OA\Schema(type="integer", example=7)
 *     ),
 *     @OA\Response(response=200, description="Total hours worked this month")
 * )
 */
Flight::route('GET /bookings/instructor/@id/hours', function ($id) {
    Flight::auth_middleware()->authorizeRole(Roles::INSTRUCTOR);
    Flight::json(Flight::bookingService()->getTotalHoursThisMonth($id));
});

/**
 * @OA\Get(
 *     path="/bookings/instructor/{id}/count",
 *     tags={"Bookings"},
 *     summary="Instructor: Get count of upcoming bookings for this month",
 *     security={{"ApiKey": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Instructor ID",
 *         @OA\Schema(type="integer", example=7)
 *     ),
 *     @OA\Response(response=200, description="Booking count for this month")
 * )
 */
Flight::route('GET /bookings/instructor/@id/count', function ($id) {
    Flight::auth_middleware()->authorizeRole(Roles::INSTRUCTOR);
    Flight::json(Flight::bookingService()->getUpcomingBookingsCount($id));
});

/**
 * @OA\Get(
 *     path="/bookings/instructor/{id}/upcoming",
 *     tags={"Bookings"},
 *     summary="Instructor: Get upcoming bookings for this month",
 *     security={{"ApiKey": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Instructor ID",
 *         @OA\Schema(type="integer", example=7)
 *     ),
 *     @OA\Response(response=200, description="List of upcoming bookings")
 * )
 */
Flight::route('GET /bookings/instructor/@id/upcoming', function ($id) {
    Flight::auth_middleware()->authorizeRole(Roles::INSTRUCTOR);
    Flight::json(Flight::bookingService()->getDetailedUpcomingBookings($id));
});

/**
 * @OA\Post(
 *     path="/bookings",
 *     tags={"Bookings"},
 *     summary="Create a new booking",
 *     security={{"ApiKey": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"user_id", "instructor_id", "service_id", "session_type", "date", "start_time", "num_of_hours", "status"},
 *             @OA\Property(property="user_id", type="integer", example=1),
 *             @OA\Property(property="instructor_id", type="integer", example=3),
 *             @OA\Property(property="service_id", type="integer", example=2),
 *             @OA\Property(property="session_type", type="string", example="Private_instruction"),
 *             @OA\Property(property="date", type="string", format="date", example="2025-05-10"),
 *             @OA\Property(property="start_time", type="string", example="10:00:00"),
 *             @OA\Property(property="num_of_hours", type="integer", example=2),
 *             @OA\Property(property="status", type="string", example="confirmed")
 *         )
 *     ),
 *     @OA\Response(response=200, description="Booking created")
 * )
 */
Flight::route('POST /bookings', function () {
    Flight::auth_middleware()->authorizeRole(Roles::USER, Roles::ADMIN);
    $data = Flight::request()->data->getData();
    Flight::json(Flight::bookingService()->createBooking($data));
});

/**
 * @OA\Delete(
 *     path="/bookings/{id}",
 *     tags={"Bookings"},
 *     summary="Delete a booking by ID",
 *     security={{"ApiKey": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Booking ID",
 *         @OA\Schema(type="integer", example=5)
 *     ),
 *     @OA\Response(response=200, description="Booking deleted")
 * )
 */
Flight::route('DELETE /bookings/@id', function ($id) {
    Flight::auth_middleware()->authorizeRole([Roles::USER, Roles::ADMIN]);
    Flight::json(Flight::bookingService()->deleteBooking($id));
});

/**
 * @OA\Get(
 *     path="/instructors/{id}/bookings",
 *     tags={"Bookings"},
 *     summary="Get bookings for an instructor on a specific date",
 *     security={{"ApiKey": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Instructor ID",
 *         @OA\Schema(type="integer", example=2)
 *     ),
 *     @OA\Parameter(
 *         name="date",
 *         in="query",
 *         required=true,
 *         description="Date (YYYY-MM-DD) to check",
 *         @OA\Schema(type="string", example="2025-05-10")
 *     ),
 *     @OA\Response(response=200, description="List of bookings for that instructor on the given date")
 * )
 */
Flight::route('GET /instructors/@id/bookings', function ($id) {
    Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);

    $date = Flight::request()->query['date'];
    if (!$date) {
        Flight::halt(400, "Missing required 'date' parameter.");
    }

    try {
        $bookings = Flight::bookingService()->getInstructorBookingsByDate($id, $date);
        Flight::json($bookings);
    } catch (Exception $e) {
        Flight::halt(500, $e->getMessage());
    }
});

/**
 * @OA\Post(
 *     path="/bookings/ski-school",
 *     tags={"Bookings"},
 *     summary="Create a new Ski School booking",
 *     security={{"ApiKey": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={
 *                 "user_id", "service_id", "session_type", "num_of_spots", "week"
 *             },
 *             @OA\Property(property="user_id", type="integer", example=1, description="ID of the user booking"),
 *             @OA\Property(property="service_id", type="integer", example=4, description="Service ID for ski school"),
 *             @OA\Property(property="session_type", type="string", example="Ski_school", description="Must be 'Ski_school'"),
 *             @OA\Property(property="num_of_spots", type="integer", example=3, description="Total number of spots reserved"),
 *             @OA\Property(property="week", type="string", example="week1", description="Ski school week (week1–week4)"),
 *             @OA\Property(property="age_group_child", type="integer", example=1, description="Number of children (0–12)"),
 *             @OA\Property(property="age_group_teen", type="integer", example=1, description="Number of teens (13–17)"),
 *             @OA\Property(property="age_group_adult", type="integer", example=1, description="Number of adults (18+)"),
 *             @OA\Property(property="ski_level_b", type="integer", example=1, description="Beginner level count"),
 *             @OA\Property(property="ski_level_i", type="integer", example=1, description="Intermediate level count"),
 *             @OA\Property(property="ski_level_a", type="integer", example=1, description="Advanced level count"),
 *             @OA\Property(property="veg_count", type="integer", example=1, description="Optional vegetarian count"),
 *             @OA\Property(property="other", type="string", example="Allergic to nuts", description="Optional other concerns")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Ski School booking created",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Ski School booking created"),
 *             @OA\Property(property="booking_id", type="integer", example=15)
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Validation error or overbooking"
 *     )
 * )
 */

Flight::route('POST /bookings/ski-school', function () {
    Flight::auth_middleware()->authorizeRole(Roles::USER);

    try {
        $data = Flight::request()->data->getData();
        $bookingId = Flight::bookingService()->createSkiSchoolBooking($data);
        Flight::json(["message" => "Ski School booking created", "booking_id" => $bookingId]);
    } catch (Exception $e) {
        Flight::halt(400, $e->getMessage());
    }
});

/**
 * @OA\Get(
 *     path="/bookings/user/{id}",
 *     tags={"Bookings"},
 *     summary="Get all bookings by a specific user",
 *     security={{"ApiKey": {}}}, 
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="User ID",
 *         @OA\Schema(type="integer", example=2)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="List of user's bookings"
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized"
 *     )
 * )
 */
Flight::route('GET /bookings/user/@id', function ($id) {
    Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
    try {
        $bookings = Flight::bookingService()->getBookingsByUserId($id);
        Flight::json($bookings);
    } catch (Exception $e) {
        Flight::halt(400, $e->getMessage());
    }
});
