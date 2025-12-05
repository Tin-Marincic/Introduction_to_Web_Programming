<?php
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../data/roles.php';

/**
 * @OA\Get(
 *     path="/users",
 *     tags={"Users"},
 *     summary="Get all users",
 *     security={{"ApiKey": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="List of users"
 *     )
 * )
 */
Flight::route('GET /users', function () {
    Flight::auth_middleware()->authorizeRoles([Roles::ADMIN]);
    $result = Flight::userService()->getAll();

    if (empty($result)) {
        Flight::json(["message" => "No users found"], 200);
    } else {
        Flight::json($result);
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
 *     summary="Add a new instructor (admin only, with optional image upload)",
 *     security={{"ApiKey": {}}}, 
 *     @OA\RequestBody(
 *         required=true,
 *         description="Send as multipart/form-data",
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 required={"name", "surname", "licence", "username", "password"},
 *                 @OA\Property(property="name", type="string", example="Malik"),
 *                 @OA\Property(property="surname", type="string", example="Sabotić"),
 *                 @OA\Property(property="licence", type="string", example="U1"),
 *                 @OA\Property(property="username", type="string", example="malik@example.com"),
 *                 @OA\Property(property="password", type="string", example="secure123"),
 *                 @OA\Property(
 *                     property="image",
 *                     type="string",
 *                     format="binary",
 *                     description="Optional instructor profile image"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(response=200, description="Instructor added")
 * )
 */
Flight::route('POST /instructors', function () {
    Flight::auth_middleware()->authorizeRoles([Roles::ADMIN]);

    try {
        // 1) Standard fields
        $name     = Flight::request()->data->name;
        $surname  = Flight::request()->data->surname;
        $licence  = Flight::request()->data->licence;
        $username = Flight::request()->data->username;
        $password = Flight::request()->data->password;

        // 2) Create instructor in DB (returns ID)
        $id = Flight::userService()->addInstructor([
            "name"     => $name,
            "surname"  => $surname,
            "licence"  => $licence,
            "username" => $username,
            "password" => $password,
            "role"     => "instructor"
        ]);

        // 3) Optional image upload
        if (!empty($_FILES['image']['name'])) {

            $host   = $_SERVER['HTTP_HOST'] ?? 'unisport-9kjwi.ondigitalocean.app';
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $baseUrl = $scheme . '://' . $host;

            // LOCAL vs PRODUCTION behavior
            if (strpos($host, 'localhost') !== false) {
                // 🔹 Local dev → still write into frontend folder
                $uploadDir     = __DIR__ . '/../../frontend/assets/img/team/';
                $publicUrlBase = 'assets/img/team/';
                $storeFullUrl  = false; // store relative path
            } else {
                // 🔹 Production → write into backend /uploads/team
                // assuming backend document root is the "backend" folder
                $uploadDir     = __DIR__ . '/../uploads/team/';
                $publicUrlBase = $baseUrl . '/uploads/team/';
                $storeFullUrl  = true; // store full URL
            }

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Keep original extension if possible
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION) ?: 'jpg';
            $filename   = 'instructor_' . $id . '_' . time() . '.' . $ext;
            $targetPath = $uploadDir . $filename;

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                throw new Exception("Failed to upload instructor image.");
            }

            // What we store in DB
            if ($storeFullUrl) {
                // e.g. https://unisport-9kjwi.ondigitalocean.app/uploads/team/instructor_201_xxx.jpg
                $imageValue = $publicUrlBase . $filename;
            } else {
                // e.g. assets/img/team/instructor_201_xxx.jpg (relative path)
                $imageValue = $publicUrlBase . $filename;
            }

            Flight::userService()->updateInstructorImage($id, $imageValue);
        }

        Flight::json(["success" => true, "id" => $id]);

    } catch (Exception $e) {
        Flight::halt(500, $e->getMessage());
    }
});

/**
 * @OA\Put(
 *     path="/instructors/{id}",
 *     tags={"Users"},
 *     summary="Update instructor by ID (admin only)",
 *     security={{"ApiKey": {}}},
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
    Flight::auth_middleware()->authorizeRoles([Roles::ADMIN]);
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
 *     security={{"ApiKey": {}}},
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
    Flight::auth_middleware()->authorizeRoles([Roles::ADMIN]);
    try {
        Flight::json(Flight::userService()->deleteInstructor($id));
    } catch (Exception $e) {
        Flight::halt(400, $e->getMessage());
    }
});


/**
 * @OA\Get(
 *     path="/users/{id}/has-bookings",
 *     tags={"Users"},
 *     summary="Check if a user has made any bookings",
 *     security={{"ApiKey": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="User ID",
 *         @OA\Schema(type="integer", example=5)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Returns true or false",
 *         @OA\JsonContent(
 *             @OA\Property(property="has_booking", type="boolean", example=true)
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized access"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid request"
 *     )
 * )
 */
Flight::route('GET /users/@id/has-bookings', function($id) {
    Flight::auth_middleware()->authorizeRole(Roles::USER);

    try {
        $hasBooking = Flight::bookingService()->userHasBooking($id);
        Flight::json(['has_booking' => $hasBooking]);
    } catch (Exception $e) {
        Flight::halt(400, $e->getMessage());
    }
});