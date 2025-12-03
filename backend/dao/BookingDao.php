<?php
require_once 'BaseDao.php';

class BookingDao extends BaseDao {
    public function __construct() {
        parent::__construct("bookings");
    }

    public function getDetailedUpcomingInstructorBookings() {
        $stmt = $this->connection->prepare(
        "SELECT 
            CONCAT(u.name, ' ', u.surname) AS instructor_full_name,
            b.id AS booking_id,
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
        WHERE b.date >= CURDATE()
        ORDER BY u.surname, u.name, b.date, b.start_time"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);
    }

    // Get available spots for ski school bookings for each week in January FOR ADMIN
    public function getSkiSchoolAvailability() {
        $totalSpotsPerWeek = 100; // capacity per week

        $stmt = $this->connection->prepare("
            SELECT 
                week,
                COUNT(*) AS spots_booked
            FROM bookings
            WHERE session_type = 'Ski_school'
            GROUP BY week
            ORDER BY FIELD(week, 'week1', 'week2', 'week3', 'week4')
        ");
        $stmt->execute();
        $results = $stmt->fetchAll();

        $weeks = [
            'week1' => 'Jan 5–9',
            'week2' => 'Jan 12–16',
            'week3' => 'Jan 19–23',
            'week4' => 'Jan 26–30'
        ];

        $availability = [];
        foreach ($weeks as $week => $dateRange) {
            $availability[$dateRange] = $totalSpotsPerWeek;
        }

        foreach ($results as $result) {
            $weekRange = $weeks[$result['week']] ?? $result['week'];
            $availability[$weekRange] = max(0, $totalSpotsPerWeek - intval($result['spots_booked']));
        }

        $formattedResults = [];
        foreach ($availability as $weekRange => $availableSpots) {
            $formattedResults[] = [
                'Week' => $weekRange,
                'Available Spots' => "{$availableSpots} mjesta slobodno"
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
            "SELECT 
                b.id AS booking_id,
                c.name AS client_name, 
                c.phone, 
                b.date, 
                b.start_time, 
                b.session_type, 
                b.num_of_hours, 
                b.status
            FROM bookings b
            JOIN users c ON b.user_id = c.id
            WHERE b.instructor_id = :instructor_id
            AND b.date >= CURDATE()
            ORDER BY b.date, b.start_time"
        );

        $stmt->bindParam(':instructor_id', $instructorId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            b.week,
            b.first_name,
            b.last_name,
            b.phone_number,
            b.date_of_birth,
            b.ski_level,
            b.address,
            b.is_vegetarian,
            b.allergies AS other,
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


// Get all Ski School bookings grouped by week for admin
public function getSkiSchoolBookingsByWeek() {
    $stmt = $this->connection->prepare("
        SELECT
            b.id AS booking_id,
            b.week,
            u.name AS user_name,
            u.surname AS user_surname,
            b.first_name AS child_first_name,
            b.last_name AS child_last_name,
            b.phone_number,
            b.date_of_birth,
            b.ski_level,
            b.address,
            b.allergies,
            b.is_vegetarian,
            b.date,
            b.status
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        WHERE b.session_type = 'Ski_school'
        ORDER BY 
            FIELD(b.week, 'week1', 'week2', 'week3', 'week4'),
            b.age_group ASC,
            b.date ASC
    ");
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Group results by week
    $grouped = [
        'week1' => [],
        'week2' => [],
        'week3' => [],
        'week4' => []
    ];

    foreach ($results as $row) {
        if (isset($grouped[$row['week']])) {
            $grouped[$row['week']][] = $row;
        } else {
            // fallback if some weird week value is inserted
            $grouped[$row['week']] = [$row];
        }
    }

    return $grouped;
}

public function deleteBooking($bookingId, $userId, $isAdmin) {
    if ($isAdmin) {
        // Admin can delete ANY booking
        $stmt = $this->connection->prepare("
            DELETE FROM bookings
            WHERE id = :id
        ");
        $stmt->bindParam(':id', $bookingId);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // Normal user: can delete only their own booking
    $stmt = $this->connection->prepare("
        DELETE FROM bookings
        WHERE id = :id AND user_id = :user_id
    ");
    $stmt->bindParam(':id', $bookingId);
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    
    return $stmt->rowCount() > 0;
}


public function deleteBookingsInRange($startDate, $endDate) {

    // Fetch affected bookings before deleting
    $stmt = $this->connection->prepare("
        SELECT b.id, b.user_id, b.date, u.name, u.surname, u.username AS email
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        WHERE b.date BETWEEN :start AND :end
          AND b.user_id != 0
    ");
    $stmt->execute([
        ':start' => $startDate,
        ':end' => $endDate
    ]);

    $affected = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Delete them
    $deleteStmt = $this->connection->prepare("
        DELETE FROM bookings
        WHERE date BETWEEN :start AND :end
    ");
    $deleteStmt->execute([
        ':start' => $startDate,
        ':end' => $endDate
    ]);

    return $affected; // return list of users to email
}


public function blockDateRange($startDate, $endDate) {

    // Valid SERVICE ID
    $serviceId = $this->connection
        ->query("SELECT id FROM services LIMIT 1")
        ->fetchColumn();

    if (!$serviceId) {
        throw new Exception("No services found — cannot block dates.");
    }

    // VALID SYSTEM USER ID
    $systemUserId = $this->connection
        ->query("SELECT id FROM users WHERE username='systemblocker@gmail.com' LIMIT 1")
        ->fetchColumn();

    if (!$systemUserId) {
        throw new Exception("System user not found. Create a user with username 'systemblocker'.");
    }

    // GET ALL INSTRUCTORS
    $instructors = $this->connection
        ->query("SELECT id FROM users WHERE role='instructor'")
        ->fetchAll(PDO::FETCH_COLUMN);

    if (!$instructors) {
        throw new Exception("No instructors found — cannot block dates.");
    }

    // DATE RANGE LOOP
    $period = new DatePeriod(
        new DateTime($startDate),
        new DateInterval('P1D'),
        (new DateTime($endDate))->modify('+1 day')
    );

    foreach ($period as $dateObj) {
        $date = $dateObj->format('Y-m-d');

        foreach ($instructors as $instructorId) {

            $stmt = $this->connection->prepare("
                INSERT INTO bookings (
                    user_id,
                    instructor_id,
                    service_id,
                    session_type,
                    date,
                    start_time,
                    num_of_hours,
                    status
                ) VALUES (
                    :user_id,
                    :instructor_id,
                    :service_id,
                    'Private_instruction',
                    :date,
                    '00:00:00',
                    24,
                    'cancelled'
                )
            ");

            $stmt->execute([
                ':user_id'       => $systemUserId,
                ':instructor_id' => $instructorId,
                ':service_id'    => $serviceId,
                ':date'          => $date
            ]);
        }
    }
}

public function getBookingById($id) {
    $stmt = $this->connection->prepare("
        SELECT 
            b.*,

            -- Client information
            u1.name AS client_first_name,
            u1.surname AS client_last_name,
            CONCAT(u1.name, ' ', u1.surname) AS client_name,
            u1.username AS client_email,

            -- Instructor information
            u2.name AS instructor_first_name,
            u2.surname AS instructor_last_name,
            CONCAT(u2.name, ' ', u2.surname) AS instructor_name,
            u2.username AS instructor_email

        FROM bookings b
        JOIN users u1 ON b.user_id = u1.id
        LEFT JOIN users u2 ON b.instructor_id = u2.id

        WHERE b.id = :id
        LIMIT 1
    ");

    $stmt->execute([':id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

}
?>
