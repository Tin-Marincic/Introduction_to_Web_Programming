<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/UserDao.php';

class UserService extends BaseService {

    public function __construct() {
        $this->dao = new UserDao();
        parent::__construct($this->dao);
    }

    // validate role values
    private function validateRole($role) {
        $valid_roles = ['instructor', 'user', 'admin'];
        if (!in_array($role, $valid_roles)) {
            throw new Exception("Invalid role: $role. Allowed roles: instructor, user, admin.");
        }
    }

    // Admin: Add new instructor
// Admin: Add new instructor
    public function addInstructor($data) {

        $this->validateRole($data['role']);

        if (
            empty($data['name']) ||
            empty($data['surname']) ||
            empty($data['username']) ||
            empty($data['password']) ||
            empty($data['licence'])
        ) {
            throw new Exception("Missing required fields: name, surname, licence, email, or password.");
        }

        if ($this->dao->get_user_by_email($data['username'])) {
            throw new Exception("Username/email is already in use.");
        }

        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        // Insert instructor (email_verified MUST be 1)
        $this->dao->addInstructor(
            $data['name'],
            $data['surname'],
            $data['licence'],
            $data['username'],
            $hashedPassword,
            $data['role'],
            true   // 👈 pass email_verified = 1
        );

        // Fetch created user
        $user = $this->dao->get_user_by_email($data['username']);
        if (!$user || !isset($user['id'])) {
            throw new Exception("Failed to fetch newly created instructor.");
        }

        return (int)$user['id'];
    }



    // Admin: Update instructor
    public function updateInstructor($id, $data) {
        return $this->dao->updateInstructor($id, $data);
    }

    // Admin: Delete instructor
    public function deleteInstructor($id) {
        return $this->dao->deleteInstructor($id);
    }

    // Public: Get all instructors
    public function getAllInstructors() {
        return $this->dao->getAllInstructors();
    }


    // Role-based screen rendering
    public function getUsersByRole($role) {
        $this->validateRole($role);
        return $this->dao->getUsersByRole($role);
    }

    public function updateInstructorImage($id, $filenameOrUrl) {
        return $this->dao->updateInstructorImage($id, $filenameOrUrl);
    }



}
