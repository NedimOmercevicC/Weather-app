<?php
require_once __DIR__ . '/../dao/AuthDao.php';
require_once __DIR__ . '/../config.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthService {
    private $auth_dao;

    public function __construct() {
        $this->auth_dao = new AuthDao();
    }

    public function get_user_by_email($email) {
        return $this->auth_dao->get_user_by_email($email);
    }

    public function register($entity) {
        if (empty($entity['email']) || empty($entity['password'])) {
            return ['success' => false, 'error' => 'Email and password are required.'];
        }

        // Check if user already exists
        $existing_user = $this->get_user_by_email($entity['email']);
        if ($existing_user) {
            return ['success' => false, 'error' => 'Email already exists.'];
        }

        // Hash password
        $entity['pass'] = password_hash($entity['password'], PASSWORD_DEFAULT);
        unset($entity['password']);

        // Set default values
        if (!isset($entity['is_admin'])) {
            $entity['is_admin'] = false;
        }

        // Create user
        try {
            $user = $this->auth_dao->insert($entity);
            unset($user['pass']);
            return ['success' => true, 'data' => $user];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function login($data) {
        if (empty($data['email']) || empty($data['password'])) {
            return ['success' => false, 'error' => 'Email and password are required.'];
        }

        $user = $this->get_user_by_email($data['email']);
        if (!$user) {
            return ['success' => false, 'error' => 'Invalid login credentials.'];
        }

        if (!password_verify($data['password'], $user['pass'])) {
            return ['success' => false, 'error' => 'Invalid login credentials.'];
        }

        // Generate JWT token
        $payload = [
            'iss' => 'weather-app',
            'iat' => time(),
            'exp' => time() + (60 * 60 * 24), // 24 hours
            'user_id' => $user['id'],
            'email' => $user['email'],
            'is_admin' => $user['is_admin']
        ];

        $token = JWT::encode($payload, Config::JWT_SECRET(), 'HS256');

        // Remove password from user data
        unset($user['pass']);

        return [
            'success' => true,
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ];
    }
}
?>

