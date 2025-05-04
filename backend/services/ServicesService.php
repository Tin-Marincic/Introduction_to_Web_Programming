<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/ServicesDao.php';

class ServicesService extends BaseService {

    public function __construct() {
        $this->dao = new ServicesDao();
        parent::__construct($this->dao);
    }


}
?>
