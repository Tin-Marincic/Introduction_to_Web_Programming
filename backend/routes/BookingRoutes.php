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
    Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
    $data = Flight::request()->data->getData();
    Flight::json(Flight::bookingService()->createBooking($data));
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
 *     summary="Create a new Ski School booking (single participant)",
 *     security={{"ApiKey": {}}}, 
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={
 *                 "user_id", "service_id", "session_type",
 *                 "first_name", "last_name", "phone_number", "week",
 *                 "age_group", "ski_level", "is_vegetarian"
 *             },
 *             @OA\Property(property="user_id", type="integer", example=1, description="ID of the user making the booking"),
 *             @OA\Property(property="service_id", type="integer", example=4, description="Service ID for ski school"),
 *             @OA\Property(property="session_type", type="string", example="Ski_school", description="Must be 'Ski_school'"),
 * 
 *             @OA\Property(property="first_name", type="string", example="John", description="First name of participant"),
 *             @OA\Property(property="last_name", type="string", example="Doe", description="Last name of participant"),
 *             @OA\Property(property="phone_number", type="string", example="+39 123 456 7890", description="Participant's WhatsApp phone number"),
 * 
 *             @OA\Property(property="week", type="string", example="week1", description="Ski school week (week1–week4)"),
 *             @OA\Property(property="age_group", type="string", example="teen", description="Age group: child, teen, or adult"),
 *             @OA\Property(property="ski_level", type="string", example="beginner", description="Skiing level: beginner, intermediate, or advanced"),
 *             @OA\Property(property="is_vegetarian", type="boolean", example=true, description="Whether the participant is vegetarian"),
 *             @OA\Property(property="other", type="string", example="Allergic to nuts", description="Optional: allergies or concerns")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Ski School booking created",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Ski School booking created"),
 *             @OA\Property(property="booking_id", type="integer", example=42)
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Validation error or failed booking"
 *     )
 * )
 */

Flight::route('POST /bookings/ski-school', function () {
    Flight::auth_middleware()->authorizeRole(Roles::USER);

    try {
        $data = Flight::request()->data->getData();

        // Validation (basic example)
        $required = ['user_id', 'first_name', 'last_name', 'phone_number', 'week', 'age_group', 'ski_level'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                Flight::halt(400, "Missing required field: $field");
            }
        }

        $bookingId = Flight::bookingService()->createSkiSchoolBooking($data);

        // Optional: you could also add the phone number to a separate "phone_numbers" table
        // if you have a service like Flight::phoneService()->storePhoneNumber($data);

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


/**
 * @OA\Get(
 *     path="/bookings/ski-school-bookings",
 *     tags={"Bookings"},
 *     summary="Get all Ski School bookings grouped by week for admin panel",
 *     security={{"ApiKey": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Grouped ski school bookings by week"
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized"
 *     )
 * )
 */
Flight::route('GET /bookings/ski-school-bookings', function () {
    // Only allow admins
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);

    try {
        $data = Flight::bookingService()->getSkiSchoolBookingsByWeek();
        Flight::json($data);
    } catch (Exception $e) {
        Flight::halt(400, $e->getMessage());
    }
});

/**
 * @OA\Delete(
 *     path="/bookings/range",
 *     tags={"Bookings"},
 *     summary="Delete all bookings between two dates (ADMIN ONLY)",
 *     security={{"ApiKey": {}}}, 
 * 
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"start_date", "end_date"},
 *             @OA\Property(property="start_date", type="string", format="date", example="2025-01-10"),
 *             @OA\Property(property="end_date", type="string", format="date", example="2025-01-13")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Bookings deleted successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Deleted 23 bookings between 2025-01-10 and 2025-01-13")
 *         )
 *     ),
 * 
 *     @OA\Response(
 *         response=400,
 *         description="Invalid input or unauthorized"
 *     )
 * )
 */
Flight::route('DELETE /bookings/range', function() {

    Flight::auth_middleware()->authorizeRoles([Roles::ADMIN]); // ADMIN ONLY

    try {
        $body = Flight::request()->data->getData();

        if (!isset($body['start_date']) || !isset($body['end_date'])) {
            Flight::halt(400, "start_date and end_date are required.");
        }

        $start = $body['start_date'];
        $end   = $body['end_date'];

        // Perform deletion
        $count = Flight::bookingService()->deleteBookingsInRange($start, $end);

        Flight::json([
            "message" => "Deleted $count bookings between $start and $end"
        ]);

    } catch (Exception $e) {
        Flight::halt(400, $e->getMessage());
    }
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
 *         @OA\Schema(type="integer"),
 *         description="Booking ID to delete"
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Booking deleted successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Booking deleted successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Deletion error or unauthorized"
 *     )
 * )
 */
Flight::route('DELETE /bookings/@id', function($id) {
    Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);

    try {
        $user = Flight::get('user');
        $userId = $user->id;
        $role = $user->role;

        Flight::bookingService()->deleteBooking($id, $userId, $role);
        Flight::json(["message" => "Booking deleted successfully"]);
    } catch (Exception $e) {
        Flight::halt(400, $e->getMessage());
    }
});

