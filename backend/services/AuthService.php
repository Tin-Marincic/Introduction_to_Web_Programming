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


   public function get_user_by_username($username){
       return $this->auth_dao->get_user_by_email($username);
   }


public function register($data) {
    // Validate required fields
    if (
        empty($data['username']) ||
        empty($data['password']) ||
        empty($data['name']) ||
        empty($data['surname'])
    ) {
        return ['success' => false, 'error' => 'First name, last name, email, and password are required.'];
    }

    // Only normal users can self-register
    $data['role'] = 'user';

    // Check if the email is already taken
    if ($this->auth_dao->get_user_by_email($data['username'])) {
        return ['success' => false, 'error' => 'Email is already in use.'];
    }

    // Hash the password before storing
    $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

    // Insert the new user into the database
    $created = $this->auth_dao->insert($data);
    if (!$created) {
        return ['success' => false, 'error' => 'Failed to register user.'];
    }

    // Retrieve the newly created user (without password)
    $user = $this->auth_dao->get_user_by_email($data['username']);
    unset($user['password']);

    return ['success' => true, 'data' => $user];
}



   public function login($entity) {  
       if (empty($entity['username']) || empty($entity['password'])) {
           return ['success' => false, 'error' => 'Email and password are required.'];
       }


       $user = $this->auth_dao->get_user_by_email($entity['username']);
       if(!$user){
           return ['success' => false, 'error' => 'Invalid username or password.'];
       }


       if(!$user || !password_verify($entity['password'], $user['password']))
           return ['success' => false, 'error' => 'Invalid username or password.'];


       unset($user['password']);
      
       $jwt_payload = [
           'user' => $user,
           'iat' => time(),
           'exp' => time() + (60 * 60 * 24) // valid for day
       ];


       $token = JWT::encode(
           $jwt_payload,
           Config::JWT_SECRET(),
           'HS256'
       );


       return ['success' => true, 'data' => array_merge($user, ['token' => $token])];             
   }
}
