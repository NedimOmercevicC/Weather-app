<?php

class ValidationMiddleware {
    /**
     * Validate required fields in request data
     */
    public static function validateRequired($data, $requiredFields) {
        $missing = [];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            Flight::halt(400, json_encode([
                'error' => true,
                'message' => 'Missing required fields: ' . implode(', ', $missing)
            ]));
            return false;
        }
        return true;
    }

    /**
     * Validate email format
     */
    public static function validateEmail($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Flight::halt(400, json_encode([
                'error' => true,
                'message' => 'Invalid email format'
            ]));
            return false;
        }
        return true;
    }

    /**
     * Validate numeric ID
     */
    public static function validateId($id) {
        if (!is_numeric($id) || $id <= 0) {
            Flight::halt(400, json_encode([
                'error' => true,
                'message' => 'Invalid ID format'
            ]));
            return false;
        }
        return true;
    }
}
?>

