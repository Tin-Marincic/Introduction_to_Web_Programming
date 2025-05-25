<?php
require_once 'BaseDao.php';

class AvailabilityCalendarDao extends BaseDao {
    public function __construct() {
        parent::__construct("availabilityCalendar");
    }

    // Check if availability already exists for instructor/date/status
    public function exists($instructorId, $date, $status) {
        $stmt = $this->connection->prepare(
            "SELECT COUNT(*) FROM availabilityCalendar WHERE instructor_id = :instructor_id AND date = :date AND status = :status"
        );
        $stmt->bindParam(':instructor_id', $instructorId);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':status', $status);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    // Check if there's already availability for instructor/date 
    public function hasConflict($instructorId, $date) {
        $stmt = $this->connection->prepare(
            "SELECT COUNT(*) FROM availabilityCalendar WHERE instructor_id = :instructor_id AND date = :date"
        );
        $stmt->bindParam(':instructor_id', $instructorId);
        $stmt->bindParam(':date', $date);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    // Get availability by instructor (custom query)
    public function getAvailabilityByInstructor($instructorId) {
        $stmt = $this->connection->prepare(
            "SELECT * FROM availabilityCalendar WHERE instructor_id = :instructor_id AND date >= CURDATE()"
        );
        $stmt->bindParam(':instructor_id', $instructorId);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAvailableInstructorsByDate($date) {
    $stmt = $this->connection->prepare("
        SELECT DISTINCT instructor_id 
        FROM availabilityCalendar 
        WHERE date = :date AND status = 'active'
    ");
    $stmt->bindParam(':date', $date);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}


}
