<?php
require_once 'BaseDao.php';

class CityDao extends BaseDao {
    
    public function __construct() {
        parent::__construct('cities');
    }

    public function getByName($name) {
        $stmt = $this->connection->prepare("SELECT * FROM cities WHERE name = :name");
        $stmt->bindParam(':name', $name);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getByZipCode($zipCode) {
        $stmt = $this->connection->prepare("SELECT * FROM cities WHERE zip_code = :zip_code");
        $stmt->bindParam(':zip_code', $zipCode);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function searchByName($searchTerm) {
        $stmt = $this->connection->prepare("SELECT * FROM cities WHERE name LIKE :search ORDER BY name");
        $searchTerm = "%" . $searchTerm . "%";
        $stmt->bindParam(':search', $searchTerm);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getByCountry($country) {
        $stmt = $this->connection->prepare("SELECT * FROM cities WHERE country = :country ORDER BY name");
        $stmt->bindParam(':country', $country);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function createCity($name, $zipCode) {
        $data = [
            'name' => $name,
            'zip_code' => $zipCode
        ];
        return $this->insert($data);
    }

    public function cityExists($name, $zipCode) {
        $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM cities WHERE name = :name AND zip_code = :zip_code");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':zip_code', $zipCode);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
}
?>
