<?php

/**
 * @OA\Info(
 *     title="Ski School API",
 *     description="API for managing ski school platform",
 *     version="1.0",
 *     @OA\Contact(
 *         email="tin.marincic@stu.ibu.edu.ba",
 *         name="Tin Marincic"
 *     )
 * )
 *
 * @OA\Server(
 *     url="http://localhost/TinMarincic/Introduction_to_Web_Programming/backend",
 *     description="Local API server"
 * )
 *
 * @OA\Server(
 *     url="https://unisport-9kjwi.ondigitalocean.app",
 *     description="Production API server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="ApiKey",
 *     type="apiKey",
 *     in="header",
 *     name="Authentication"
 * )
 */
