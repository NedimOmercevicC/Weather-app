<?php
require_once __DIR__ . '/../dao/CityDao.php';

class CityService {
    private $cityDao;

    public function __construct() {
        $this->cityDao = new CityDao();
    }

    public function getAllCities() {
        return $this->cityDao->getAll();
    }

    public function getCityById($id) {
        if (!is_numeric($id) || $id <= 0) {
            throw new ValidationException("Invalid city ID");
        }
        $city = $this->cityDao->getById($id);
        if (!$city) {
            throw new NotFoundException("City not found");
        }
        return $city;
    }

    public function searchCities($searchTerm) {
        if (empty($searchTerm)) {
            throw new ValidationException("Search term cannot be empty");
        }
        if (strlen($searchTerm) < 2) {
            throw new ValidationException("Search term must be at least 2 characters");
        }
        return $this->cityDao->searchByName($searchTerm);
    }

    public function getCityByName($name) {
        if (empty($name)) {
            throw new ValidationException("City name is required");
        }
        return $this->cityDao->getByName($name);
    }

    public function getCityByZipCode($zipCode) {
        if (empty($zipCode)) {
            throw new ValidationException("Zip code is required");
        }
        return $this->cityDao->getByZipCode($zipCode);
    }

    public function createCity($data) {
        $this->validateCityData($data, true);

        if ($this->cityDao->cityExists($data['name'], $data['zip_code'] ?? null)) {
            throw new ValidationException("City with this name and zip code already exists");
        }

        return $this->cityDao->insert($data);
    }

    public function updateCity($id, $data) {
        if (!is_numeric($id) || $id <= 0) {
            throw new ValidationException("Invalid city ID");
        }

        $city = $this->cityDao->getById($id);
        if (!$city) {
            throw new NotFoundException("City not found");
        }

        $this->validateCityData($data, false);

        if ((isset($data['name']) && $data['name'] !== $city['name']) || 
            (isset($data['zip_code']) && $data['zip_code'] !== $city['zip_code'])) {
            $name = $data['name'] ?? $city['name'];
            $zipCode = $data['zip_code'] ?? $city['zip_code'];
            if ($this->cityDao->cityExists($name, $zipCode)) {
                throw new ValidationException("City with this name and zip code already exists");
            }
        }

        return $this->cityDao->update($id, $data);
    }

    public function deleteCity($id) {
        if (!is_numeric($id) || $id <= 0) {
            throw new ValidationException("Invalid city ID");
        }

        $city = $this->cityDao->getById($id);
        if (!$city) {
            throw new NotFoundException("City not found");
        }

        return $this->cityDao->delete($id);
    }

    private function validateCityData($data, $isCreate) {
        if ($isCreate) {
            if (empty($data['name'])) {
                throw new ValidationException("City name is required");
            }
        }

        if (isset($data['name']) && strlen($data['name']) > 100) {
            throw new ValidationException("City name must be 100 characters or less");
        }

        if (isset($data['zip_code']) && strlen($data['zip_code']) > 20) {
            throw new ValidationException("Zip code must be 20 characters or less");
        }
    }
}
?>

