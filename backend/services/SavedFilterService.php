<?php
require_once __DIR__ . '/../dao/SavedFilterDao.php';
require_once __DIR__ . '/UserService.php';
require_once __DIR__ . '/CityService.php';

class SavedFilterService {
    private $savedFilterDao;
    private $userService;
    private $cityService;

    public function __construct() {
        $this->savedFilterDao = new SavedFilterDao();
        $this->userService = new UserService();
        $this->cityService = new CityService();
    }

    public function getAllFilters() {
        return $this->savedFilterDao->getAll();
    }

    public function getFilterById($id) {
        if (!is_numeric($id) || $id <= 0) {
            throw new ValidationException("Invalid filter ID");
        }
        $filter = $this->savedFilterDao->getById($id);
        if (!$filter) {
            throw new NotFoundException("Filter not found");
        }
        return $filter;
    }

    public function getFiltersByUserId($userId) {
        if (!is_numeric($userId) || $userId <= 0) {
            throw new ValidationException("Invalid user ID");
        }
        return $this->savedFilterDao->getByUserId($userId);
    }

    public function getFilterByUserAndCity($userId, $cityId) {
        if (!is_numeric($userId) || $userId <= 0) {
            throw new ValidationException("Invalid user ID");
        }
        if (!is_numeric($cityId) || $cityId <= 0) {
            throw new ValidationException("Invalid city ID");
        }
        return $this->savedFilterDao->getByUserAndCity($userId, $cityId);
    }

    public function createFilter($data) {
        $this->validateFilterData($data, true);

        $user = $this->userService->getUserById($data['user_id']);
        if (!$user) {
            throw new NotFoundException("User not found");
        }

        $city = $this->cityService->getCityById($data['city_id']);
        if (!$city) {
            throw new NotFoundException("City not found");
        }

        $existingFilter = $this->savedFilterDao->getByUserAndCity($data['user_id'], $data['city_id']);
        if ($existingFilter) {
            throw new ValidationException("Filter already exists for this user and city");
        }

        return $this->savedFilterDao->createFilter(
            $data['user_id'],
            $data['city_id'],
            $data['forecast_days'] ?? 1,
            $data['min_temp_selected'] ?? null,
            $data['max_temp_selected'] ?? null,
            $data['avg_temp_selected'] ?? null,
            $data['weather_cond_selected'] ?? null
        );
    }

    public function updateFilter($id, $data) {
        if (!is_numeric($id) || $id <= 0) {
            throw new ValidationException("Invalid filter ID");
        }

        $filter = $this->savedFilterDao->getById($id);
        if (!$filter) {
            throw new NotFoundException("Filter not found");
        }

        $this->validateFilterData($data, false);

        if (isset($data['city_id'])) {
            $city = $this->cityService->getCityById($data['city_id']);
            if (!$city) {
                throw new NotFoundException("City not found");
            }

            if ($data['city_id'] != $filter['city_id']) {
                $existingFilter = $this->savedFilterDao->getByUserAndCity($filter['user_id'], $data['city_id']);
                if ($existingFilter && $existingFilter['id'] != $id) {
                    throw new ValidationException("Filter already exists for this user and city");
                }
            }
        }

        return $this->savedFilterDao->updateFilter(
            $id,
            $data['forecast_days'] ?? $filter['forecast_days'],
            $data['min_temp_selected'] ?? $filter['min_temp_selected'],
            $data['max_temp_selected'] ?? $filter['max_temp_selected'],
            $data['avg_temp_selected'] ?? $filter['avg_temp_selected'],
            $data['weather_cond_selected'] ?? $filter['weather_cond_selected']
        );
    }

    public function deleteFilter($id) {
        if (!is_numeric($id) || $id <= 0) {
            throw new ValidationException("Invalid filter ID");
        }

        $filter = $this->savedFilterDao->getById($id);
        if (!$filter) {
            throw new NotFoundException("Filter not found");
        }

        return $this->savedFilterDao->delete($id);
    }

    public function deleteFiltersByUserId($userId) {
        if (!is_numeric($userId) || $userId <= 0) {
            throw new ValidationException("Invalid user ID");
        }
        return $this->savedFilterDao->deleteByUserId($userId);
    }

    public function getFilterCountByUserId($userId) {
        if (!is_numeric($userId) || $userId <= 0) {
            throw new ValidationException("Invalid user ID");
        }
        return $this->savedFilterDao->countByUserId($userId);
    }

    private function validateFilterData($data, $isCreate) {
        if ($isCreate) {
            if (empty($data['user_id'])) {
                throw new ValidationException("User ID is required");
            }
            if (empty($data['city_id'])) {
                throw new ValidationException("City ID is required");
            }
        }

        if (isset($data['user_id']) && (!is_numeric($data['user_id']) || $data['user_id'] <= 0)) {
            throw new ValidationException("Invalid user ID");
        }

        if (isset($data['city_id']) && (!is_numeric($data['city_id']) || $data['city_id'] <= 0)) {
            throw new ValidationException("Invalid city ID");
        }

        if (isset($data['forecast_days']) && (!is_numeric($data['forecast_days']) || $data['forecast_days'] < 1 || $data['forecast_days'] > 14)) {
            throw new ValidationException("Forecast days must be between 1 and 14");
        }

        if (isset($data['min_temp_selected']) && !is_numeric($data['min_temp_selected'])) {
            throw new ValidationException("Min temperature must be a number");
        }

        if (isset($data['max_temp_selected']) && !is_numeric($data['max_temp_selected'])) {
            throw new ValidationException("Max temperature must be a number");
        }

        if (isset($data['avg_temp_selected']) && !is_numeric($data['avg_temp_selected'])) {
            throw new ValidationException("Average temperature must be a number");
        }

        if (isset($data['min_temp_selected']) && isset($data['max_temp_selected'])) {
            if ($data['min_temp_selected'] > $data['max_temp_selected']) {
                throw new ValidationException("Min temperature cannot be greater than max temperature");
            }
        }

        if (isset($data['weather_cond_selected']) && strlen($data['weather_cond_selected']) > 100) {
            throw new ValidationException("Weather condition must be 100 characters or less");
        }
    }
}
?>

