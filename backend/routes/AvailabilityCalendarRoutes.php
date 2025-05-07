<?php

/**
 * @OA\Get(
 *     path="/availability/instructor/{id}",
 *     tags={"Availability"},
 *     summary="Get availability for a specific instructor",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Instructor ID",
 *         @OA\Schema(type="integer", example=4)
 *     ),
 *     @OA\Response(response=200, description="List of available days for the instructor")
 * )
 */
Flight::route('GET /availability/instructor/@id', function($id) {
    Flight::json(Flight::availabilityService()->getAvailabilityByInstructor($id));
});

/**
 * @OA\Post(
 *     path="/availability",
 *     tags={"Availability"},
 *     summary="Add a new availability slot",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"instructor_id", "date", "status"},
 *             @OA\Property(property="instructor_id", type="integer", example=4),
 *             @OA\Property(property="date", type="string", format="date", example="2025-05-09"),
 *             @OA\Property(property="status", type="string", example="active")
 *         )
 *     ),
 *     @OA\Response(response=200, description="Availability added")
 * )
 */
Flight::route('POST /availability', function() {
    try {
        $data = Flight::request()->data->getData();
        Flight::json(Flight::availabilityService()->addAvailability($data['instructor_id'], $data['date'], $data['status']));
    } catch (Exception $e) {
        Flight::json(["error" => $e->getMessage()], 400);
    }
});

/**
 * @OA\Put(
 *     path="/availability/{id}",
 *     tags={"Availability"},
 *     summary="Update an availability slot",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Availability ID",
 *         @OA\Schema(type="integer", example=7)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"date", "status"},
 *             @OA\Property(property="date", type="string", example="2025-05-06"),
 *             @OA\Property(property="status", type="string", example="not_active")
 *         )
 *     ),
 *     @OA\Response(response=200, description="Availability updated")
 * )
 */
Flight::route('PUT /availability/@id', function($id) {
    try {
        $data = Flight::request()->data->getData();
        Flight::json(Flight::availabilityService()->updateAvailability($id, $data['date'], $data['status']));
    } catch (Exception $e) {
        Flight::json(["error" => $e->getMessage()], 400);
    }
});

/**
 * @OA\Delete(
 *     path="/availability/{id}",
 *     tags={"Availability"},
 *     summary="Delete an availability slot",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Availability ID",
 *         @OA\Schema(type="integer", example=7)
 *     ),
 *     @OA\Response(response=200, description="Availability deleted")
 * )
 */
Flight::route('DELETE /availability/@id', function($id) {
    try {
        Flight::json(Flight::availabilityService()->delete($id)); // uses BaseService->delete
    } catch (Exception $e) {
        Flight::json(["error" => $e->getMessage()], 400);
    }
});
