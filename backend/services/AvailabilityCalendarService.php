<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/AvailabilityCalendarDao.php';
require_once __DIR__ . '/../dao/BookingDao.php';

class AvailabilityCalendarService extends BaseService {

    public function __construct() {
        $this->dao = new AvailabilityCalendarDao();
        parent::__construct($this->dao);
    }

    public function addAvailability($instructorId, $date, $status) {
        if (!is_numeric($instructorId)) throw new Exception("Instructor ID must be numeric.");
        if (empty($date) || empty($status)) throw new Exception("Date and status are required.");
        if (strtotime($date) < strtotime(date('Y-m-d'))) throw new Exception("Cannot add availability for past dates.");
        if ($this->dao->exists($instructorId, $date, $status)) throw new Exception("This availability already exists.");
        if ($this->dao->hasConflict($instructorId, $date)) throw new Exception("Another availability exists for this instructor and date.");

        return $this->dao->insert([
            'instructor_id' => $instructorId,
            'date' => $date,
            'status' => $status
        ]);
    }

    public function updateAvailability($id, $date, $status) {
        if (!is_numeric($id)) throw new Exception("Availability ID must be numeric.");
        if (empty($date) || empty($status)) throw new Exception("Date and status are required.");
        if (strtotime($date) < strtotime(date('Y-m-d'))) throw new Exception("Cannot update availability to a past date.");

        
        if ($status === 'not_active') {
            $availability = $this->dao->getById($id);
            if ($availability) {
                $instructorId = $availability['instructor_id'];
                $bookingDao = new BookingDao();
                $bookings = $bookingDao->getBookingsForInstructorOnDate($instructorId, $date);
                if (!empty($bookings)) {
                    throw new Exception("Cannot deactivate availability — there are existing bookings on this date.");
                }
            }
        }

        return $this->dao->update($id, [
            'date' => $date,
            'status' => $status
        ]);
    }

    public function getAvailabilityByInstructor($instructorId) {
        if (!is_numeric($instructorId)) throw new Exception("Instructor ID must be numeric.");
        return $this->dao->getAvailabilityByInstructor($instructorId);
    }

    public function getAvailableInstructorsByDate($date) {
        if (empty($date)) throw new Exception("Date parameter is required.");
        return $this->dao->getAvailableInstructorsByDate($date);
    }
}
