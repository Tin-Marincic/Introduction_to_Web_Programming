<?php
use Dotenv\Dotenv;
use PHPMailer\PHPMailer\PHPMailer;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require __DIR__ . '/../vendor/autoload.php';

Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();

require_once __DIR__ . '/../dao/config.php';

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedOrigins = [
    'https://unisport-frontend-rg53w.ondigitalocean.app',
    'https://skiunisport.com',
    'https://www.skiunisport.com',
    'http://127.0.0.1',
    'http://localhost'
];

if (in_array($origin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: {$origin}");
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Allow-Credentials: true');
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

header('Content-Type: application/json');

function env_value(string $key, string $default = ''): string {
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

    if ($value === false || $value === null || trim((string)$value) === '') {
        return $default;
    }

    return trim((string)$value);
}

function response_json(int $statusCode, array $payload): void {
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

function html_value($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
    response_json(401, ['error' => 'Unauthorized: No token provided.']);
}

$jwt = str_replace('Bearer ', '', $authHeader);

try {
    $decoded = JWT::decode($jwt, new Key(Config::JWT_SECRET(), 'HS256'));
    $name = $decoded->user->name ?? 'Unknown User';
    $email = $decoded->user->username ?? 'unknown@example.com';
} catch (Throwable $e) {
    response_json(401, ['error' => 'Invalid token.']);
}

$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($subject === '' || $message === '') {
    response_json(400, ['error' => 'Molim Vas upisite i predmet i poruku']);
}

try {
    $username = env_value('MAIL_USERNAME', 'unisportskolaskijanja@gmail.com');
    $password = env_value('MAIL_PASSWORD');
    $fromAddress = env_value('MAIL_FROM_ADDRESS', $username);
    $adminAddress = env_value('MAIL_ADMIN_ADDRESS', $fromAddress);
    $contactFromName = env_value('MAIL_CONTACT_FROM_NAME', 'Unisport Contact Form');

    if ($password === '') {
        throw new RuntimeException('MAIL_PASSWORD is not configured.');
    }

    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';
    $mail->isSMTP();
    $mail->Host = env_value('MAIL_HOST', 'smtp.gmail.com');
    $mail->SMTPAuth = true;
    $mail->Username = $username;
    $mail->Password = $password;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = (int) env_value('MAIL_PORT', '587');

    $mail->setFrom($fromAddress, $contactFromName);
    $mail->addAddress($adminAddress, 'Unisport Admin');
    $mail->addReplyTo($email, $name);

    $safeName = html_value($name);
    $safeEmail = html_value($email);
    $safeMessage = nl2br(html_value($message));

    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = "<strong>Poruka od {$safeName} ({$safeEmail})</strong><br><br>{$safeMessage}";

    $mail->send();
    response_json(200, ['success' => 'Poruka uspjesno poslana']);
} catch (Throwable $e) {
    response_json(500, ['error' => 'Mailer Error: email could not be sent.']);
}
