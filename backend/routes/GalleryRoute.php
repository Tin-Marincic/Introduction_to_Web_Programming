<?php

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

// === GET /gallery – list all media ===
Flight::route('GET /gallery', function () {
    try {
        $s3 = new S3Client([
            'version' => 'latest',
            'region'  => getenv('SPACES_REGION'),
            'endpoint' => getenv('SPACES_ENDPOINT'),
            'credentials' => [
                'key'    => getenv('SPACES_KEY'),
                'secret' => getenv('SPACES_SECRET'),
            ],
            'use_path_style_endpoint' => true
        ]);

        $bucket  = getenv('SPACES_BUCKET');
        $baseUrl = getenv('SPACES_BASE_URL');
        $prefix  = 'gallery/';

        $result = $s3->listObjectsV2([
            'Bucket' => $bucket,
            'Prefix' => $prefix
        ]);

        $items = [];

        if (!empty($result['Contents'])) {
            foreach ($result['Contents'] as $object) {
                $key = $object['Key'];
                if ($key === $prefix) {
                    continue;
                }

                $ext  = strtolower(pathinfo($key, PATHINFO_EXTENSION));
                $type = in_array($ext, ['mp4', 'webm', 'ogg']) ? 'video' : 'image';

                $items[] = [
                    'key'  => $key,
                    'url'  => $baseUrl . '/' . $key,
                    'type' => $type
                ];
            }
        }

        Flight::json($items);
    } catch (AwsException $e) {
        Flight::halt(500, 'Error listing gallery items: ' . $e->getMessage());
    }
});


// === POST /gallery – upload (ADMIN) ===
Flight::route('POST /gallery', function () {
    Flight::auth_middleware()->authorizeRoles([Roles::ADMIN]);

    try {
        if (empty($_FILES['media'])) {
            Flight::halt(400, 'No files uploaded.');
        }

        $s3 = new S3Client([
            'version' => 'latest',
            'region'  => getenv('SPACES_REGION'),
            'endpoint' => getenv('SPACES_ENDPOINT'),
            'credentials' => [
                'key'    => getenv('SPACES_KEY'),
                'secret' => getenv('SPACES_SECRET'),
            ],
            'use_path_style_endpoint' => true
        ]);

        $bucket  = getenv('SPACES_BUCKET');
        $baseUrl = getenv('SPACES_BASE_URL');
        $prefix  = 'gallery/';

        $files    = $_FILES['media'];
        $uploaded = [];

        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }

            $originalName = $files['name'][$i];
            $ext          = pathinfo($originalName, PATHINFO_EXTENSION) ?: 'jpg';
            $filename     = $prefix . time() . '_' . uniqid() . '.' . $ext;

            $s3->putObject([
                'Bucket'      => $bucket,
                'Key'         => $filename,
                'Body'        => fopen($files['tmp_name'][$i], 'rb'),
                'ACL'         => 'public-read',
                'ContentType' => $files['type'][$i]
            ]);

            $lowerExt = strtolower($ext);
            $type     = in_array($lowerExt, ['mp4', 'webm', 'ogg']) ? 'video' : 'image';

            $uploaded[] = [
                'key'  => $filename,
                'url'  => $baseUrl . '/' . $filename,
                'type' => $type
            ];
        }

        Flight::json(['success' => true, 'items' => $uploaded]);
    } catch (Exception $e) {
        Flight::halt(500, $e->getMessage());
    }
});


// === DELETE /gallery – delete (ADMIN) ===
Flight::route('DELETE /gallery', function () {
    Flight::auth_middleware()->authorizeRoles([Roles::ADMIN]);

    $body = json_decode(Flight::request()->getBody(), true);
    if (empty($body['key'])) {
        Flight::halt(400, 'Missing key');
    }
    $key = $body['key'];

    try {
        $s3 = new S3Client([
            'version' => 'latest',
            'region'  => getenv('SPACES_REGION'),
            'endpoint' => getenv('SPACES_ENDPOINT'),
            'credentials' => [
                'key'    => getenv('SPACES_KEY'),
                'secret' => getenv('SPACES_SECRET'),
            ],
            'use_path_style_endpoint' => true
        ]);

        $bucket = getenv('SPACES_BUCKET');

        $s3->deleteObject([
            'Bucket' => $bucket,
            'Key'    => $key
        ]);

        Flight::json(['success' => true]);
    } catch (Exception $e) {
        Flight::halt(500, $e->getMessage());
    }
});
