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


}
