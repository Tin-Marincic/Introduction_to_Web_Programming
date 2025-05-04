<?php
require_once 'BaseDao.php';

class ServicesDao extends BaseDao {
    public function __construct() {
        parent::__construct("services");
    }

    // Add a new service  ADMIN
    // Update an existing service ADMIN
    // Delete a service  ADMIN
    // Get all services  ADMIN AND USER

}
?>
