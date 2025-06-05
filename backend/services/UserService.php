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
    public function addInstructor($data) {
        $this->validateRole($data['role']);

        // ✅ Hash the password before passing it to the DAO
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        return $this->dao->addInstructor(
            $data['name'],
            $data['surname'],
            $data['licence'],
            $data['username'],
            $hashedPassword,
            $data['role']
        );
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

    // Public: Register normal user
    public function registerUser($data) {
        $this->validateRole($data['role']);
        return $this->dao->registerUser(
            $data['name'],
            $data['surname'],
            $data['username'],
            $data['password'],
            $data['role']
        );
    }

    // Role-based screen rendering
    public function getUsersByRole($role) {
        $this->validateRole($role);
        return $this->dao->getUsersByRole($role);
    }
}
