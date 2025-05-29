<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../data/roles.php';

/**
 * @OA\Get(
 *     path="/api/services",
 *     tags={"Services"},
 *     summary="Get all services",
 *     security={{"ApiKey": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Array of all available services"
 *     )
 * )
 */
Flight::route('GET /api/services', function () {
    Flight::json(Flight::servicesService()->getAll());
});

/**
 * @OA\Post(
 *     path="/api/services",
 *     tags={"Services"},
 *     summary="Add a new service",
 *     security={{"ApiKey": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name", "description", "price", "valid_from", "valid_to"},
 *             @OA\Property(property="name", type="string", example="Private Lesson"),
 *             @OA\Property(property="description", type="string", example="One-on-one coaching session"),
 *             @OA\Property(property="price", type="number", example=120.00),
 *             @OA\Property(property="valid_from", type="string", format="date", example="2025-06-01"),
 *             @OA\Property(property="valid_to", type="string", format="date", example="2025-08-31")
 *         )
 *     ),
 *     @OA\Response(response=200, description="Service added")
 * )
 */
Flight::route('POST /api/services', function () {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    try {
        $data = Flight::request()->data->getData();
        Flight::json(Flight::servicesService()->create($data));
    } catch (Exception $e) {
        Flight::halt(400, $e->getMessage());
    }
});

/**
 * @OA\Put(
 *     path="/api/services/{id}",
 *     tags={"Services"},
 *     summary="Update an existing service",
 *     security={{"ApiKey": {}}}, 
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Service ID",
 *         @OA\Schema(type="integer", example=2)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="name", type="string", example="Updated Lesson"),
 *             @OA\Property(property="description", type="string", example="Updated description for the lesson"),
 *             @OA\Property(property="price", type="number", example=140.00)
 *         )
 *     ),
 *     @OA\Response(response=200, description="Service updated")
 * )
 */
Flight::route('PUT /api/services/@id', function ($id) {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    try {
        $data = Flight::request()->data->getData();
        Flight::json(Flight::servicesService()->update($id, $data));
    } catch (Exception $e) {
        Flight::halt(400, $e->getMessage());
    }
});

/**
 * @OA\Delete(
 *     path="/api/services/{id}",
 *     tags={"Services"},
 *     summary="Delete a service",
 *     security={{"ApiKey": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Service ID",
 *         @OA\Schema(type="integer", example=2)
 *     ),
 *     @OA\Response(response=200, description="Service deleted")
 * )
 */
Flight::route('DELETE /api/services/@id', function ($id) {
    Flight::auth_middleware()->authorizeRole(Roles::ADMIN);
    try {
        Flight::json(Flight::servicesService()->delete($id));
    } catch (Exception $e) {
        Flight::halt(400, $e->getMessage());
    }
});
