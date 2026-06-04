<?php
use Dotenv\Dotenv;
use PHPMailer\PHPMailer\PHPMailer;

require __DIR__ . '/../vendor/autoload.php';

class EmailUtil {
    private static bool $envLoaded = false;

    private static function loadEnv(): void {
        if (self::$envLoaded) {
            return;
        }

        Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();
        self::$envLoaded = true;
    }

    private static function env(string $key, string $default = ''): string {
        self::loadEnv();

        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($value === false || $value === null || trim((string)$value) === '') {
            return $default;
        }

        return trim((string)$value);
    }

    private static function e($value): string {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private static function setupMailer(): PHPMailer {
        $mail = new PHPMailer(true);

        $username = self::env('MAIL_USERNAME', 'unisportskolaskijanja@gmail.com');
        $password = self::env('MAIL_PASSWORD');
        $fromAddress = self::env('MAIL_FROM_ADDRESS', $username);
        $fromName = self::env('MAIL_FROM_NAME', 'Unisport Ski School');

        if ($password === '') {
            throw new RuntimeException('MAIL_PASSWORD is not configured.');
        }

        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->Host = self::env('MAIL_HOST', 'smtp.gmail.com');
        $mail->SMTPAuth = true;
        $mail->Username = $username;
        $mail->Password = $password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = (int) self::env('MAIL_PORT', '587');
        $mail->setFrom($fromAddress, $fromName);

        return $mail;
    }

    private static function adminEmail(): string {
        return self::env('MAIL_ADMIN_ADDRESS', self::env('MAIL_FROM_ADDRESS', self::env('MAIL_USERNAME', 'unisportskolaskijanja@gmail.com')));
    }

    private static function frontendUrl(): string {
        return rtrim(self::env('FRONTEND_URL', 'https://skiunisport.com'), '/');
    }

    public static function sendCancellationEmail($userEmail, $userName, $date) {
        try {
            $mail = self::setupMailer();
            $mail->addAddress($userEmail, $userName);

            $safeUserName = self::e($userName);
            $safeDate = self::e($date);

            $mail->isHTML(true);
            $mail->Subject = 'Vasa rezervacija je otkazana';
            $mail->Body = "
                <p>Poštovani/Poštovana <strong>{$safeUserName}</strong>,</p>
                <p>Mi se izvinjavamo ali Vaša rezervacija za termin <strong>{$safeDate}</strong> je otkazana.</p>
                <p>Ukoliko želite, možete izvršiti novu rezervaciju putem našeg sistema.</p>
                <br>
                <p><strong>Unisport Škola Skijanja</strong></p>

                <hr>

                <p>Dear <strong>{$safeUserName}</strong>,</p>
                <p>We are sorry but your booking for <strong>{$safeDate}</strong> has been cancelled.</p>
                <p>You may book a new session at any time.</p>
                <br>
                <p><strong>Unisport Ski School</strong></p>
            ";

            $mail->send();
            return true;
        } catch (Throwable $e) {
            error_log('CANCELLATION EMAIL FAILED: ' . $e->getMessage());
            return false;
        }
    }

    public static function sendAdminCancellationAlert($userName, $userEmail, $date, $time) {
        try {
            $mail = self::setupMailer();
            $mail->addAddress(self::adminEmail(), 'Admin');

            $safeUserName = self::e($userName);
            $safeUserEmail = self::e($userEmail);
            $safeDate = self::e($date);
            $safeTime = self::e($time);

            $mail->isHTML(true);
            $mail->Subject = 'Korisnik je otkazao rezervaciju';
            $mail->Body = "
                <p><strong>Obavijest:</strong> Korisnik je otkazao rezervaciju.</p>
                <p><strong>Korisnik:</strong> {$safeUserName} ({$safeUserEmail})</p>
                <p><strong>Datum:</strong> {$safeDate}</p>
                <p><strong>Vrijeme:</strong> {$safeTime}</p>
            ";

            $mail->send();
            return true;
        } catch (Throwable $e) {
            error_log('ADMIN CANCELLATION ALERT FAILED: ' . $e->getMessage());
            return false;
        }
    }

    public static function sendInstructorBookingEmail($instructorEmail, $instructorName, $clientName, $date, $time, $hours) {
        try {
            $mail = self::setupMailer();
            $mail->addAddress($instructorEmail, $instructorName);

            $safeInstructorName = self::e($instructorName);
            $safeClientName = self::e($clientName);
            $safeDate = self::e($date);
            $safeTime = self::e($time);
            $safeHours = self::e($hours);

            $mail->isHTML(true);
            $mail->Subject = "Nova rezervacija – {$clientName}";
            $mail->Body = "
                <p>Poštovani <strong>{$safeInstructorName}</strong>,</p>
                <p>Imate novu rezervaciju!</p>

                <p><strong>Korisnik:</strong> {$safeClientName}</p>
                <p><strong>Datum:</strong> {$safeDate}</p>
                <p><strong>Početak:</strong> {$safeTime}</p>
                <p><strong>Broj sati:</strong> {$safeHours}</p>

                <br>
                <p><strong>Unisport Škola Skijanja</strong></p>
            ";

            $mail->send();
            return true;
        } catch (Throwable $e) {
            error_log('INSTRUCTOR BOOKING EMAIL FAILED: ' . $e->getMessage());
            return false;
        }
    }

    public static function sendInstructorCancellationEmail($instructorEmail, $instructorName, $clientName, $date, $time) {
        try {
            $mail = self::setupMailer();
            $mail->addAddress($instructorEmail, $instructorName);

            $safeInstructorName = self::e($instructorName);
            $safeClientName = self::e($clientName);
            $safeDate = self::e($date);
            $safeTime = self::e($time);

            $mail->isHTML(true);
            $mail->Subject = "Otkazana rezervacija – {$clientName}";
            $mail->Body = "
                <p>Poštovani <strong>{$safeInstructorName}</strong>,</p>
                <p>Korisnik <strong>{$safeClientName}</strong> je otkazao rezervaciju.</p>

                <p><strong>Datum:</strong> {$safeDate}</p>
                <p><strong>Vrijeme:</strong> {$safeTime}</p>

                <br>
                <p><strong>Unisport Škola Skijanja</strong></p>
            ";

            $mail->send();
            return true;
        } catch (Throwable $e) {
            error_log('INSTRUCTOR CANCELLATION EMAIL FAILED: ' . $e->getMessage());
            return false;
        }
    }

    public static function sendPasswordResetEmail($email, $name, $token) {
        try {
            $mail = self::setupMailer();
            $mail->addAddress($email, $name);

            $resetLink = self::frontendUrl() . '/#reset_password/token=' . $token;
            $safeName = self::e($name);
            $safeResetLink = self::e($resetLink);

            $mail->isHTML(true);
            $mail->Subject = 'Resetovanje lozinke - Unisport';
            $mail->Body = "
                <p>Poštovani/Poštovana <strong>{$safeName}</strong>,</p>
                <p>Kliknite na link da resetujete lozinku:</p>
                <p><a href='{$safeResetLink}'>{$safeResetLink}</a></p>
                <p>Link ističe za 1 sat.</p>
                <br>
                <p>Unisport Ski School</p>
            ";

            $mail->send();
            return true;
        } catch (Throwable $e) {
            error_log('RESET PASSWORD EMAIL FAILED: ' . $e->getMessage());
            return false;
        }
    }

    public static function sendEmailVerification($email, $name, $token) {
        try {
            $mail = self::setupMailer();
            $mail->addAddress($email, $name);

            $verifyLink = self::frontendUrl() . '/#verify_email/token=' . $token;
            $safeName = self::e($name);
            $safeVerifyLink = self::e($verifyLink);

            $mail->isHTML(true);
            $mail->Subject = 'Potvrdite vasu email adresu / Verify your email address';
            $mail->Body = "
                <p>Poštovani/Poštovana <strong>{$safeName}</strong>,</p>
                <p>Molimo vas da kliknete na link kako biste verifikovali svoju email adresu:</p>
                <p><a href='{$safeVerifyLink}'>{$safeVerifyLink}</a></p>
                <p>Hvala što koristite Unisport.</p>

                <hr>

                <p>Dear <strong>{$safeName}</strong>,</p>
                <p>Please click the link below to verify your email address:</p>
                <p><a href='{$safeVerifyLink}'>{$safeVerifyLink}</a></p>
                <p>Thank you for using Unisport.</p>
            ";

            return $mail->send();
        } catch (Throwable $e) {
            error_log('EMAIL VERIFICATION FAILED: ' . $e->getMessage());
            return false;
        }
    }

    public static function sendDailyPrivateLessonsReport(string $htmlBody, string $dateLabel) {
        try {
            $mail = self::setupMailer();
            $mail->addAddress(self::adminEmail(), 'Unisport Admin');

            $safeDateLabel = self::e($dateLabel);

            $mail->isHTML(true);
            $mail->Subject = "Dnevni izvještaj privatnih časova – {$safeDateLabel}";
            $mail->Body = $htmlBody;

            $mail->send();
            return true;
        } catch (Throwable $e) {
            error_log('DAILY REPORT EMAIL FAILED: ' . $e->getMessage());
            return false;
        }
    }
}
