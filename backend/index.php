<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/vendor/mikecao/flight/Flight.php')) {
    require_once __DIR__ . '/vendor/mikecao/flight/Flight.php';
} else {
    http_response_code(500);
    die(json_encode(['error' => true, 'message' => 'FlightPHP framework not found']));
}

if (!class_exists('ValidationException')) {
    class ValidationException extends Exception {}
}
if (!class_exists('NotFoundException')) {
    class NotFoundException extends Exception {}
}

try {
    require_once __DIR__ . '/config.php';
    require_once __DIR__ . '/Database.php';
    
    // Load middleware
    require_once __DIR__ . '/middleware/AuthMiddleware.php';
    require_once __DIR__ . '/middleware/AdminMiddleware.php';
    require_once __DIR__ . '/middleware/ValidationMiddleware.php';
    require_once __DIR__ . '/middleware/LoggingMiddleware.php';
    
    // Load services
    require_once __DIR__ . '/services/UserService.php';
    require_once __DIR__ . '/services/CityService.php';
    require_once __DIR__ . '/services/SubscriptionService.php';
    require_once __DIR__ . '/services/PaymentService.php';
    require_once __DIR__ . '/services/SavedFilterService.php';
    require_once __DIR__ . '/services/AuthService.php';

    // Load routes
    require_once __DIR__ . '/routes/user_routes.php';
    require_once __DIR__ . '/routes/city_routes.php';
    require_once __DIR__ . '/routes/subscription_routes.php';
    require_once __DIR__ . '/routes/payment_routes.php';
    require_once __DIR__ . '/routes/saved_filter_routes.php';
} catch (Exception $e) {
    http_response_code(500);
    die(json_encode(['error' => true, 'message' => 'Failed to load routes: ' . $e->getMessage()]));
}

function sendJsonResponse($data, $statusCode = 200) {
    try {
        Flight::json($data, $statusCode);
    } catch (Exception $e) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        die(json_encode($data));
    }
}

function handleError($e) {
    $statusCode = 500;
    $message = "Internal server error";

    if ($e instanceof ValidationException || $e instanceof InvalidArgumentException) {
        $statusCode = 400;
        $message = $e->getMessage();
    } elseif ($e instanceof NotFoundException) {
        $statusCode = 404;
        $message = $e->getMessage();
    } elseif ($e instanceof Exception) {
        if (strpos($e->getMessage(), 'foreign key') !== false || 
            strpos($e->getMessage(), 'constraint') !== false) {
            $statusCode = 400;
            $message = "Database constraint violation: " . $e->getMessage();
        } else {
            $statusCode = 500;
            $message = $e->getMessage() . " (File: " . basename($e->getFile()) . ", Line: " . $e->getLine() . ")";
        }
    }

    sendJsonResponse([
        'error' => true,
        'message' => $message
    ], $statusCode);
}

Flight::map('error', function($e) {
    handleError($e);
});

Flight::map('notFound', function() {
    sendJsonResponse([
        'error' => true,
        'message' => 'Endpoint not found'
    ], 404);
});

Flight::route('GET /', function() {
    try {
        sendJsonResponse([
            'message' => 'Weather App API',
            'version' => '1.0.0',
            'endpoints' => [
                'users' => '/api/users',
                'cities' => '/api/cities',
                'subscriptions' => '/api/subscriptions',
                'payments' => '/api/payments',
                'saved-filters' => '/api/saved-filters',
                'documentation' => '/api-docs'
            ]
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        die(json_encode(['error' => true, 'message' => 'Route error: ' . $e->getMessage()]));
    }
});

Flight::route('GET /api-docs', function() {
    $docsPath = __DIR__ . '/api-docs/index.html';
    if (file_exists($docsPath)) {
        readfile($docsPath);
    } else {
        sendJsonResponse(['error' => true, 'message' => 'API documentation not found'], 404);
    }
});

Flight::route('GET /api-docs/openapi.yaml', function() {
    $yamlPath = __DIR__ . '/api-docs/openapi.yaml';
    if (file_exists($yamlPath)) {
        header('Content-Type: application/x-yaml');
        readfile($yamlPath);
    } else {
        sendJsonResponse(['error' => true, 'message' => 'OpenAPI specification not found'], 404);
    }
});

try {
    Flight::start();
} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    die(json_encode([
        'error' => true,
        'message' => 'FlightPHP startup error: ' . $e->getMessage() . ' (File: ' . basename($e->getFile()) . ', Line: ' . $e->getLine() . ')'
    ]));
} catch (Error $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    die(json_encode([
        'error' => true,
        'message' => 'Fatal error: ' . $e->getMessage() . ' (File: ' . basename($e->getFile()) . ', Line: ' . $e->getLine() . ')'
    ]));
}
?>

