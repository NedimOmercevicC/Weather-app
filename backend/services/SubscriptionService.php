<?php
require_once __DIR__ . '/../dao/SubscriptionDao.php';
require_once __DIR__ . '/UserService.php';

class SubscriptionService {
    private $subscriptionDao;
    private $userService;

    public function __construct() {
        $this->subscriptionDao = new SubscriptionDao();
        $this->userService = new UserService();
    }

    public function getAllSubscriptions() {
        return $this->subscriptionDao->getAll();
    }

    public function getSubscriptionById($id) {
        if (!is_numeric($id) || $id <= 0) {
            throw new ValidationException("Invalid subscription ID");
        }
        $subscription = $this->subscriptionDao->getById($id);
        if (!$subscription) {
            throw new NotFoundException("Subscription not found");
        }
        return $subscription;
    }

    public function getSubscriptionsByUserId($userId) {
        if (!is_numeric($userId) || $userId <= 0) {
            throw new ValidationException("Invalid user ID");
        }
        return $this->subscriptionDao->getByUserId($userId);
    }

    public function getActiveSubscriptionByUserId($userId) {
        if (!is_numeric($userId) || $userId <= 0) {
            throw new ValidationException("Invalid user ID");
        }
        return $this->subscriptionDao->getActiveByUserId($userId);
    }

    public function hasActiveSubscription($userId) {
        if (!is_numeric($userId) || $userId <= 0) {
            throw new ValidationException("Invalid user ID");
        }
        return $this->subscriptionDao->hasActiveSubscription($userId);
    }

    public function createSubscription($data) {
        $this->validateSubscriptionData($data, true);

        $user = $this->userService->getUserById($data['user_id']);
        if (!$user) {
            throw new NotFoundException("User not found");
        }

        $lastsUntil = $data['lasts_until'];
        if (strtotime($lastsUntil) <= time()) {
            throw new ValidationException("Subscription end date must be in the future");
        }

        return $this->subscriptionDao->createSubscription(
            $data['user_id'],
            $lastsUntil
        );
    }

    public function updateSubscription($id, $data) {
        if (!is_numeric($id) || $id <= 0) {
            throw new ValidationException("Invalid subscription ID");
        }

        $subscription = $this->subscriptionDao->getById($id);
        if (!$subscription) {
            throw new NotFoundException("Subscription not found");
        }

        $this->validateSubscriptionData($data, false);

        if (isset($data['lasts_until'])) {
            if (strtotime($data['lasts_until']) <= time()) {
                throw new ValidationException("Subscription end date must be in the future");
            }
            return $this->subscriptionDao->updateEndDate($id, $data['lasts_until']);
        }

        return $this->subscriptionDao->update($id, $data);
    }

    public function deleteSubscription($id) {
        if (!is_numeric($id) || $id <= 0) {
            throw new ValidationException("Invalid subscription ID");
        }

        $subscription = $this->subscriptionDao->getById($id);
        if (!$subscription) {
            throw new NotFoundException("Subscription not found");
        }

        return $this->subscriptionDao->delete($id);
    }

    public function getExpiredSubscriptions() {
        return $this->subscriptionDao->getExpiredSubscriptions();
    }

    public function getExpiringSoon() {
        return $this->subscriptionDao->getExpiringSoon();
    }

    private function validateSubscriptionData($data, $isCreate) {
        if ($isCreate) {
            if (empty($data['user_id'])) {
                throw new ValidationException("User ID is required");
            }
            if (empty($data['lasts_until'])) {
                throw new ValidationException("End date is required");
            }
        }

        if (isset($data['user_id']) && (!is_numeric($data['user_id']) || $data['user_id'] <= 0)) {
            throw new ValidationException("Invalid user ID");
        }

        if (isset($data['lasts_until'])) {
            if (!strtotime($data['lasts_until'])) {
                throw new ValidationException("Invalid date format for lasts_until");
            }
        }
    }
}
?>

