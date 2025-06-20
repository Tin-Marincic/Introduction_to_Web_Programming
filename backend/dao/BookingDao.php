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
                c.phone AS client_phone,        
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

    //get all upcoming bookings for instructor panel
    public function getDetailedUpcomingBookings($instructorId) {
        $stmt = $this->connection->prepare(
            "SELECT c.name AS client_name, c.phone, b.date, b.start_time, b.session_type, b.num_of_hours, b.status
            FROM bookings b
            JOIN users c ON b.user_id = c.id
            WHERE b.instructor_id = :instructor_id
            AND b.date >= CURDATE()
            ORDER BY b.date, b.start_time"
        );
        $stmt->bindParam(':instructor_id', $instructorId);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    //to check if there is already a booking for that date time and for that instructor
    public function hasTimeConflict($instructorId, $date, $startTime, $numOfHours) {
    $stmt = $this->connection->prepare(
        "SELECT COUNT(*) FROM bookings 
         WHERE instructor_id = :instructor_id 
         AND date = :date 
         AND (
             TIME_TO_SEC(start_time) < TIME_TO_SEC(:start_time) + (:duration * 3600)
             AND 
             TIME_TO_SEC(start_time) + (num_of_hours * 3600) > TIME_TO_SEC(:start_time)
         )"
    );
    $stmt->bindParam(':instructor_id', $instructorId);
    $stmt->bindParam(':date', $date);
    $stmt->bindParam(':start_time', $startTime);
    $stmt->bindParam(':duration', $numOfHours);
    $stmt->execute();
    return $stmt->fetchColumn() > 0;
}

//get start times and hours for time slot checking free/occupied 
    public function getBookingsForInstructorOnDate($instructorId, $date) {
    $stmt = $this->connection->prepare(
        "SELECT start_time, num_of_hours 
         FROM bookings 
         WHERE instructor_id = :instructor_id AND date = :date"
    );
    $stmt->execute([
        ':instructor_id' => $instructorId,
        ':date' => $date
    ]);
    return $stmt->fetchAll();
}

//to check if user has bookings and then to show the add review button if true
public function userHasBooking($userId) {
    $query = "SELECT COUNT(*) as total FROM bookings WHERE user_id = :user_id";
    $stmt = $this->connection->prepare($query); 
    $stmt->execute(['user_id' => $userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result && $result['total'] > 0;
}


//to show every user their own bookings
public function getBookingsByUserId($userId) {
    $stmt = $this->connection->prepare("
        SELECT 
            b.id,
            b.session_type,
            b.date,
            b.start_time,
            b.num_of_hours,
            b.num_of_spots,
            b.week,
            b.age_group_child,
            b.age_group_teen,
            b.age_group_adult,
            b.ski_level_b,
            b.ski_level_i,
            b.ski_level_a,
            b.veg_count,
            b.other,
            b.status,
            s.name AS service_name,
            u.name AS instructor_name,
            u.surname AS instructor_surname
        FROM bookings b
        LEFT JOIN services s ON b.service_id = s.id
        LEFT JOIN users u ON b.instructor_id = u.id
        WHERE b.user_id = ?
        ORDER BY b.date DESC, b.start_time ASC
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}




}
?>
