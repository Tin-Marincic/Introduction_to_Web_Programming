<?php
require_once 'BaseDao.php';

class ServicesDao extends BaseDao {
    public function __construct() {
        parent::__construct("services");
    }

    // Add a new service  ADMIN
    public function addService($name, $description, $price, $validFrom, $validTo) {
        $stmt = $this->connection->prepare("INSERT INTO services (name, description, price, valid_from, valid_to) VALUES (:name, :description, :price, :valid_from, :valid_to)");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':valid_from', $validFrom);
        $stmt->bindParam(':valid_to', $validTo);
        return $stmt->execute();
    }

    // Update an existing service ADMIN
    public function updateService($serviceId, $data) {
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
        }
        $fields = implode(', ', $fields);
        $sql = "UPDATE services SET $fields WHERE id = :id";
        $stmt = $this->connection->prepare($sql);
        $data['id'] = $serviceId; 
        foreach ($data as $key => $value) {
            $stmt->bindParam(":$key", $data[$key]);
        }
        return $stmt->execute();
    }

    // Delete a service  ADMIN
    public function deleteService($serviceId) {
        $stmt = $this->connection->prepare("DELETE FROM services WHERE id = :id");
        $stmt->bindParam(':id', $serviceId);
        return $stmt->execute();
    }

    // Get all services  ADMIN AND USER
    public function getAllServices() {
        $stmt = $this->connection->prepare("SELECT * FROM services");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>
