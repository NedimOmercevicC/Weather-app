<?php
$userService = new UserService();
require_once __DIR__ . '/../services/AuthService.php';
$authService = new AuthService();

// Public routes - no authentication required
Flight::route('POST /api/users/register', function() use ($authService) {
    try {
        $data = json_decode(Flight::request()->getBody(), true);
        $response = $authService->register($data);
        if ($response['success']) {
            sendJsonResponse(['message' => 'User registered successfully', 'data' => $response['data']], 201);
        } else {
            sendJsonResponse(['error' => true, 'message' => $response['error']], 400);
        }
    } catch (Exception $e) {
        handleError($e);
    }
});

Flight::route('POST /api/users/login', function() use ($authService) {
    try {
        $data = json_decode(Flight::request()->getBody(), true);
        $response = $authService->login($data);
        if ($response['success']) {
            sendJsonResponse(['message' => 'User logged in successfully', 'data' => $response['data']]);
        } else {
            Flight::halt(401, json_encode(['error' => true, 'message' => $response['error']]));
        }
    } catch (Exception $e) {
        handleError($e);
    }
});

// Protected routes - require authentication
Flight::route('GET /api/users/me', function() {
    try {
        AuthMiddleware::authenticate();
        $userId = AuthMiddleware::getUserId();
        $user = (new UserService())->getUserById($userId);
        sendJsonResponse(['data' => $user]);
    } catch (Exception $e) {
        handleError($e);
    }
});

// Admin-only routes - require admin role
Flight::route('GET /api/users', function() use ($userService) {
    try {
        AuthMiddleware::authenticate();
        AdminMiddleware::requireAdmin();
        $users = $userService->getAllUsers();
        sendJsonResponse(['data' => $users]);
    } catch (Exception $e) {
        handleError($e);
    }
});

Flight::route('GET /api/users/@id', function($id) use ($userService) {
    try {
        AuthMiddleware::authenticate();
        // Users can view their own profile, admins can view any
        $userId = AuthMiddleware::getUserId();
        $isAdmin = AuthMiddleware::isAdmin();
        if (!$isAdmin && $userId != $id) {
            Flight::halt(403, json_encode(['error' => true, 'message' => 'Access denied']));
        }
        $user = $userService->getUserById($id);
        sendJsonResponse(['data' => $user]);
    } catch (Exception $e) {
        handleError($e);
    }
});

Flight::route('PUT /api/users/@id', function($id) use ($userService) {
    try {
        AuthMiddleware::authenticate();
        // Users can update their own profile, admins can update any
        $userId = AuthMiddleware::getUserId();
        $isAdmin = AuthMiddleware::isAdmin();
        if (!$isAdmin && $userId != $id) {
            Flight::halt(403, json_encode(['error' => true, 'message' => 'Access denied']));
        }
        $data = json_decode(Flight::request()->getBody(), true);
        // Prevent non-admins from changing is_admin status
        if (!$isAdmin && isset($data['is_admin'])) {
            unset($data['is_admin']);
        }
        $result = $userService->updateUser($id, $data);
        sendJsonResponse(['message' => 'User updated successfully', 'data' => $result]);
    } catch (Exception $e) {
        handleError($e);
    }
});

Flight::route('DELETE /api/users/@id', function($id) use ($userService) {
    try {
        AuthMiddleware::authenticate();
        AdminMiddleware::requireAdmin();
        $result = $userService->deleteUser($id);
        sendJsonResponse(['message' => 'User deleted successfully']);
    } catch (Exception $e) {
        handleError($e);
    }
});
?>

