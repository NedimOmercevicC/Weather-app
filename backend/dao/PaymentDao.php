<?php
require_once 'BaseDao.php';

class PaymentDao extends BaseDao {
    
    public function __construct() {
        parent::__construct('payments');
    }

    public function getBySubscriptionId($subscriptionId) {
        $stmt = $this->connection->prepare("SELECT * FROM payments WHERE subscription_id = :subscription_id ORDER BY id DESC");
        $stmt->bindParam(':subscription_id', $subscriptionId);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getByUserId($userId) {
        $stmt = $this->connection->prepare("SELECT p.* FROM payments p 
                                          JOIN subscriptions s ON p.subscription_id = s.id 
                                          WHERE s.user_id = :user_id ORDER BY p.id DESC");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function createPayment($subscriptionId, $paymentMethod, $amount, $cardNumber, $bankTransactionId) {
        $data = [
            'subscription_id' => $subscriptionId,
            'payment_method' => $paymentMethod,
            'amount' => $amount,
            'card_number' => $cardNumber,
            'bank_transaction_id' => $bankTransactionId
        ];
        return $this->insert($data);
    }

    public function getByPaymentMethod($paymentMethod) {
        $stmt = $this->connection->prepare("SELECT * FROM payments WHERE payment_method = :payment_method ORDER BY id DESC");
        $stmt->bindParam(':payment_method', $paymentMethod);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTotalAmountByUserId($userId) {
        $stmt = $this->connection->prepare("SELECT SUM(p.amount) as total FROM payments p 
                                          JOIN subscriptions s ON p.subscription_id = s.id 
                                          WHERE s.user_id = :user_id");
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function getByDateRange($startDate, $endDate) {
        $stmt = $this->connection->prepare("SELECT * FROM payments WHERE created_at BETWEEN :start_date AND :end_date ORDER BY created_at DESC");
        $stmt->bindParam(':start_date', $startDate);
        $stmt->bindParam(':end_date', $endDate);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function paymentExists($bankTransactionId) {
        $stmt = $this->connection->prepare("SELECT COUNT(*) as count FROM payments WHERE bank_transaction_id = :bank_transaction_id");
        $stmt->bindParam(':bank_transaction_id', $bankTransactionId);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
}
?>
