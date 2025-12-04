<?php
require_once 'BaseService.php';
require_once __DIR__ . '/../dao/AuthDao.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthService extends BaseService {

    private $auth_dao;

    public function __construct() {
        $this->auth_dao = new AuthDao();
        parent::__construct(new AuthDao);
    }

    public function get_user_by_username($username) {
        return $this->auth_dao->get_user_by_email($username);
    }

    /* ============================================================
       REGISTER USER + SEND EMAIL VERIFICATION
    ============================================================ */
    public function register($data) {

        if (
            empty($data['username']) ||
            empty($data['password']) ||
            empty($data['name']) ||
            empty($data['surname']) ||
            empty($data['phone'])
        ) {
            return ['success' => false, 'error' => 'First name, last name, email, and password are required.'];
        }

        $data['role'] = 'user';
        $data['email_verified'] = 0;       // NEW
        $data['verification_token'] = null;

        // Check duplicate email
        if ($this->auth_dao->get_user_by_email($data['username'])) {
            return ['success' => false, 'error' => 'Email is already in use.'];
        }

        // Check duplicate phone
        if ($this->auth_dao->get_user_by_phone($data['phone'])) {
            return ['success' => false, 'error' => 'Phone number is already in use.'];
        }

        // Hash password
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

        // Insert user
        $created = $this->auth_dao->insert($data);
        if (!$created) {
            return ['success' => false, 'error' => 'Failed to register user.'];
        }

        // Fetch saved user
        $user = $this->auth_dao->get_user_by_email($data['username']);
        unset($user['password']);

        // Create verification token
        $token = bin2hex(random_bytes(32));
        $this->auth_dao->setVerificationToken($data['username'], $token);

        // Send verification email
        require_once __DIR__ . '/../forms/emailUtil.php';
        EmailUtil::sendEmailVerification($data['username'], $data['name'], $token);

        return [
            'success' => true,
            'data' => $user,
            'message' => 'Registracija uspješna! Molimo vas da provjerite email i verifikujete svoj račun.'
        ];
    }

    /* ============================================================
       LOGIN — BLOCK IF EMAIL NOT VERIFIED
    ============================================================ */
    public function login($entity) {

        if (empty($entity['username']) || empty($entity['password'])) {
            return ['success' => false, 'error' => 'Email and password are required.'];
        }

        $user = $this->auth_dao->get_user_by_email($entity['username']);
        if (!$user) {
            return ['success' => false, 'error' => 'Invalid username or password.'];
        }

        // BLOCK LOGIN UNTIL VERIFIED
        if ((int)$user['email_verified'] === 0) {
            return [
                'success' => false,
                'error' => 'Molimo verifikujte email prije prijave.'
            ];
        }

        // Validate password
        if (!password_verify($entity['password'], $user['password'])) {
            return ['success' => false, 'error' => 'Invalid username or password.'];
        }

        unset($user['password']);

        // Create JWT
        $jwt_payload = [
            'user' => $user,
            'iat'  => time(),
            'exp'  => time() + (60 * 60 * 24) // valid 1 day
        ];

        $token = JWT::encode(
            $jwt_payload,
            Config::JWT_SECRET(),
            'HS256'
        );

        return [
            'success' => true,
            'data' => array_merge($user, ['token' => $token])
        ];
    }

    /* ============================================================
       FORGOT PASSWORD
    ============================================================ */
    public function forgotPassword($email) {
        $user = $this->auth_dao->get_user_by_email($email);

        if (!$user) {
            return ['success' => false, 'error' => 'Korisnik sa datim emailom ne postoji.'];
        }

        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", time() + 3600);

        $this->auth_dao->setResetToken($email, $token, $expires);

        require_once __DIR__ . '/../forms/emailUtil.php';
        EmailUtil::sendPasswordResetEmail($email, $user['name'], $token);

        return ['success' => true];
    }

    /* ============================================================
       RESET PASSWORD
    ============================================================ */
    public function resetPassword($token, $newPassword) {
        $user = $this->auth_dao->getUserByResetToken($token);

        if (!$user) {
            return ['success' => false, 'error' => 'Nevažeći ili istekao token.'];
        }

        $hashed = password_hash($newPassword, PASSWORD_BCRYPT);
        $this->auth_dao->updatePassword($user['id'], $hashed);

        return ['success' => true];
    }

    /* ============================================================
       VERIFY EMAIL (NEW)
    ============================================================ */
    public function verifyEmail($token) {

        $user = $this->auth_dao->getUserByVerificationToken($token);

        if (!$user) {
            return ['success' => false, 'error' => 'Nevažeći ili istekao verifikacijski link.'];
        }

        $updated = $this->auth_dao->verifyUserByToken($token);

        return [
            'success' => $updated,
            'message' => 'Email uspješno verifikovan!'
        ];
    }

}
