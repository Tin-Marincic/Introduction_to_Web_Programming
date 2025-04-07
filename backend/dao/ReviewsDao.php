<?php
require_once 'BaseDao.php';

class ReviewsDao extends BaseDao {
    public function __construct() {
        parent::__construct("reviews");
    }

    // Add a new review  USER
    public function addReview($userId, $bookingId, $grade, $note) {
        $stmt = $this->connection->prepare("INSERT INTO reviews (user_id, booking_id, grade, note) VALUES (:user_id, :booking_id, :grade, :note)");
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':booking_id', $bookingId);
        $stmt->bindParam(':grade', $grade);
        $stmt->bindParam(':note', $note);
        return $stmt->execute();
    }

    // Get reviews by user  VIDJET CU
    public function getReviewsByUser($userId) {
        $stmt = $this->connection->prepare("SELECT * FROM reviews WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Update a review  USER (trebao bih unutar 24h od bookinga)
    public function updateReview($reviewId, $grade, $note) {
        $stmt = $this->connection->prepare("UPDATE reviews SET grade = :grade, note = :note WHERE id = :id");
        $stmt->bindParam(':id', $reviewId);
        $stmt->bindParam(':grade', $grade);
        $stmt->bindParam(':note', $note);
        $stmt->execute();
        return $stmt->rowCount();
    }

    // Delete a review  USER (trebao bih unutar 24h od bookinga)
    public function deleteReview($reviewId) {
        $stmt = $this->connection->prepare("DELETE FROM reviews WHERE id = :id");
        $stmt->bindParam(':id', $reviewId);
        return $stmt->execute();
    }
}
?>
