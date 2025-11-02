<?php
require_once 'BaseDao.php';

class SubscriptionDao extends BaseDao {
    
    public function __construct() {
        parent::__construct('subscriptions');
    }

    public function getByUserId($userId) {
        $stmt = $this->connection->prepare("SELECT * FROM subscriptions WHERE user_id = :user_id ORDER BY created_at DESC");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getActiveByUserId($userId) {
        $stmt = $this->connection->prepare("SELECT * FROM subscriptions WHERE user_id = :user_id AND lasts_until > NOW() ORDER BY created_at DESC LIMIT 1");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function createSubscription($userId, $lastsUntil) {
        $data = [
            'user_id' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
            'lasts_until' => $lastsUntil
        ];
        return $this->insert($data);
    }

    public function updateEndDate($id, $newEndDate) {
        $data = [
            'lasts_until' => $newEndDate
        ];
        return $this->update($id, $data);
    }

    public function hasActiveSubscription($userId) {
        $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM subscriptions WHERE user_id = :user_id AND lasts_until > NOW()");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    public function getExpiredSubscriptions() {
        $stmt = $this->connection->prepare("SELECT * FROM subscriptions WHERE lasts_until < NOW()");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getExpiringSoon() {
        $stmt = $this->connection->prepare("SELECT * FROM subscriptions WHERE lasts_until BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>
