<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

class EmailUtil {

    /* ============================================================
       Helper – configure PHPMailer
    ============================================================ */
    private static function setupMailer() {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'skolaskijanjaunisport@gmail.com';
        $mail->Password = 'nxeq xpsm qphx wavt';   // App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('skolaskijanjaunisport@gmail.com', 'Unisport Ski School');

        return $mail;
    }


    /* ============================================================
       1) USER receives cancellation email 
          → (Admin cancelled SINGLE booking OR admin cancelled date range)
    ============================================================ */
    public static function sendCancellationEmail($userEmail, $userName, $date) {
        try {
            $mail = self::setupMailer();
            $mail->addAddress($userEmail, $userName);

            $mail->isHTML(true);
            $mail->Subject = "Vasa rezervacija je otkazana";

            $mail->Body = "
                <p>Poštovani/Poštovana <strong>$userName</strong>,</p>
                <p>Mi se izvinjavamo ali Vaša rezervacija za termin <strong>$date</strong> je otkazana.</p>
                <p>Ukoliko želite, možete izvršiti novu rezervaciju putem našeg sistema.</p>
                <br>
                <p><strong>Unisport Škola Skijanja</strong></p>

                <hr>

                <p>Dear <strong>$userName</strong>,</p>
                <p>We are sorry but your booking for <strong>$date</strong> has been cancelled.</p>
                <p>You may book a new session at any time.</p>
                <br>
                <p><strong>Unisport Ski School</strong></p>
            ";

            $mail->send();
            return true;

        } catch (Exception $e) {
            return false;
        }
    }


    /* ============================================================
       2) ADMIN receives alert when USER cancels a booking
          → show date + time, no booking ID
    ============================================================ */
    public static function sendAdminCancellationAlert($userName, $userEmail, $date, $time) {
        try {
            $mail = self::setupMailer();
            $mail->addAddress('skolaskijanjaunisport@gmail.com', 'Admin');

            $mail->isHTML(true);
            $mail->Subject = "Korisnik je otkazao rezervaciju";

            $mail->Body = "
                <p><strong>Obavijest:</strong> Korisnik je otkazao rezervaciju.</p>
                <p><strong>Korisnik:</strong> $userName ($userEmail)</p>
                <p><strong>Datum:</strong> $date</p>
                <p><strong>Vrijeme:</strong> $time</p>
            ";

            $mail->send();
            return true;

        } catch (Exception $e) {
            return false;
        }
    }


    /* ============================================================
       3) INSTRUCTOR receives email when USER creates a booking
    ============================================================ */
    public static function sendInstructorBookingEmail($instructorEmail, $instructorName, $clientName, $date, $time, $hours) {
        try {
            $mail = self::setupMailer();
            $mail->addAddress($instructorEmail, $instructorName);

            $mail->isHTML(true);
            $mail->Subject = "Nova rezervacija – $clientName";

            $mail->Body = "
                <p>Poštovani <strong>$instructorName</strong>,</p>
                <p>Imate novu rezervaciju!</p>

                <p><strong>Korisnik:</strong> $clientName</p>
                <p><strong>Datum:</strong> $date</p>
                <p><strong>Početak:</strong> $time</p>
                <p><strong>Broj sati:</strong> $hours</p>

                <br>
                <p><strong>Unisport Škola Skijanja</strong></p>
            ";

            $mail->send();
            return true;

        } catch (Exception $e) {
            return false;
        }
    }


    /* ============================================================
       4) INSTRUCTOR receives email when USER cancels a booking
          → include date + time
    ============================================================ */
    public static function sendInstructorCancellationEmail($instructorEmail, $instructorName, $clientName, $date, $time) {
        try {
            $mail = self::setupMailer();
            $mail->addAddress($instructorEmail, $instructorName);

            $mail->isHTML(true);
            $mail->Subject = "Otkazana rezervacija – $clientName";

            $mail->Body = "
                <p>Poštovani <strong>$instructorName</strong>,</p>
                <p>Korisnik <strong>$clientName</strong> je otkazao rezervaciju.</p>

                <p><strong>Datum:</strong> $date</p>
                <p><strong>Vrijeme:</strong> $time</p>

                <br>
                <p><strong>Unisport Škola Skijanja</strong></p>
            ";

            $mail->send();
            return true;

        } catch (Exception $e) {
            return false;
        }
    }


    public static function sendPasswordResetEmail($email, $name, $token) {
        try {
            // USE SAME MAILER CONFIG AS OTHER EMAILS
            $mail = self::setupMailer();
            $mail->addAddress($email, $name);

            // AUTO-SELECT FRONTEND URL
            $host = $_SERVER['HTTP_HOST'] ?? '';

            if (strpos($host, 'localhost') !== false) {
                // Local development backend → local frontend
                $frontendURL = 'http://localhost/TinMarincic/Introduction_to_Web_Programming/frontend';

            } elseif (
                strpos($host, 'unisport-9kjwi.ondigitalocean.app') !== false ||  // backend on DO
                strpos($host, 'skiunisport.com') !== false ||                    // in case backend ever runs here
                strpos($host, 'www.skiunisport.com') !== false
            ) {
                // Production backend → main live frontend
                $frontendURL = 'https://skiunisport.com';

            } else {
                // Fallback (if you ever use some other host)
                $frontendURL = 'https://skiunisport.com';
            }

            // Construct reset link
            $resetLink = $frontendURL . '/#reset_password/token=' . $token;

            $mail->isHTML(true);
            $mail->Subject = 'Resetovanje lozinke - Unisport';

            $mail->Body = "
                <p>Poštovani/Poštovana <strong>{$name}</strong>,</p>
                <p>Kliknite na link da resetujete lozinku:</p>
                <p><a href='{$resetLink}'>{$resetLink}</a></p>
                <p>Link ističe za 1 sat.</p>
                <br>
                <p>Unisport Ski School</p>
            ";

            $mail->send();
            return true;

        } catch (Exception $e) {
            error_log('RESET PASSWORD EMAIL FAILED: ' . $e->getMessage());
            return false;
        }
    }

    public static function sendEmailVerification($email, $name, $token) {
        try {
            $mail = self::setupMailer();
            $mail->addAddress($email, $name);

            $host = $_SERVER['HTTP_HOST'] ?? '';

            if (strpos($host, 'localhost') !== false) {
                $frontendURL = 'http://localhost/TinMarincic/Introduction_to_Web_Programming/frontend';

            } elseif (
                strpos($host, 'unisport-9kjwi.ondigitalocean.app') !== false ||
                strpos($host, 'skiunisport.com') !== false ||
                strpos($host, 'www.skiunisport.com') !== false
            ) {
                $frontendURL = 'https://skiunisport.com';

            } else {
                $frontendURL = 'https://skiunisport.com';
            }

            $verifyLink = $frontendURL . '/#verify_email/token=' . $token;

            $mail->isHTML(true);
            $mail->Subject = 'Potvrdite vašu email adresu';

            $mail->Body = "
                <p>Poštovani/Poštovana <strong>{$name}</strong>,</p>
                <p>Molimo vas da kliknete na link kako biste verifikovali svoju email adresu:</p>
                <p><a href='{$verifyLink}'>{$verifyLink}</a></p>
                <p>Hvala što koristite Unisport.</p>
            ";

            return $mail->send();
        } catch (Exception $e) {
            error_log('EMAIL VERIFICATION FAILED: ' . $e->getMessage());
            return false;
        }
    }




}