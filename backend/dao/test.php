<?php
// test.dao

// Include all DAO files
require_once 'UserDao.php';
require_once 'ServicesDao.php';
require_once 'ReviewsDao.php';
require_once 'BookingDao.php';
require_once 'AvailabilityCalendarDao.php';

echo "<pre>";
echo " DAO Tests\n";


echo "----- Testing UserDao -----\n";

$userDao = new UserDao();

// addInstructor
echo "\n[UserDao] Testing addInstructor...\n";
$instructorName = "TestInstructor";
$instructorSurname = "Doe";
$licence = "U1";
$username = "inst_" . rand(1000, 9999);
$password = password_hash("secret", PASSWORD_DEFAULT);
$addInstructorResult = $userDao->addInstructor($instructorName, $instructorSurname, $licence, $username, $password);
echo $addInstructorResult ? "addInstructor: SUCCESS\n" : "addInstructor: FAIL\n";

// getAllInstructors
echo "\n[UserDao] Testing getAllInstructors...\n";
$instructors = $userDao->getAllInstructors();
print_r($instructors);

// updateInstructor
if (!empty($instructors)) {
    $lastInstructor = end($instructors);
    $instructorId = $lastInstructor['id'];
    echo "\n[UserDao] Testing updateInstructor for ID: $instructorId...\n";
    $updateData = ['name' => 'UpdatedInstructor'];
    $updateInstructorResult = $userDao->updateInstructor($instructorId, $updateData);
    echo $updateInstructorResult ? "updateInstructor: SUCCESS\n" : "updateInstructor: FAIL\n";
}

// registerUser (normal user)
echo "\n[UserDao] Testing registerUser...\n";
$username2 = "user_" . rand(1000, 9999);
$registerResult = $userDao->registerUser("Regular", "User", $username2, password_hash("pass123", PASSWORD_DEFAULT));
echo $registerResult ? "registerUser: SUCCESS\n" : "registerUser: FAIL\n";

// getUsersByRole (for 'user')
echo "\n[UserDao] Testing getUsersByRole for role 'user'...\n";
$usersByRole = $userDao->getUsersByRole('user');
print_r($usersByRole);

// deleteInstructor
if (isset($instructorId)) {
    echo "\n[UserDao] Testing deleteInstructor for ID: $instructorId...\n";
    $deleteInstructorResult = $userDao->deleteInstructor($instructorId);
    echo $deleteInstructorResult ? "deleteInstructor: SUCCESS\n" : "deleteInstructor: FAIL\n";
}


echo "\n----- Testing ServicesDao -----\n";

$servicesDao = new ServicesDao();

// addService
echo "\n[ServicesDao] Testing addService...\n";
$serviceName = "Test Service";
$serviceDescription = "Service description test";
$servicePrice = 100.00;
$validFrom = date('Y-m-d', strtotime("+1 day")); 
$validTo = date('Y-m-d', strtotime("+30 days"));
$addServiceResult = $servicesDao->addService($serviceName, $serviceDescription, $servicePrice, $validFrom, $validTo);
echo $addServiceResult ? "addService: SUCCESS\n" : "addService: FAIL\n";

// getAllServices
echo "\n[ServicesDao] Testing getAllServices...\n";
$allServices = $servicesDao->getAllServices();
print_r($allServices);

// updateService
if (!empty($allServices)) {
    $lastService = end($allServices);
    $serviceId = $lastService['id'];
    echo "\n[ServicesDao] Testing updateService for Service ID: $serviceId...\n";
    $updateDataService = ['name' => 'Updated Test Service', 'price' => 120.00];
    $updateServiceResult = $servicesDao->updateService($serviceId, $updateDataService);
    echo $updateServiceResult ? "updateService: SUCCESS\n" : "updateService: FAIL\n";
}

// deleteService
if (isset($serviceId)) {
    echo "\n[ServicesDao] Testing deleteService for Service ID: $serviceId...\n";
    $deleteServiceResult = $servicesDao->deleteService($serviceId);
    echo $deleteServiceResult ? "deleteService: SUCCESS\n" : "deleteService: FAIL\n";
}

echo "\n----- Testing ReviewsDao -----\n";

$reviewsDao = new ReviewsDao();

// addReview
echo "\n[ReviewsDao] Testing addReview...\n";
$reviewUserId = 1;      
$reviewBookingId = 1;   
$grade = 5;
$note = "Great service!";
$addReviewResult = $reviewsDao->addReview($reviewUserId, $reviewBookingId, $grade, $note);
echo $addReviewResult ? "addReview: SUCCESS\n" : "addReview: FAIL\n";

