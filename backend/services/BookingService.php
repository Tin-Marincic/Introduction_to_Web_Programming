<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/BookingDao.php';
require_once __DIR__ . '/../dao/AvailabilityCalendarDao.php'; 

class BookingService extends BaseService {

    public function __construct() {
        $this->dao = new BookingDao();
        parent::__construct($this->dao);
    }

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

    public function createBooking($data) {
        $this->validateNumeric($data['instructor_id'], "Instructor ID");
        $this->validateNumeric($data['num_of_hours'], "Number of hours");

        if (empty($data['date']) || empty($data['start_time']) || empty($data['service_id'])) {
            throw new Exception("Datum, vrijeme početka i usluga su obavezni.");
        }

        
        if (strtotime($data['date']) < strtotime(date('Y-m-d'))) {
            throw new Exception("Nije moguće rezervisati termine u prošlosti.");
        }

        
        $availableIds = (new AvailabilityCalendarDao())->getAvailableInstructorsByDate($data['date']);
        if (!in_array($data['instructor_id'], $availableIds)) {
            throw new Exception("Odabrani instruktor nije dostupan na ovaj datum.");
        }

        
        if ($this->dao->hasTimeConflict($data['instructor_id'], $data['date'], $data['start_time'], $data['num_of_hours'])) {
            throw new Exception("Instruktor je već zauzet u ovom terminu.");
        }

        return $this->dao->insert($data);
    }

    public function createSkiSchoolBooking($data) {
        // Required fields
        $required = ["user_id", "service_id", "session_type", "first_name", "last_name", "phone_number", "week", "age_group", "ski_level"];
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new Exception("$field je obavezno.");
            }
        }

        // Capacity check: 1 person per booking
        $availability = $this->getSkiSchoolAvailability();

        // Map to get readable week label
        $weekMap = ["Jan 5–9" => "week1", "Jan 12–16" => "week2", "Jan 19–23" => "week3", "Jan 26–30" => "week4"];
        $weekLabel = array_search($data['week'], $weekMap);
        if ($weekLabel === false) {
            $weekLabel = $data['week']; // fallback if already "week1"
        }

        // Find the week in current availability
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

        // Insert booking
        return $this->dao->insert($data);
    }


    private function validateNumeric($value, $label) {
        if (!is_numeric($value)) {
            throw new Exception("$label mora biti broj.");
        }
    }

    public function userHasBooking($userId) {
        $this->validateNumeric($userId, "User ID");
        return $this->dao->userHasBooking($userId);
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
    public function deleteBooking($id, $userId, $role) {
        $isAdmin = ($role === Roles::ADMIN);

        // FIRST: get booking details before deleting
        $booking = $this->dao->getBookingById($id);
        if (!$booking) {
            throw new Exception("Termin nije pronadjen.");
        }

        $deleted = $this->dao->deleteBooking($id, $userId, $isAdmin);

        if (!$deleted) {
            throw new Exception("Rezervacija nije pronađena ili niste ovlašteni da je obrišete.");
        }

        // Only send email if a USER cancelled
        if (!$isAdmin) {
            require_once __DIR__ . '/../forms/emailUtil.php';
            EmailUtil::sendAdminCancellationAlert(
                $id,
                $booking['client_name'] ?? 'Unknown User',
                $booking['client_email'] ?? '',
                $booking['date']
            );
        }

        return true;
    }

    public function deleteBookingsInRange($start, $end) {

        require_once __DIR__ . '/../forms/emailUtil.php';

        // Get affected bookings
        $affectedBookings = $this->dao->deleteBookingsInRange($start, $end);

        // Send notification emails
        foreach ($affectedBookings as $b) {
            $userEmail = $b['email'];
            $userName  = $b['name'] . " " . $b['surname'];
            $date      = $b['date'];

            EmailUtil::sendCancellationEmail($userEmail, $userName, $date);
        }

        // Block dates
        $this->dao->blockDateRange($start, $end);

        return count($affectedBookings);
    }




}

