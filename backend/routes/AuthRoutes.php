<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
Flight::group('/auth', function() {
    /**
     * @OA\Post(
     *     path="/auth/register",
     *     summary="Register new user.",
     *     description="Add a new user to the database.",
     *     tags={"auth"},
     *     @OA\RequestBody(
     *         description="Add new user",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name", "surname", "username", "password", "phone"},
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     example="John",
     *                     description="User's first name"
     *                 ),
     *                 @OA\Property(
     *                     property="surname",
     *                     type="string",
     *                     example="Doe",
     *                     description="User's last name"
     *                 ),
     *                 @OA\Property(
     *                     property="username",
     *                     type="string",
     *                     example="demo@gmail.com",
     *                     description="User email"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string",
     *                     example="some_password",
     *                     description="User password"
     *                 ),
     *                 @OA\Property(
     *                     property="phone",
     *                     type="string",
     *                     example="+38761111222",
     *                     description="User's phone number"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User has been added."
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error."
     *     )
     * )
     */
   Flight::route("POST /register", function () {
       $data = Flight::request()->data->getData();

       // Add name and surname to validation if needed here
       $response = Flight::auth_service()->register($data);
  
       if ($response['success']) {
           Flight::json([
               'message' => 'User registered successfully',
               'data' => $response['data']
           ]);
       } else {
           Flight::json(['error' => $response['error']], 400);
       }
   });

   /**
    * @OA\Post(
    *      path="/auth/login",
    *      tags={"auth"},
    *      summary="Login to system using email and password",
    *      @OA\Response(
    *           response=200,
    *           description="User data and JWT"
    *      ),
    *      @OA\RequestBody(
    *          description="Credentials",
    *          @OA\JsonContent(
    *              required={"username","password"},
    *              @OA\Property(property="username", type="string", example="demo@gmail.com", description="email address"),
    *              @OA\Property(property="password", type="string", example="some_password", description="password")
    *          )
    *      )
    * )
    */
   Flight::route('POST /login', function() {
       $data = Flight::request()->data->getData();


       $response = Flight::auth_service()->login($data);
  
       if ($response['success']) {
            Flight::json([
                'message' => 'User logged in successfully',
                'data' => $response['data']
            ]);
        } else {
            Flight::json([
                'error' => $response['error']
            ], 401); 
        }

   });
});
?>
