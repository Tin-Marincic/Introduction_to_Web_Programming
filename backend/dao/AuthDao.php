<?php
require_once __DIR__ . '/BaseDao.php';


class AuthDao extends BaseDao {
   protected $table_name;


   public function __construct() {
       $this->table_name = "users";
       parent::__construct($this->table_name);
   }


   public function get_user_by_email($username) {
       $query = "SELECT * FROM " . $this->table_name . " WHERE username = :username";
       return $this->query_unique($query, ['username' => $username]);
   }

    public function get_user_by_phone($phone) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE phone = :phone";
        return $this->query_unique($query, ['phone' => $phone]);
    }

    public function setResetToken($email, $token, $expires) {
    $stmt = $this->connection->prepare("
        UPDATE users 
        SET reset_token = :token, reset_expires = :expires 
        WHERE username = :email
    ");
    $stmt->execute([
        ':token' => $token,
        ':expires' => $expires,
        ':email' => $email
    ]);
}

public function getUserByResetToken($token) {
    $stmt = $this->connection->prepare("
        SELECT * FROM users 
        WHERE reset_token = :token 
        AND reset_expires > NOW()
    ");
    $stmt->execute([':token' => $token]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

public function updatePassword($userId, $hashedPassword) {
    $stmt = $this->connection->prepare("
        UPDATE users 
        SET password = :password, reset_token = NULL, reset_expires = NULL 
        WHERE id = :id
    ");
    $stmt->execute([
        ':password' => $hashedPassword,
        ':id' => $userId
    ]);
}



}
