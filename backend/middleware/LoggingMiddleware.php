<?php

class LoggingMiddleware {
    /**
     * Log request information (optional - basic logging)
     */
    public static function logRequest() {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $_SERVER['REQUEST_METHOD'],
            'uri' => $_SERVER['REQUEST_URI'],
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];

        // Optional: Write to log file
        // $logFile = __DIR__ . '/../logs/requests.log';
        // if (!file_exists(dirname($logFile))) {
        //     mkdir(dirname($logFile), 0755, true);
        // }
        // file_put_contents($logFile, json_encode($logData) . "\n", FILE_APPEND);

        // For now, just store in Flight for potential use
        Flight::set('request_log', $logData);
        
        return true;
    }
}
?>

