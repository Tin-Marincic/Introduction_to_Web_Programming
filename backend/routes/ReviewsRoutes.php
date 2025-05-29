<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../data/roles.php';

/**
 * @OA\Get(
 *     path="/reviews",
 *     tags={"Reviews"},
 *     summary="Get all reviews",
 *     @OA\Response(response=200, description="List of all reviews")
 * )
 */
Flight::route('GET /reviews', function () {
    try {
        Flight::json(Flight::reviewService()->getAllReviewsWithUserNames());
    } catch (Exception $e) {
        Flight::halt(400, $e->getMessage());
    }
});

/**
 * @OA\Get(
 *     path="/reviews/user/{id}",
 *     tags={"Reviews"},
 *     summary="Get all reviews submitted by a user",
 *     security={{"ApiKey": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="User ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(response=200, description="List of user-specific reviews")
 * )
 */
Flight::route('GET /reviews/user/@id', function ($id) {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    try {
        Flight::json(Flight::reviewService()->getReviewsByUser($id));
    } catch (Exception $e) {
        Flight::halt(400, $e->getMessage());
    }
});

/**
 * @OA\Post(
 *     path="/reviews",
 *     tags={"Reviews"},
 *     summary="Add a new review",
 *     security={{"ApiKey": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"user_id", "grade"},
 *             @OA\Property(property="user_id", type="integer", example=2),
 *             @OA\Property(property="booking_id", type="integer", example=8),
 *             @OA\Property(property="grade", type="integer", example=4),
 *             @OA\Property(property="note", type="string", example="Great session!")
 *         )
 *     ),
 *     @OA\Response(response=200, description="Review submitted")
 * )
 */
Flight::route('POST /reviews', function () {
    Flight::auth_middleware()->authorizeRole(Roles::USER);
    try {
        $data = Flight::request()->data->getData();
        Flight::json(Flight::reviewService()->addReview($data));
    } catch (Exception $e) {
        Flight::halt(400, $e->getMessage());
    }
});

/**
 * @OA\Put(
 *     path="/reviews/{id}",
 *     tags={"Reviews"},
 *     summary="Update an existing review",
 *     security={{"ApiKey": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Review ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"grade", "note"},
 *             @OA\Property(property="grade", type="integer", example=5),
 *             @OA\Property(property="note", type="string", example="Updated note content")
 *         )
 *     ),
 *     @OA\Response(response=200, description="Review updated")
 * )
 */
Flight::route('PUT /reviews/@id', function ($id) {
    Flight::auth_middleware()->authorizeRole(Roles::USER);
    try {
        $data = Flight::request()->data->getData();
        Flight::json(Flight::reviewService()->update($id, $data));
    } catch (Exception $e) {
        Flight::halt(400, $e->getMessage());
    }
});

/**
 * @OA\Delete(
 *     path="/reviews/{id}",
 *     tags={"Reviews"},
 *     summary="Delete a review",
 *     security={{"ApiKey": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Review ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(response=200, description="Review deleted")
 * )
 */
Flight::route('DELETE /reviews/@id', function ($id) {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    try {
        Flight::json(Flight::reviewService()->delete($id));
    } catch (Exception $e) {
        Flight::halt(400, $e->getMessage());
    }
});

