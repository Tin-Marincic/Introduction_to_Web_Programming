<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/ReviewsDao.php';
require_once __DIR__ . '/../dao/BookingDao.php'; 

class ReviewService extends BaseService {

    private $bookingDao;

    public function __construct() {
        $this->dao = new ReviewsDao();
        $this->bookingDao = new BookingDao(); 
        parent::__construct($this->dao);
    }

    public function addReview($data) {
    error_log("Review payload: " . json_encode($data)); 

    if (!isset($data['user_id'], $data['grade'])) {
        throw new Exception("Missing required fields: user_id or grade.");
    }

    if (!is_numeric($data['user_id']) || !is_numeric($data['grade'])) {
        throw new Exception("User ID and grade must be numeric.");
    }

    if ($data['grade'] < 1 || $data['grade'] > 5) {
        throw new Exception("Grade must be between 1 and 5.");
    }

    if (isset($data['booking_id']) && !is_numeric($data['booking_id'])) {
        throw new Exception("Booking ID must be numeric if provided.");
    }

    if (empty($data['booking_id'])) {
        if (!$this->bookingDao->userHasBooking($data['user_id'])) {
            throw new Exception("User must have at least one booking to leave a review.");
        }
    }

    try {
        return $this->dao->insert([
            'user_id' => $data['user_id'],
            'booking_id' => $data['booking_id'] ?? null,
            'grade' => $data['grade'],
            'note' => $data['note'] ?? null
        ]);
    } catch (Exception $e) {
        error_log("Review insert error: " . $e->getMessage());
        throw new Exception("Failed to insert review: " . $e->getMessage());
    }
}


    public function getReviewsByUser($userId) {
        if (!is_numeric($userId)) {
            throw new Exception("User ID must be numeric.");
        }
        return $this->dao->getReviewsByUser($userId);
    }

    public function getAllReviewsWithUserNames() {
        return $this->dao->getAllWithUserNames();
    }
}

