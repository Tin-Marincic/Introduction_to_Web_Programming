<?php
require_once 'BaseDao.php';

class UserDao extends BaseDao {
    public function __construct() {
        parent::__construct("users"); 
    }

    // Add a new instructor ADMIN
    public function addInstructor($name, $surname, $licence, $username, $password, $role = 'instructor') {
        $stmt = $this->connection->prepare("INSERT INTO users (name, surname, licence, username, password, role, created_at) VALUES (:name, :surname, :licence, :username, :password, :role, NOW())");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':surname', $surname);
        $stmt->bindParam(':licence', $licence);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password); // Consider using password_hash() in real scenarios 
        $stmt->bindParam(':role', $role);
        return $stmt->execute();
    }

    // Update an existing instructor  ADMIN
    public function updateInstructor($userId, $data) {
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
        }
        $fields = implode(', ', $fields);
        $sql = "UPDATE users SET $fields WHERE id = :id AND role = 'instructor'";
        $stmt = $this->connection->prepare($sql);
        $data['id'] = $userId;
        foreach ($data as $key => $value) {
            $stmt->bindParam(":$key", $data[$key]);
        }
        return $stmt->execute();
    }

    // Delete an instructor  ADMIN
    public function deleteInstructor($userId) {
        $stmt = $this->connection->prepare("DELETE FROM users WHERE id = :id AND role = 'instructor'");
        $stmt->bindParam(':id', $userId);
        return $stmt->execute();
    }

    // Get all instructors  ADMIN AND TEAM SECTION IN HTML FOR USERS TO SEE
    public function getAllInstructors() {
        $stmt = $this->connection->prepare("SELECT * FROM users WHERE role = 'instructor'");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Get user by email  (i need this for login i think)
    public function getByEmail($email) {
        $stmt = $this->connection->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Get users by role (i need this to see which screens to show that user since different roles have different access to sites on website)
    public function getUsersByRole($role) {
        $stmt = $this->connection->prepare("SELECT * FROM users WHERE role = :role");
        $stmt->bindParam(':role', $role);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Register a new user (normal user)
public function registerUser($name, $surname, $username, $password, $role = 'user') {
    $stmt = $this->connection->prepare("INSERT INTO users (name, surname, username, password, role, created_at) VALUES (:name, :surname, :username, :password, :role, NOW())");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':surname', $surname);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $password); // Remember to hash passwords in production!
    $stmt->bindParam(':role', $role);
    return $stmt->execute();
}

}   
?>
