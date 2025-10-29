<?php
require_once 'BaseDao.php';

class SavedFilterDao extends BaseDao {
    
    public function __construct() {
        parent::__construct('saved_filters');
    }

    public function getByUserId($userId) {
        $stmt = $this->connection->prepare("SELECT sf.*, c.name as city_name FROM saved_filters sf 
                                          JOIN cities c ON sf.city_id = c.id 
                                          WHERE sf.user_id = :user_id ORDER BY sf.id DESC");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getByUserAndCity($userId, $cityId) {
        $stmt = $this->connection->prepare("SELECT * FROM saved_filters WHERE user_id = :user_id AND city_id = :city_id");
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':city_id', $cityId);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function createFilter($userId, $cityId, $forecastDays, $minTemp, $maxTemp, $avgTemp, $weatherCond) {
        $data = [
            'user_id' => $userId,
            'city_id' => $cityId,
            'forecast_days' => $forecastDays,
            'min_temp_selected' => $minTemp,
            'max_temp_selected' => $maxTemp,
            'avg_temp_selected' => $avgTemp,
            'weather_cond_selected' => $weatherCond
        ];
        return $this->insert($data);
    }

    public function updateFilter($id, $forecastDays, $minTemp, $maxTemp, $avgTemp, $weatherCond) {
        $data = [
            'forecast_days' => $forecastDays,
            'min_temp_selected' => $minTemp,
            'max_temp_selected' => $maxTemp,
            'avg_temp_selected' => $avgTemp,
            'weather_cond_selected' => $weatherCond
        ];
        return $this->update($id, $data);
    }

    public function deleteByUserId($userId) {
        $stmt = $this->connection->prepare("DELETE FROM saved_filters WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $userId);
        return $stmt->execute();
    }

    public function countByUserId($userId) {
        $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM saved_filters WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'];
    }
}
?>
