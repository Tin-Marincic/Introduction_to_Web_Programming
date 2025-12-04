<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/BookingDao.php';
require_once __DIR__ . '/../dao/AvailabilityCalendarDao.php';
require_once __DIR__ . '/../forms/emailUtil.php';

class BookingService extends BaseService {

    public function __construct() {
        $this->dao = new BookingDao();
        parent::__construct($this->dao);
    }

    /* ============================================================
       GETTERS
    ============================================================ */
    public function getDetailedUpcomingInstructorBookings() {
        return $this->dao->getDetailedUpcomingInstructorBookings();
    }

    public function getSkiSchoolAvailability() {
        return $this->dao->getSkiSchoolAvailability();
    }

    public function getTotalHoursThisMonth($instructorId) {
        $this->validateNumeric($instructorId, "Instructor ID");
        return $this->dao->getTotalHoursThisMonth($instructorId);
    }

    public function getUpcomingBookingsCount($instructorId) {
        $this->validateNumeric($instructorId, "Instructor ID");
        return $this->dao->getUpcomingBookingsCount($instructorId);
    }

    public function getDetailedUpcomingBookings($instructorId) {
        $this->validateNumeric($instructorId, "Instructor ID");
        return $this->dao->getDetailedUpcomingBookings($instructorId);
    }

    public function getInstructorBookingsByDate($instructorId, $date) {
        return $this->dao->getBookingsForInstructorOnDate($instructorId, $date);
    }

    public function getBookingsByUserId($userId) {
        return $this->dao->getBookingsByUserId($userId);
    }

    public function getSkiSchoolBookingsByWeek() {
        return $this->dao->getSkiSchoolBookingsByWeek();
    }

    /* ============================================================
       CREATE PRIVATE INSTRUCTION BOOKING
       → sends email to instructor
    ============================================================ */
    public function createBooking($data) {
        $this->validateNumeric($data['instructor_id'], "Instructor ID");
        $this->validateNumeric($data['num_of_hours'], "Number of hours");

        if (empty($data['date']) || empty($data['start_time']) || empty($data['service_id'])) {
            throw new Exception("Datum, vrijeme početka i usluga su obavezni.");
        }

        // Past date check
        if (strtotime($data['date']) < strtotime(date('Y-m-d'))) {
            throw new Exception("Nije moguće rezervisati termine u prošlosti.");
        }

        // Check instructor availability
        $availableIds = (new AvailabilityCalendarDao())->getAvailableInstructorsByDate($data['date']);
        if (!in_array($data['instructor_id'], $availableIds)) {
            throw new Exception("Odabrani instruktor nije dostupan na ovaj datum.");
        }

        // Time conflict check
        if ($this->dao->hasTimeConflict($data['instructor_id'], $data['date'], $data['start_time'], $data['num_of_hours'])) {
            throw new Exception("Instruktor je već zauzet u ovom terminu.");
        }

        // Insert booking in DB
        $bookingId = $this->dao->insert($data);

        // Fetch instructor + client info for email
        $details = $this->dao->getBookingById($bookingId);

        if ($details) {
            $instructorEmail = $details['instructor_email'] ?? null;
            $instructorName  = $details['instructor_name'] ?? null;
            $clientName      = $details['client_name'] ?? null;
            $date            = $details['date'];
            $startTime       = $details['start_time'];
            $hours           = $details['num_of_hours'];

            if ($instructorEmail) {
                EmailUtil::sendInstructorBookingEmail(
                    $instructorEmail,
                    $instructorName,
                    $clientName,
                    $date,
                    $startTime,
                    $hours
                );
            }
        }

        return $bookingId;
    }

    /* ============================================================
        CHECK IF USER HAS BOOKINGS (for reviews)
        ============================================================ */
        public function userHasBooking($userId) {
            return $this->dao->userHasBooking($userId);
        }



    /* ============================================================
       CREATE SKI SCHOOL BOOKING
    ============================================================ */
    public function createSkiSchoolBooking($data) {
        $required = [
            "user_id", "service_id", "session_type", "first_name",
            "last_name", "phone_number", "week", "date_of_birth", "ski_level"
        ];


        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new Exception("$field je obavezno.");
            }
        }

        // Check capacity
        $availability = $this->getSkiSchoolAvailability();
        $weekMap = ["Jan 5–9" => "sedmica_1", "Jan 12–16" => "sedmica_2", "Jan 19–23" => "sedmica_3", "Jan 26–30" => "sedmica_4"];
        $weekLabel = array_search($data['week'], $weekMap);

        if ($weekLabel === false) $weekLabel = $data['week'];

        $availableSpots = 0;
        foreach ($availability as $entry) {
            if ($entry['Week'] === $weekLabel || strpos($entry['Week'], $weekLabel) !== false) {
                $availableSpots = intval(explode(' ', $entry['Available Spots'])[0]);
                break;
            }
        }

        if ($availableSpots <= 0) {
            throw new Exception("Nema više slobodnih mjesta za odabranu sedmicu.");
        }

        return $this->dao->insert($data);
    }


    /* ============================================================
       DELETE SINGLE BOOKING
       → User cancels → email to instructor + admin
       → Admin cancels → email to user
    ============================================================ */
    public function deleteBooking($id, $userId, $role) {
        $isAdmin = ($role === Roles::ADMIN);

        // Get booking details BEFORE delete
        $booking = $this->dao->getBookingById($id);
        if (!$booking) {
            throw new Exception("Termin nije pronadjen.");
        }

        // Perform delete
        $deleted = $this->dao->deleteBooking($id, $userId, $isAdmin);
        if (!$deleted) {
            throw new Exception("Rezervacija nije pronađena ili niste ovlašteni da je obrišete.");
        }

        /* ------------------------------------------------------------
           CASE 1 → USER cancels booking
           Notify instructor + admin
        ------------------------------------------------------------ */
        if (!$isAdmin) {
            EmailUtil::sendInstructorCancellationEmail(
                $booking['instructor_email'],
                $booking['instructor_name'],
                $booking['client_name'],
                $booking['date']
            );

            EmailUtil::sendAdminCancellationAlert(
                $id,
                $booking['client_name'],
                $booking['client_email'],
                $booking['date']
            );
        }

        /* ------------------------------------------------------------
           CASE 2 → ADMIN cancels single booking
           Send email to user
        ------------------------------------------------------------ */
        if ($isAdmin) {
            EmailUtil::sendCancellationEmail(
                $booking['client_email'],
                $booking['client_name'],
                $booking['date']
            );
        }

        return true;
    }


    /* ============================================================
       DELETE RANGE OF BOOKINGS (admin)
       Existing feature – users receive email
    ============================================================ */
    public function deleteBookingsInRange($start, $end) {
        $affectedBookings = $this->dao->deleteBookingsInRange($start, $end);

        foreach ($affectedBookings as $b) {
            EmailUtil::sendCancellationEmail(
                $b['email'],
                $b['name'] . " " . $b['surname'],
                $b['date']
            );
        }

        $this->dao->blockDateRange($start, $end);
        return count($affectedBookings);
    }


    /* ============================================================
       VALIDATION
    ============================================================ */
    private function validateNumeric($value, $label) {
        if (!is_numeric($value)) {
            throw new Exception("$label mora biti broj.");
        }
    }
}
