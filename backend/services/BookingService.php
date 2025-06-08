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
            throw new Exception("Date, start time, and service are required.");
        }

        
        if (strtotime($data['date']) < strtotime(date('Y-m-d'))) {
            throw new Exception("Cannot book sessions in the past.");
        }

        
        $availableIds = (new AvailabilityCalendarDao())->getAvailableInstructorsByDate($data['date']);
        if (!in_array($data['instructor_id'], $availableIds)) {
            throw new Exception("Selected instructor is not available on this date.");
        }

        
        if ($this->dao->hasTimeConflict($data['instructor_id'], $data['date'], $data['start_time'], $data['num_of_hours'])) {
            throw new Exception("Instructor is already booked during this time slot.");
        }

        return $this->dao->insert($data);
    }

    public function createSkiSchoolBooking($data) {
        $required = ["user_id", "service_id", "session_type", "num_of_spots", "week"];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new Exception("$field is required.");
            }
        }

        $numSpots = intval($data['num_of_spots']);
        if ($numSpots < 1 || $numSpots > 20) {
            throw new Exception("Number of spots must be between 1 and 20.");
        }

        $sumAgeGroups = intval($data['age_group_child'] ?? 0) +
                        intval($data['age_group_teen'] ?? 0) +
                        intval($data['age_group_adult'] ?? 0);

        $sumSkiLevels = intval($data['ski_level_b'] ?? 0) +
                        intval($data['ski_level_i'] ?? 0) +
                        intval($data['ski_level_a'] ?? 0);

        if ($sumAgeGroups !== $numSpots) {
            throw new Exception("The sum of age groups must equal the number of spots.");
        }

        if ($sumSkiLevels !== $numSpots) {
            throw new Exception("The sum of ski levels must equal the number of spots.");
        }

        $availability = $this->dao->getSkiSchoolAvailability();
        $weekMap = ["Jan 1-7" => "week1", "Jan 8-14" => "week2", "Jan 15-21" => "week3", "Jan 22-28" => "week4"];
        $weekLabel = array_search($data['week'], $weekMap);

        foreach ($availability as $entry) {
            if ($entry['Week'] === $weekLabel) {
                $availableSpots = intval(explode(' ', $entry['Available Spots'])[0]);
                if ($numSpots > $availableSpots) {
                    throw new Exception("Not enough available spots for the selected week.");
                }
            }
        }

        return $this->dao->insert($data);
    }

    private function validateNumeric($value, $label) {
        if (!is_numeric($value)) {
            throw new Exception("$label must be numeric.");
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
}
