<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../services/AuthService.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware {
    /**
     * Verify JWT token and set authenticated user
     */
    public static function authenticate() {
        $token = null;

        // Get token from Authorization header
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        if (empty($headers)) {
            // Fallback for servers that don't support getallheaders()
            foreach ($_SERVER as $key => $value) {
                if (strpos($key, 'HTTP_') === 0) {
                    $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                    $headers[$header] = $value;
                }
            }
        }
        
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                $token = $matches[1];
            }
        }

        // Also check for token in request data (for POST requests)
        if (!$token && isset($_POST['token'])) {
            $token = $_POST['token'];
        }

        // Check query parameter as fallback
        if (!$token && isset($_GET['token'])) {
            $token = $_GET['token'];
        }

        if (!$token) {
            Flight::halt(401, json_encode([
                'error' => true,
                'message' => 'Authentication token required'
            ]));
            return false;
        }

        try {
            // Decode and verify token
            $decoded = JWT::decode($token, new Key(Config::JWT_SECRET(), 'HS256'));
            
            // Store user info in Flight for use in routes
            Flight::set('user_id', $decoded->user_id);
            Flight::set('user_email', $decoded->email);
            Flight::set('is_admin', $decoded->is_admin ?? false);
            Flight::set('user_token', $decoded);
            
            return true;
        } catch (Exception $e) {
            Flight::halt(401, json_encode([
                'error' => true,
                'message' => 'Invalid or expired token: ' . $e->getMessage()
            ]));
            return false;
        }
    }

    /**
     * Get current authenticated user ID
     */
    public static function getUserId() {
        return Flight::get('user_id');
    }

    /**
     * Get current authenticated user email
     */
    public static function getUserEmail() {
        return Flight::get('user_email');
    }

    /**
     * Check if current user is admin
     */
    public static function isAdmin() {
        return Flight::get('is_admin') === true;
    }
}
?>

