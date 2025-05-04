<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/ReviewsDao.php';

class ReviewService extends BaseService {

    public function __construct() {
        $this->dao = new ReviewsDao();
        parent::__construct($this->dao);
    }

    public function addReview($data) {
        if (!isset($data['user_id'], $data['booking_id'], $data['grade'])) {
            throw new Exception("Missing required fields: user_id, booking_id, or grade.");
        }

        if (!is_numeric($data['user_id']) || !is_numeric($data['booking_id']) || !is_numeric($data['grade'])) {
            throw new Exception("User ID, booking ID, and grade must be numeric.");
        }

        if ($data['grade'] < 1 || $data['grade'] > 5) {
            throw new Exception("Grade must be between 1 and 5.");
        }

        return $this->dao->insert([
            'user_id' => $data['user_id'],
            'booking_id' => $data['booking_id'],
            'grade' => $data['grade'],
            'note' => $data['note'] ?? null
        ]);
    }

    public function getReviewsByUser($userId) {
        if (!is_numeric($userId)) {
            throw new Exception("User ID must be numeric.");
        }
        return $this->dao->getReviewsByUser($userId);
    }
}

