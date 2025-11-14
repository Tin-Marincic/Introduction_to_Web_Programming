<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

class EmailUtil {

    public static function sendCancellationEmail($userEmail, $userName, $date) {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'skolaskijanjaunisport@gmail.com';
            $mail->Password = 'ezqc huvo idlq dyqy';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('skolaskijanjaunisport@gmail.com', 'Unisport Ski School');
            $mail->addAddress($userEmail, $userName);

            $mail->isHTML(true);
            $mail->Subject = "Your Booking Has Been Cancelled";
            $mail->Body = "
                <p>Dear <strong>$userName</strong>,</p>
                <p>We regret to inform you that your booking for <strong>$date</strong> has been cancelled due to unfavorable ski resort conditions.</p>
                <p>Please contact us if you would like to reschedule.</p>
                <br>
                <p>Thank you for understanding.</p>
                <p><strong>Unisport Ski School</strong></p>
            ";

            $mail->send();
            return true;

        } catch (Exception $e) {
            return false; // don't break the system on email failure
        }
    }

    public static function sendAdminCancellationAlert($bookingId, $userName, $userEmail, $date) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'skolaskijanjaunisport@gmail.com';
        $mail->Password = 'ezqc huvo idlq dyqy';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Admin email
        $mail->setFrom('skolaskijanjaunisport@gmail.com', 'Unisport Ski School');
        $mail->addAddress('skolaskijanjaunisport@gmail.com', 'Admin');

        $mail->isHTML(true);
        $mail->Subject = "A User Cancelled Their Booking";
        $mail->Body = "
            <p><strong>User Cancellation Notice</strong></p>
            <p><strong>User:</strong> $userName ($userEmail)</p>
            <p><strong>Booking ID:</strong> $bookingId</p>
            <p><strong>Date:</strong> $date</p>
            <p>The user cancelled their booking through the system.</p>
        ";

        $mail->send();
        return true;

    } catch (Exception $e) {
        return false;
    }
}

}
