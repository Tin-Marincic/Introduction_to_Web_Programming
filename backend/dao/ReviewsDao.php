<?php
require_once 'BaseDao.php';

class ReviewsDao extends BaseDao {
    public function __construct() {
        parent::__construct("reviews");
    }
//dont use this one but could be useful in the future
    public function getReviewsByUser($userId) {
        $stmt = $this->connection->prepare("SELECT * FROM reviews WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll();
    }
//used in reviews page where i populate the page with all reviews
    public function getAllWithUserNames() {
    $stmt = $this->connection->prepare("
        SELECT r.*, CONCAT(u.name, ' ', u.surname) AS user_full_name
        FROM reviews r
        JOIN users u ON r.user_id = u.id
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

}
