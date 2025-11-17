<?php
require_once __DIR__ . '/../dao/PaymentDao.php';
require_once __DIR__ . '/SubscriptionService.php';

class PaymentService {
    private $paymentDao;
    private $subscriptionService;

    public function __construct() {
        $this->paymentDao = new PaymentDao();
        $this->subscriptionService = new SubscriptionService();
    }

    public function getAllPayments() {
        return $this->paymentDao->getAll();
    }

    public function getPaymentById($id) {
        if (!is_numeric($id) || $id <= 0) {
            throw new ValidationException("Invalid payment ID");
        }
        $payment = $this->paymentDao->getById($id);
        if (!$payment) {
            throw new NotFoundException("Payment not found");
        }
        return $payment;
    }

    public function getPaymentsBySubscriptionId($subscriptionId) {
        if (!is_numeric($subscriptionId) || $subscriptionId <= 0) {
            throw new ValidationException("Invalid subscription ID");
        }
        return $this->paymentDao->getBySubscriptionId($subscriptionId);
    }

    public function getPaymentsByUserId($userId) {
        if (!is_numeric($userId) || $userId <= 0) {
            throw new ValidationException("Invalid user ID");
        }
        return $this->paymentDao->getByUserId($userId);
    }

    public function createPayment($data) {
        $this->validatePaymentData($data, true);

        $subscription = $this->subscriptionService->getSubscriptionById($data['subscription_id']);
        if (!$subscription) {
            throw new NotFoundException("Subscription not found");
        }

        if (!empty($data['bank_transaction_id'])) {
            if ($this->paymentDao->paymentExists($data['bank_transaction_id'])) {
                throw new ValidationException("Payment with this transaction ID already exists");
            }
        }

        if (isset($data['card_number'])) {
            $cardNumber = $data['card_number'];
            if (strlen($cardNumber) > 4) {
                $data['card_number'] = '****' . substr($cardNumber, -4);
            }
        }

        return $this->paymentDao->createPayment(
            $data['subscription_id'],
            $data['payment_method'],
            $data['amount'],
            $data['card_number'] ?? null,
            $data['bank_transaction_id'] ?? null
        );
    }

    public function updatePayment($id, $data) {
        if (!is_numeric($id) || $id <= 0) {
            throw new ValidationException("Invalid payment ID");
        }

        $payment = $this->paymentDao->getById($id);
        if (!$payment) {
            throw new NotFoundException("Payment not found");
        }

        $this->validatePaymentData($data, false);

        if (isset($data['card_number'])) {
            $cardNumber = $data['card_number'];
            if (strlen($cardNumber) > 4) {
                $data['card_number'] = '****' . substr($cardNumber, -4);
            }
        }

        return $this->paymentDao->update($id, $data);
    }

    public function deletePayment($id) {
        if (!is_numeric($id) || $id <= 0) {
            throw new ValidationException("Invalid payment ID");
        }

        $payment = $this->paymentDao->getById($id);
        if (!$payment) {
            throw new NotFoundException("Payment not found");
        }

        return $this->paymentDao->delete($id);
    }

    public function getTotalAmountByUserId($userId) {
        if (!is_numeric($userId) || $userId <= 0) {
            throw new ValidationException("Invalid user ID");
        }
        return $this->paymentDao->getTotalAmountByUserId($userId);
    }

    public function getPaymentsByDateRange($startDate, $endDate) {
        if (empty($startDate) || empty($endDate)) {
            throw new ValidationException("Start date and end date are required");
        }

        if (!strtotime($startDate) || !strtotime($endDate)) {
            throw new ValidationException("Invalid date format");
        }

        if (strtotime($startDate) > strtotime($endDate)) {
            throw new ValidationException("Start date must be before end date");
        }

        return $this->paymentDao->getByDateRange($startDate, $endDate);
    }

    private function validatePaymentData($data, $isCreate) {
        if ($isCreate) {
            if (empty($data['subscription_id'])) {
                throw new ValidationException("Subscription ID is required");
            }
            if (empty($data['payment_method'])) {
                throw new ValidationException("Payment method is required");
            }
            if (!isset($data['amount']) || $data['amount'] <= 0) {
                throw new ValidationException("Valid payment amount is required");
            }
        }

        if (isset($data['subscription_id']) && (!is_numeric($data['subscription_id']) || $data['subscription_id'] <= 0)) {
            throw new ValidationException("Invalid subscription ID");
        }

        if (isset($data['payment_method'])) {
            $validMethods = ['credit_card', 'debit_card', 'bank_transfer', 'paypal'];
            if (!in_array($data['payment_method'], $validMethods)) {
                throw new ValidationException("Invalid payment method. Must be one of: " . implode(', ', $validMethods));
            }
        }

        if (isset($data['amount']) && ($data['amount'] < 0 || !is_numeric($data['amount']))) {
            throw new ValidationException("Amount must be a positive number");
        }

        if (isset($data['bank_transaction_id']) && strlen($data['bank_transaction_id']) > 100) {
            throw new ValidationException("Bank transaction ID must be 100 characters or less");
        }
    }
}
?>

