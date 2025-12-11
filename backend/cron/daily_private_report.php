<?php
// backend/cron/daily_private_report.php

use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../dao/config.php';
require_once __DIR__ . '/../forms/emailUtil.php';  // adjust path if different

// ---------- Simple protection with token ----------
$token = $_GET['token'] ?? '';
$expected = getenv('CRON_SECRET') ?: 'change-me-locally';

if (!$token || $token !== $expected) {
    http_response_code(403);
    echo "Forbidden\n";
    exit;
}

// ---------- Date handling ----------
date_default_timezone_set('Europe/Sarajevo');
$todayDate = date('Y-m-d');      // for DB
$todayNice = date('d.m.Y.');     // for email

// ---------- Connect to DB ----------
try {
    $dsn = 'mysql:host=' . Config::DB_HOST() .
           ';port=' . Config::DB_PORT() .
           ';dbname=' . Config::DB_NAME() .
           ';charset=utf8mb4';

    $pdo = new PDO($dsn, Config::DB_USER(), Config::DB_PASSWORD(), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo "DB error: " . $e->getMessage() . "\n";
    exit;
}

// ---------- Get all private-instruction bookings for TODAY ----------
$systemBlockerEmail = 'systemblocker@gmail.com'; // your fake user

$sql = "
    SELECT 
        b.id,
        b.date,
        b.start_time,
        b.num_of_hours,
        s.name AS service_name,

        -- Prefer participant_* if filled by admin, otherwise fall back to user info
        COALESCE(b.participant_first_name, cli.name)    AS client_name,
        COALESCE(b.participant_last_name,  cli.surname) AS client_surname,
        COALESCE(b.participant_phone,      cli.phone)   AS client_phone,

        inst.id      AS instructor_id,
        inst.name    AS instructor_name,
        inst.surname AS instructor_surname
    FROM bookings b
    JOIN users cli   ON b.user_id = cli.id
    JOIN users inst  ON b.instructor_id = inst.id
    JOIN services s  ON b.service_id = s.id
    WHERE 
        b.session_type = 'Private_instruction'
        AND b.date = :today
        AND cli.username <> :blockerEmail
        AND b.status = 'confirmed'
    ORDER BY 
        inst.name, inst.surname, b.start_time
";


$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':today'        => $todayDate,
    ':blockerEmail' => $systemBlockerEmail,
]);

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// If there are no bookings → do NOT send email
if (!$rows || count($rows) === 0) {
    echo "No private instructions on $todayDate. Email not sent.\n";
    exit;
}

// ---------- Group by instructor ----------
$byInstructor = [];

foreach ($rows as $r) {
    $iid  = $r['instructor_id'];
    $name = $r['instructor_name'] . ' ' . $r['instructor_surname'];

    if (!isset($byInstructor[$iid])) {
        $byInstructor[$iid] = [
            'name'  => $name,
            'rows'  => [],
            'total_hours' => 0,
        ];
    }

    $byInstructor[$iid]['rows'][] = $r;
    $byInstructor[$iid]['total_hours'] += (int)$r['num_of_hours'];
}

// ---------- Build HTML body ----------
$body  = "<p>Dnevni izvjestaj privatnih casova za datum <strong>{$todayNice}</strong>.</p>";
$body .= "<p>Molimo provjerite raspored instruktora.</p>";

foreach ($byInstructor as $inst) {
    $body .= "<hr>";
    $body .= "<h3>{$inst['name']} (ukupno: {$inst['total_hours']} h)</h3>";
    $body .= "<ul>";

    foreach ($inst['rows'] as $r) {
        $time   = substr($r['start_time'], 0, 5);
        $hours  = (int)$r['num_of_hours'];
        $serv   = htmlspecialchars($r['service_name']);
        $client = htmlspecialchars($r['client_name'] . ' ' . $r['client_surname']);
        $phone  = htmlspecialchars($r['client_phone']);

        $body .= "<li>
            <strong>{$time}</strong> – {$hours} h – {$serv}<br>
            Klijent: {$client} ({$phone})
        </li>";
    }

    $body .= "</ul>";
}

$body .= "<br><p>Unisport Škola Skijanja</p>";

// ---------- Send email ----------
try {
    if (EmailUtil::sendDailyPrivateLessonsReport($body, $todayNice)) {
        echo "Report email sent for {$todayDate}.\n";
    } else {
        http_response_code(500);
        echo "Failed to send report email.\n";
    }
} catch (Exception $e) {
    http_response_code(500);
    echo "Mailer error: " . $e->getMessage() . "\n";
}
