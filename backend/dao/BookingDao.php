<?php
require_once 'BaseDao.php';

class BookingDao extends BaseDao {
    public function __construct() {
        parent::__construct("bookings");
    }

    // This is for my admin panel BOOKING DETAILS FOR ALL INSTRUCTORS
    public function getDetailedUpcomingInstructorBookings() {
        $stmt = $this->connection->prepare(
            "SELECT 
                u.name AS instructor_name, 
                u.surname AS instructor_surname, 
                c.name AS client_name, 
                b.date, 
                b.start_time, 
                b.session_type, 
                b.num_of_hours, 
                b.status 
            FROM bookings b 
            JOIN users u ON b.instructor_id = u.id 
            JOIN users c ON b.user_id = c.id
            WHERE b.date >= CURDATE() AND u.role = 'instructor'
            ORDER BY u.surname, u.name, b.date, b.start_time ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);
    }

    // Get available spots for ski school bookings for each week in January FOR ADMIN
    public function getSkiSchoolAvailability() {
        $totalSpotsPerWeek = 100;
        $stmt = $this->connection->prepare(
            "SELECT 
                week,
                SUM(num_of_spots) AS spots_booked
            FROM bookings 
            WHERE session_type = 'Ski_school'
            GROUP BY week
            ORDER BY FIELD(week, 'week1', 'week2', 'week3', 'week4')"
        );
        $stmt->execute();
        $results = $stmt->fetchAll();
    
        $weeks = ['week1' => 'Jan 1-7', 'week2' => 'Jan 8-14', 'week3' => 'Jan 15-21', 'week4' => 'Jan 22-28'];
        $availability = [];
        foreach ($weeks as $week => $dateRange) {
            $availability[$dateRange] = $totalSpotsPerWeek;
        }
        foreach ($results as $result) {
            $weekRange = $weeks[$result['week']];
            $availability[$weekRange] = max(0, $totalSpotsPerWeek - $result['spots_booked']);
        }
        $formattedResults = [];
        foreach ($availability as $weekRange => $availableSpots) {
            $formattedResults[] = [
                'Week' => $weekRange,
                'Available Spots' => "{$availableSpots} spots available"
            ];
        }
        return $formattedResults;
    }
    
// Calculate total hours worked by an instructor this month FOR INSTRUCTOR
public function getTotalHoursThisMonth($instructorId) {
    $stmt = $this->connection->prepare(
        "SELECT SUM(num_of_hours) AS total_hours
         FROM bookings
         WHERE instructor_id = :instructor_id AND 
               YEAR(date) = YEAR(CURDATE()) AND MONTH(date) = MONTH(CURDATE())"
    );
    $stmt->bindParam(':instructor_id', $instructorId);
    $stmt->execute();
    $result = $stmt->fetch();
    return $result ? $result['total_hours'] : 0;
}

// Get the count of upcoming bookings for this month for that instructor INSTRUCTOR PANEL
public function getUpcomingBookingsCount($instructorId) {
    $stmt = $this->connection->prepare(
        "SELECT COUNT(*) AS bookings_count
         FROM bookings
         WHERE instructor_id = :instructor_id AND
               date >= CURDATE() AND 
               YEAR(date) = YEAR(CURDATE()) AND MONTH(date) = MONTH(CURDATE())"
    );
    $stmt->bindParam(':instructor_id', $instructorId);
    $stmt->execute();
    $result = $stmt->fetch();
    return $result ? $result['bookings_count'] : 0;
}

// Get detailed upcoming bookings for an instructor for the current month INSTRUCTOR PANEL
public function getDetailedUpcomingBookings($instructorId) {
    $stmt = $this->connection->prepare(
        "SELECT c.name AS client_name, b.date, b.start_time, b.session_type, b.num_of_hours, b.status
         FROM bookings b
         JOIN users c ON b.user_id = c.id
         WHERE b.instructor_id = :instructor_id AND 
               b.date >= CURDATE() AND 
               YEAR(b.date) = YEAR(CURDATE()) AND MONTH(b.date) = MONTH(CURDATE())
         ORDER BY b.date, b.start_time"
    );
    $stmt->bindParam(':instructor_id', $instructorId);
    $stmt->execute();
    return $stmt->fetchAll();
}

}
?>
