<?php
require_once __DIR__ . '/AuthMiddleware.php';

class AdminMiddleware {
    /**
     * Require admin role - must be called after AuthMiddleware
     */
    public static function requireAdmin() {
        if (!AuthMiddleware::isAdmin()) {
            Flight::halt(403, json_encode([
                'error' => true,
                'message' => 'Admin access required'
            ]));
            return false;
        }
        return true;
    }
}
?>

