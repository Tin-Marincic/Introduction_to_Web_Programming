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
                <p>Vaša rezervacija za termin <strong>$date</strong> je otkazana.</p>
                <p>Ukoliko želite, možete izvršiti novu rezervaciju putem našeg sistema.</p>
                <br>
                <p><strong>Unisport Škola Skijanja</strong></p>

                <hr>

                <p>Dear <strong>$userName</strong>,</p>
                <p>Your booking for <strong>$date</strong> has been cancelled.</p>
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
    ============================================================ */
    public static function sendAdminCancellationAlert($bookingId, $userName, $userEmail, $date) {
        try {
            $mail = self::setupMailer();
            $mail->addAddress('skolaskijanjaunisport@gmail.com', 'Admin');

            $mail->isHTML(true);
            $mail->Subject = "Korisnik je otkazao rezervaciju";

            $mail->Body = "
                <p><strong>Obavijest:</strong> Korisnik je otkazao rezervaciju.</p>
                <p><strong>Korisnik:</strong> $userName ($userEmail)</p>
                <p><strong>ID rezervacije:</strong> $bookingId</p>
                <p><strong>Datum:</strong> $date</p>
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
    ============================================================ */
    public static function sendInstructorCancellationEmail($instructorEmail, $instructorName, $clientName, $date) {
        try {
            $mail = self::setupMailer();
            $mail->addAddress($instructorEmail, $instructorName);

            $mail->isHTML(true);
            $mail->Subject = "Otkazana rezervacija – $clientName";

            $mail->Body = "
                <p>Poštovani <strong>$instructorName</strong>,</p>
                <p>Korisnik <strong>$clientName</strong> je otkazao rezervaciju.</p>

                <p><strong>Datum:</strong> $date</p>

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
        $frontendURL = (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false)
            ? "http://localhost/TinMarincic/Introduction_to_Web_Programming/frontend"
            : "https://unisport-frontend-rg53w.ondigitalocean.app";

        $resetLink = "$frontendURL/#reset_password/token=$token";

        $mail->isHTML(true);
        $mail->Subject = "Resetovanje lozinke - Unisport";

        $mail->Body = "
            <p>Poštovani/Poštovana <strong>$name</strong>,</p>
            <p>Kliknite na link da resetujete lozinku:</p>
            <p><a href='$resetLink'>$resetLink</a></p>
            <p>Link ističe za 1 sat.</p>
            <br>
            <p>Unisport Ski School</p>
        ";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("RESET PASSWORD EMAIL FAILED: " . $e->getMessage());
        return false;
    }
}


}
