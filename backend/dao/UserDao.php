<?php
require_once 'BaseDao.php';

class UserDao extends BaseDao {
    public function __construct() {
        parent::__construct("users"); 
    }

    // Add instructor (custom logic: includes `licence` and fixed role)
    public function addInstructor(
        $name,
        $surname,
        $licence,
        $username,
        $password,
        $role = 'instructor'
    ) {
        return $this->insert([
            'name'           => $name,
            'surname'        => $surname,
            'licence'        => $licence,
            'username'       => $username,
            'password'       => $password,
            'role'           => $role,
            'email_verified' => 1,                      // 👈 admin-added instructors are verified
            'created_at'     => date('Y-m-d H:i:s')
        ]);
    }



    // Update instructor (only allows if role is instructor) uses dynamic set clause 
    public function updateInstructor($userId, $data) {
        $stmt = $this->connection->prepare(
            "UPDATE users SET " . implode(', ', array_map(fn($k) => "$k = :$k", array_keys($data))) . 
            " WHERE id = :id AND role = 'instructor'"
        );
        $data['id'] = $userId;
        foreach ($data as $key => $value) {
            $stmt->bindParam(":$key", $data[$key]);
        }
        return $stmt->execute();
    }

    // Delete only instructor
    public function deleteInstructor($userId) {
        $stmt = $this->connection->prepare("DELETE FROM users WHERE id = :id AND role = 'instructor'");
        $stmt->bindParam(':id', $userId);
        return $stmt->execute();
    }

    // Get all instructors
    public function getAllInstructors() {
        $stmt = $this->connection->prepare("SELECT * FROM users WHERE role = 'instructor'");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Get users by role
    public function getUsersByRole($role) {
        $stmt = $this->connection->prepare("SELECT * FROM users WHERE role = :role");
        $stmt->bindParam(':role', $role);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function get_user_by_email($email) {
        $query = "SELECT * FROM users WHERE username = :username";
        return $this->query_unique($query, ['username' => $email]);
    }

    public function updateInstructorImage($id, $filename) {
        $stmt = $this->connection->prepare("
            UPDATE users SET image_url = :img WHERE id = :id
        ");
        $stmt->execute([
            ':img' => $filename,
            ':id' => $id
        ]);
}


}