// getReviewsByUser
echo "\n[ReviewsDao] Testing getReviewsByUser for user_id = $reviewUserId...\n";
$userReviews = $reviewsDao->getReviewsByUser($reviewUserId);
print_r($userReviews);

// updateReview
if (!empty($userReviews)) {
    $lastReview = end($userReviews);
    $reviewId = $lastReview['id'];
    echo "\n[ReviewsDao] Testing updateReview for review ID: $reviewId...\n";
    $updateGrade = 4;
    $updateNote = "Updated note.";
    $updateReviewResult = $reviewsDao->updateReview($reviewId, $updateGrade, $updateNote);
    echo $updateReviewResult ? "updateReview: SUCCESS\n" : "updateReview: FAIL\n";
}

// deleteReview
if (isset($reviewId)) {
    echo "\n[ReviewsDao] Testing deleteReview for review ID: $reviewId...\n";
    $deleteReviewResult = $reviewsDao->deleteReview($reviewId);
    echo $deleteReviewResult ? "deleteReview: SUCCESS\n" : "deleteReview: FAIL\n";
}

echo "\n----- Testing BookingDao -----\n";

$bookingDao = new BookingDao();

// getDetailedUpcomingInstructorBookings
echo "\n[BookingDao] Testing getDetailedUpcomingInstructorBookings...\n";
$detailedBookings = $bookingDao->getDetailedUpcomingInstructorBookings();
print_r($detailedBookings);

// getSkiSchoolAvailability
echo "\n[BookingDao] Testing getSkiSchoolAvailability...\n";
$availabilityResults = $bookingDao->getSkiSchoolAvailability();
print_r($availabilityResults);

echo "\n[BookingDao] Testing getTotalHoursThisMonth for instructor_id = 5...\n";
$totalHours = $bookingDao->getTotalHoursThisMonth(5);
echo "Total hours: $totalHours\n";

echo "\n[BookingDao] Testing getUpcomingBookingsCount for instructor_id = 5...\n";
$upcomingBookingsCount = $bookingDao->getUpcomingBookingsCount(5);
echo "Upcoming bookings count: $upcomingBookingsCount\n";

echo "\n[BookingDao] Testing getDetailedUpcomingBookings for instructor_id = 5...\n";
$detailedUpcomingBookings = $bookingDao->getDetailedUpcomingBookings(5);
print_r($detailedUpcomingBookings);

echo "\n----- Testing AvailabilityCalendarDao -----\n";


$availabilityCalendarDao = new AvailabilityCalendarDao();

$currentDate = new DateTime();
$testDate = (new DateTime())->modify('+15 days')->format('Y-m-d');

echo "\n[AvailabilityCalendarDao] Testing addAvailability for instructor_id = 2 on $testDate...\n";
$availabilityStatus = "active";
$addAvailabilityResult = $availabilityCalendarDao->addAvailability(2, $testDate, $availabilityStatus);
echo $addAvailabilityResult ? "addAvailability: SUCCESS\n" : "addAvailability: FAIL\n";

// getAvailabilityByInstructor
echo "\n[AvailabilityCalendarDao] Testing getAvailabilityByInstructor for instructor_id = 5...\n";
$availabilityByInstructor = $availabilityCalendarDao->getAvailabilityByInstructor(5);
print_r($availabilityByInstructor);

// updateAvailability
if (!empty($availabilityByInstructor)) {
    $lastAvailability = end($availabilityByInstructor);
    $availabilityId = $lastAvailability['id'];
    $updateDate = (new DateTime($testDate))->modify('+1 day')->format('Y-m-d');
    $newStatus = "not_active";
    echo "\n[AvailabilityCalendarDao] Testing updateAvailability for availability ID: $availabilityId...\n";
    $updateAvailabilityResult = $availabilityCalendarDao->updateAvailability($availabilityId, $updateDate, $newStatus);
    echo $updateAvailabilityResult ? "updateAvailability: SUCCESS\n" : "updateAvailability: FAIL\n";
}

// deleteAvailability
if (isset($availabilityId)) {
    echo "\n[AvailabilityCalendarDao] Testing deleteAvailability for availability ID: $availabilityId...\n";
    $deleteAvailabilityResult = $availabilityCalendarDao->deleteAvailability($availabilityId);
    echo $deleteAvailabilityResult ? "deleteAvailability: SUCCESS\n" : "deleteAvailability: FAIL\n";
}

echo "</pre>";
?>
