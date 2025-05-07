<?php

/**
 * @OA\Get(
 *     path="/bookings/detailed",
 *     tags={"Bookings"},
 *     summary="Admin: Get detailed upcoming bookings for all instructors",
 *     @OA\Response(response=200, description="Detailed grouped bookings per instructor")
 * )
 */
Flight::route('GET /bookings/detailed', function () {
    Flight::json(Flight::bookingService()->getDetailedUpcomingInstructorBookings());
});

/**
 * @OA\Get(
 *     path="/bookings/ski-school",
 *     tags={"Bookings"},
 *     summary="Admin: Get weekly ski school availability",
 *     @OA\Response(response=200, description="Available spots for each ski school week")
 * )
 */
Flight::route('GET /bookings/ski-school', function () {
    Flight::json(Flight::bookingService()->getSkiSchoolAvailability());
});

/**
 * @OA\Get(
 *     path="/bookings/instructor/{id}/hours",
 *     tags={"Bookings"},
 *     summary="Instructor: Get total hours worked this month",
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
    try {
        Flight::json(Flight::bookingService()->getTotalHoursThisMonth($id));
    } catch (Exception $e) {
        Flight::halt(400, $e->getMessage());
    }
});

/**
 * @OA\Get(
 *     path="/bookings/instructor/{id}/count",
 *     tags={"Bookings"},
 *     summary="Instructor: Get count of upcoming bookings for this month",
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
    try {
        Flight::json(Flight::bookingService()->getUpcomingBookingsCount($id));
    } catch (Exception $e) {
        Flight::halt(400, $e->getMessage());
    }
});

/**
 * @OA\Get(
 *     path="/bookings/instructor/{id}/upcoming",
 *     tags={"Bookings"},
 *     summary="Instructor: Get upcoming bookings for this month",
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
    try {
        Flight::json(Flight::bookingService()->getDetailedUpcomingBookings($id));
    } catch (Exception $e) {
        Flight::halt(400, $e->getMessage());
    }
});

/**
 * @OA\Post(
 *     path="/bookings",
 *     tags={"Bookings"},
 *     summary="Create a new booking",
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
    try {
        $data = Flight::request()->data->getData();
        Flight::json(Flight::bookingService()->createBooking($data));
    } catch (Exception $e) {
        Flight::halt(400, $e->getMessage());
    }
});

/**
 * @OA\Delete(
 *     path="/bookings/{id}",
 *     tags={"Bookings"},
 *     summary="Delete a booking by ID",
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
    try {
        Flight::json(Flight::bookingService()->deleteBooking($id));
    } catch (Exception $e) {
        Flight::halt(400, $e->getMessage());
    }
});
