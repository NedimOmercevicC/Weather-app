<?php
$savedFilterService = new SavedFilterService();

// Admin-only routes
Flight::route('GET /api/saved-filters', function() use ($savedFilterService) {
    try {
        AuthMiddleware::authenticate();
        AdminMiddleware::requireAdmin();
        $filters = $savedFilterService->getAllFilters();
        sendJsonResponse(['data' => $filters]);
    } catch (Exception $e) {
        handleError($e);
    }
});

// Authenticated users can create their own filters
Flight::route('POST /api/saved-filters', function() use ($savedFilterService) {
    try {
        AuthMiddleware::authenticate();
        $userId = AuthMiddleware::getUserId();
        $data = json_decode(Flight::request()->getBody(), true);
        
        if (empty($data)) {
            sendJsonResponse(['error' => true, 'message' => 'Request body is empty'], 400);
            return;
        }
        
        // Non-admins can only create filters for themselves
        if (!AuthMiddleware::isAdmin() && (!isset($data['user_id']) || $data['user_id'] != $userId)) {
            $data['user_id'] = $userId;
        }
        
        $result = $savedFilterService->createFilter($data);
        sendJsonResponse(['message' => 'Filter created successfully', 'data' => $result], 201);
    } catch (ValidationException $e) {
        sendJsonResponse(['error' => true, 'message' => $e->getMessage()], 400);
    } catch (NotFoundException $e) {
        sendJsonResponse(['error' => true, 'message' => $e->getMessage()], 404);
    } catch (Exception $e) {
        handleError($e);
    }
});

// User-specific routes - users can see their own, admins can see any
Flight::route('GET /api/saved-filters/user/@userId', function($userId) use ($savedFilterService) {
    try {
        AuthMiddleware::authenticate();
        $currentUserId = AuthMiddleware::getUserId();
        $isAdmin = AuthMiddleware::isAdmin();
        
        if (!$isAdmin && $currentUserId != $userId) {
            Flight::halt(403, json_encode(['error' => true, 'message' => 'Access denied']));
        }
        
        if (!is_numeric($userId) || $userId <= 0) {
            sendJsonResponse(['error' => true, 'message' => 'Invalid user ID'], 400);
            return;
        }
        $filters = $savedFilterService->getFiltersByUserId($userId);
        sendJsonResponse(['data' => $filters]);
    } catch (ValidationException $e) {
        sendJsonResponse(['error' => true, 'message' => $e->getMessage()], 400);
    } catch (Exception $e) {
        handleError($e);
    }
});

Flight::route('GET /api/saved-filters/@id', function($id) use ($savedFilterService) {
    try {
        AuthMiddleware::authenticate();
        $filter = $savedFilterService->getFilterById($id);
        $currentUserId = AuthMiddleware::getUserId();
        $isAdmin = AuthMiddleware::isAdmin();
        
        // Users can only see their own filters, admins can see any
        if (!$isAdmin && $filter['user_id'] != $currentUserId) {
            Flight::halt(403, json_encode(['error' => true, 'message' => 'Access denied']));
        }
        
        sendJsonResponse(['data' => $filter]);
    } catch (NotFoundException $e) {
        sendJsonResponse(['error' => true, 'message' => $e->getMessage()], 404);
    } catch (ValidationException $e) {
        sendJsonResponse(['error' => true, 'message' => $e->getMessage()], 400);
    } catch (Exception $e) {
        handleError($e);
    }
});

// Users can update their own filters, admins can update any
Flight::route('PUT /api/saved-filters/@id', function($id) use ($savedFilterService) {
    try {
        AuthMiddleware::authenticate();
        $filter = $savedFilterService->getFilterById($id);
        $currentUserId = AuthMiddleware::getUserId();
        $isAdmin = AuthMiddleware::isAdmin();
        
        if (!$isAdmin && $filter['user_id'] != $currentUserId) {
            Flight::halt(403, json_encode(['error' => true, 'message' => 'Access denied']));
        }
        
        $data = json_decode(Flight::request()->getBody(), true);
        
        if (empty($data)) {
            sendJsonResponse(['error' => true, 'message' => 'Request body is empty'], 400);
            return;
        }
        
        $result = $savedFilterService->updateFilter($id, $data);
        sendJsonResponse(['message' => 'Filter updated successfully', 'data' => $result]);
    } catch (NotFoundException $e) {
        sendJsonResponse(['error' => true, 'message' => $e->getMessage()], 404);
    } catch (ValidationException $e) {
        sendJsonResponse(['error' => true, 'message' => $e->getMessage()], 400);
    } catch (Exception $e) {
        handleError($e);
    }
});

// Users can delete their own filters, admins can delete any
Flight::route('DELETE /api/saved-filters/@id', function($id) use ($savedFilterService) {
    try {
        AuthMiddleware::authenticate();
        $filter = $savedFilterService->getFilterById($id);
        $currentUserId = AuthMiddleware::getUserId();
        $isAdmin = AuthMiddleware::isAdmin();
        
        if (!$isAdmin && $filter['user_id'] != $currentUserId) {
            Flight::halt(403, json_encode(['error' => true, 'message' => 'Access denied']));
        }
        
        $result = $savedFilterService->deleteFilter($id);
        sendJsonResponse(['message' => 'Filter deleted successfully']);
    } catch (NotFoundException $e) {
        sendJsonResponse(['error' => true, 'message' => $e->getMessage()], 404);
    } catch (ValidationException $e) {
        sendJsonResponse(['error' => true, 'message' => $e->getMessage()], 400);
    } catch (Exception $e) {
        handleError($e);
    }
});
?>

