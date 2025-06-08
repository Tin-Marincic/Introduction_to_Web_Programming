<?php
require_once 'BaseDao.php';

class AvailabilityCalendarDao extends BaseDao {
    public function __construct() {
        parent::__construct("availabilitycalendar");
    }

    // Check if availability already exists for instructor/date/status so it cant overlap
    public function exists($instructorId, $date, $status) {
        $instructorId = (int)$instructorId;

        $stmt = $this->connection->prepare(
            "SELECT COUNT(*) FROM availabilitycalendar WHERE instructor_id = :instructor_id AND date = :date AND status = :status"
        );
        $stmt->bindParam(':instructor_id', $instructorId, PDO::PARAM_INT);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':status', $status);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }

    // Check if there's already availability for instructor/date 
    public function hasConflict($instructorId, $date) {
        $instructorId = (int)$instructorId;

        $stmt = $this->connection->prepare(
            "SELECT COUNT(*) FROM availabilitycalendar WHERE instructor_id = :instructor_id AND date = :date"
        );
        $stmt->bindParam(':instructor_id', $instructorId, PDO::PARAM_INT);
        $stmt->bindParam(':date', $date);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }

    // Get availability by instructor 
    public function getAvailabilityByInstructor($instructorId) {
        $instructorId = (int)$instructorId;

        $stmt = $this->connection->prepare(
            "SELECT * FROM availabilitycalendar WHERE instructor_id = :instructor_id AND date >= CURDATE()"
        );
        $stmt->bindParam(':instructor_id', $instructorId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    //for the dropdown in booking
    public function getAvailableInstructorsByDate($date) {
        $stmt = $this->connection->prepare("
            SELECT DISTINCT instructor_id 
            FROM availabilitycalendar 
            WHERE date = :date AND status = 'active'
        ");
        $stmt->bindParam(':date', $date);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
