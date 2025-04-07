<?php
require_once 'BaseDao.php';

class AvailabilityCalendarDao extends BaseDao {
    public function __construct() {
        parent::__construct("availabilityCalendar");
    }

    private function isDateInNextMonth($date) {
        $currentDate = new DateTime(); 
        $endOfMonthDate = clone $currentDate;
        $endOfMonthDate->modify('+1 month'); 

        $targetDate = new DateTime($date);
        return $targetDate >= $currentDate && $targetDate <= $endOfMonthDate;
    }

    // Add availability for an instructor
    public function addAvailability($instructorId, $date, $status) {
        if (!$this->isDateInNextMonth($date)) {
            return false; 
        }

        $stmt = $this->connection->prepare("INSERT INTO availabilityCalendar (instructor_id, date, status) VALUES (:instructor_id, :date, :status)");
        $stmt->bindParam(':instructor_id', $instructorId);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':status', $status);
        return $stmt->execute();
    }

    // Update availability for an instructor
    public function updateAvailability($availabilityId, $date, $status) {
        if (!$this->isDateInNextMonth($date)) {
            return false; 
        }

        $stmt = $this->connection->prepare("UPDATE availabilityCalendar SET date = :date, status = :status WHERE id = :id");
        $stmt->bindParam(':id', $availabilityId);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':status', $status);
        return $stmt->execute();
    }

    // Delete an availability entry
    public function deleteAvailability($availabilityId) {
        $stmt = $this->connection->prepare("DELETE FROM availabilityCalendar WHERE id = :id");
        $stmt->bindParam(':id', $availabilityId);
        return $stmt->execute();
    }

    public function getAvailabilityByInstructor($instructorId) {
        $stmt = $this->connection->prepare("SELECT * FROM availabilityCalendar WHERE instructor_id = :instructor_id AND date >= CURDATE()");
        $stmt->bindParam(':instructor_id', $instructorId);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
}
?>
