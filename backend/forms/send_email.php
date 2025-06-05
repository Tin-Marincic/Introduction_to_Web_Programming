<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../dao/config.php'; 

header('Content-Type: application/json');

// Read Authorization header
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized: No token provided."]);
    exit;
}

$jwt = str_replace('Bearer ', '', $authHeader);

try {
    $decoded = JWT::decode($jwt, new Key(Config::JWT_SECRET(), 'HS256'));
    $name = $decoded->user->name ?? 'Unknown User';
    $email = $decoded->user->username ?? 'unknown@example.com';

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["error" => "Invalid token."]);
    exit;
}


$subject = $_POST['subject'] ?? '';
$message = $_POST['message'] ?? '';

if (!$subject || !$message) {
    http_response_code(400);
    echo json_encode(["error" => "Please fill in subject and message."]);
    exit;
}

$mail = new PHPMailer(true);

try {
    
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'skolaskijanjaunisport@gmail.com'; 
    $mail->Password = 'ezqc huvo idlq dyqy';        
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Email headers
    $mail->setFrom('skolaskijanjaunisport@gmail.com', 'Unisport Contact Form');
    $mail->addAddress('skolaskijanjaunisport@gmail.com'); 
    $mail->addReplyTo($email, $name); 

    // Email content
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = "<strong>Message from $name ($email)</strong><br><br>" . nl2br($message);

    $mail->send();
    echo json_encode(["success" => "Message sent successfully."]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Mailer Error: " . $mail->ErrorInfo]);
}
