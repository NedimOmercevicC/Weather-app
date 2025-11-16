<?php
$userService = new UserService();

Flight::route('GET /api/users', function() use ($userService) {
    try {
        $users = $userService->getAllUsers();
        sendJsonResponse(['data' => $users]);
    } catch (Exception $e) {
        handleError($e);
    }
});

Flight::route('GET /api/users/@id', function($id) use ($userService) {
    try {
        $user = $userService->getUserById($id);
        sendJsonResponse(['data' => $user]);
    } catch (Exception $e) {
        handleError($e);
    }
});

Flight::route('POST /api/users', function() use ($userService) {
    try {
        $data = json_decode(Flight::request()->getBody(), true);
        $result = $userService->createUser($data);
        sendJsonResponse(['message' => 'User created successfully', 'data' => $result], 201);
    } catch (Exception $e) {
        handleError($e);
    }
});

Flight::route('PUT /api/users/@id', function($id) use ($userService) {
    try {
        $data = json_decode(Flight::request()->getBody(), true);
        $result = $userService->updateUser($id, $data);
        sendJsonResponse(['message' => 'User updated successfully', 'data' => $result]);
    } catch (Exception $e) {
        handleError($e);
    }
});

Flight::route('DELETE /api/users/@id', function($id) use ($userService) {
    try {
        $result = $userService->deleteUser($id);
        sendJsonResponse(['message' => 'User deleted successfully']);
    } catch (Exception $e) {
        handleError($e);
    }
});

Flight::route('POST /api/users/login', function() use ($userService) {
    try {
        $data = json_decode(Flight::request()->getBody(), true);
        $user = $userService->authenticate($data['email'] ?? '', $data['password'] ?? '');
        if ($user) {
            sendJsonResponse(['message' => 'Login successful', 'data' => $user]);
        } else {
            sendJsonResponse(['error' => true, 'message' => 'Invalid credentials'], 401);
        }
    } catch (Exception $e) {
        handleError($e);
    }
});
?>

