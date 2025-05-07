<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/BookingDao.php';

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

        if ($this->dao->hasTimeConflict($data['instructor_id'], $data['date'], $data['start_time'], $data['num_of_hours'])) {
            throw new Exception("Instructor is already booked during this time slot.");
        }

        return $this->dao->insert($data);
    }

    private function validateNumeric($value, $label) {
        if (!is_numeric($value)) {
            throw new Exception("$label must be numeric.");
        }
    }
}
