<?php

/**
 * @OA\Get(
 *     path="/users",
 *     tags={"Users"},
 *     summary="Get all users",
 *     @OA\Response(response=200, description="List of users")
 * )
 */
Flight::route('GET /users', function () {
    try {
        Flight::json(Flight::userService()->getAll());
    } catch (Exception $e) {
        Flight::halt(400, $e->getMessage());
    }
});

/**
 * @OA\Post(
 *     path="/users",
 *     tags={"Users"},
 *     summary="Register a new user",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name", "surname", "username", "password", "role"},
 *             @OA\Property(property="name", type="string", example="Ana"),
 *             @OA\Property(property="surname", type="string", example="Petrovic"),
 *             @OA\Property(property="username", type="string", example="ana123"),
 *             @OA\Property(property="password", type="string", example="pass123"),
 *             @OA\Property(property="role", type="string", example="user")
 *         )
 *     ),
 *     @OA\Response(response=200, description="User registered")
 * )
 */
Flight::route('POST /users', function () {
    try {
        $data = Flight::request()->data->getData();
        Flight::json(Flight::userService()->registerUser($data));
    } catch (Exception $e) {
        Flight::halt(400, $e->getMessage());
    }
});

/**
 * @OA\Get(
 *     path="/users/{role}",
 *     tags={"Users"},
 *     summary="Get users by role",
 *     @OA\Parameter(
 *         name="role",
 *         in="path",
 *         required=true,
 *         description="User role: user, instructor, or admin",
 *         @OA\Schema(type="string", example="instructor")
 *     ),
 *     @OA\Response(response=200, description="Users filtered by role")
 * )
 */
Flight::route('GET /users/@role', function($role) {
    try {
        Flight::json(Flight::userService()->getUsersByRole($role));
    } catch (Exception $e) {
        Flight::halt(400, $e->getMessage());
    }
});



/**
 * @OA\Post(
 *     path="/instructors",
 *     tags={"Users"},
 *     summary="Add a new instructor (admin only)",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name", "surname", "licence", "username", "password", "role"},
 *             @OA\Property(property="name", type="string", example="Malik"),
 *             @OA\Property(property="surname", type="string", example="Sabotic"),
 *             @OA\Property(property="licence", type="string", example="U1"),
 *             @OA\Property(property="username", type="string", example="Malik"),
 *             @OA\Property(property="password", type="string", example="secure123"),
 *             @OA\Property(property="role", type="string", example="instructor")
 *         )
 *     ),
 *     @OA\Response(response=200, description="Instructor added")
 * )
 */
Flight::route('POST /instructors', function () {
    try {
        $data = Flight::request()->data->getData();
        Flight::json(Flight::userService()->addInstructor($data));
    } catch (Exception $e) {
        Flight::halt(400, $e->getMessage());
    }
});

/**
 * @OA\Put(
 *     path="/instructors/{id}",
 *     tags={"Users"},
 *     summary="Update instructor by ID (admin only)",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Instructor ID",
 *         @OA\Schema(type="integer", example=7)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="name", type="string", example="Updated Name"),
 *             @OA\Property(property="licence", type="string", example="U2")
 *         )
 *     ),
 *     @OA\Response(response=200, description="Instructor updated")
 * )
 */
Flight::route('PUT /instructors/@id', function($id) {
    try {
        $data = Flight::request()->data->getData();
        Flight::json(Flight::userService()->updateInstructor($id, $data));
    } catch (Exception $e) {
        Flight::halt(400, $e->getMessage());
    }
});

/**
 * @OA\Delete(
 *     path="/instructors/{id}",
 *     tags={"Users"},
 *     summary="Delete instructor by ID (admin only)",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Instructor ID",
 *         @OA\Schema(type="integer", example=5)
 *     ),
 *     @OA\Response(response=200, description="Instructor deleted")
 * )
 */
Flight::route('DELETE /instructors/@id', function($id) {
    try {
        Flight::json(Flight::userService()->deleteInstructor($id));
    } catch (Exception $e) {
        Flight::halt(400, $e->getMessage());
    }
});
