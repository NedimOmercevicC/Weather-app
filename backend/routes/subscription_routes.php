<?php
$subscriptionService = new SubscriptionService();

// Admin-only routes
Flight::route('GET /api/subscriptions', function() use ($subscriptionService) {
    try {
        AuthMiddleware::authenticate();
        AdminMiddleware::requireAdmin();
        $subscriptions = $subscriptionService->getAllSubscriptions();
        sendJsonResponse(['data' => $subscriptions]);
    } catch (Exception $e) {
        handleError($e);
    }
});

// Authenticated users can create their own subscriptions
Flight::route('POST /api/subscriptions', function() use ($subscriptionService) {
    try {
        AuthMiddleware::authenticate();
        $userId = AuthMiddleware::getUserId();
        $data = json_decode(Flight::request()->getBody(), true);
        
        if (empty($data)) {
            sendJsonResponse(['error' => true, 'message' => 'Request body is empty'], 400);
            return;
        }
        
        // Non-admins can only create subscriptions for themselves
        if (!AuthMiddleware::isAdmin() && (!isset($data['user_id']) || $data['user_id'] != $userId)) {
            $data['user_id'] = $userId;
        }
        
        $result = $subscriptionService->createSubscription($data);
        sendJsonResponse(['message' => 'Subscription created successfully', 'data' => $result], 201);
    } catch (ValidationException $e) {
        sendJsonResponse(['error' => true, 'message' => $e->getMessage()], 400);
    } catch (NotFoundException $e) {
        sendJsonResponse(['error' => true, 'message' => $e->getMessage()], 404);
    } catch (Exception $e) {
        handleError($e);
    }
});

// User-specific routes - users can see their own, admins can see any
Flight::route('GET /api/subscriptions/user/@userId/active', function($userId) use ($subscriptionService) {
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
        $subscription = $subscriptionService->getActiveSubscriptionByUserId($userId);
        if ($subscription) {
            sendJsonResponse(['data' => $subscription]);
        } else {
            sendJsonResponse(['data' => null, 'message' => 'No active subscription'], 404);
        }
    } catch (ValidationException $e) {
        sendJsonResponse(['error' => true, 'message' => $e->getMessage()], 400);
    } catch (Exception $e) {
        handleError($e);
    }
});

Flight::route('GET /api/subscriptions/user/@userId', function($userId) use ($subscriptionService) {
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
        $subscriptions = $subscriptionService->getSubscriptionsByUserId($userId);
        sendJsonResponse(['data' => $subscriptions]);
    } catch (ValidationException $e) {
        sendJsonResponse(['error' => true, 'message' => $e->getMessage()], 400);
    } catch (Exception $e) {
        handleError($e);
    }
});

Flight::route('GET /api/subscriptions/@id', function($id) use ($subscriptionService) {
    try {
        AuthMiddleware::authenticate();
        $subscription = $subscriptionService->getSubscriptionById($id);
        $currentUserId = AuthMiddleware::getUserId();
        $isAdmin = AuthMiddleware::isAdmin();
        
        // Users can only see their own subscriptions, admins can see any
        if (!$isAdmin && $subscription['user_id'] != $currentUserId) {
            Flight::halt(403, json_encode(['error' => true, 'message' => 'Access denied']));
        }
        
        sendJsonResponse(['data' => $subscription]);
    } catch (NotFoundException $e) {
        sendJsonResponse(['error' => true, 'message' => $e->getMessage()], 404);
    } catch (ValidationException $e) {
        sendJsonResponse(['error' => true, 'message' => $e->getMessage()], 400);
    } catch (Exception $e) {
        handleError($e);
    }
});

// Admin-only routes
Flight::route('PUT /api/subscriptions/@id', function($id) use ($subscriptionService) {
    try {
        AuthMiddleware::authenticate();
        AdminMiddleware::requireAdmin();
        $data = json_decode(Flight::request()->getBody(), true);
        
        if (empty($data)) {
            sendJsonResponse(['error' => true, 'message' => 'Request body is empty'], 400);
            return;
        }
        
        $result = $subscriptionService->updateSubscription($id, $data);
        sendJsonResponse(['message' => 'Subscription updated successfully', 'data' => $result]);
    } catch (NotFoundException $e) {
        sendJsonResponse(['error' => true, 'message' => $e->getMessage()], 404);
    } catch (ValidationException $e) {
        sendJsonResponse(['error' => true, 'message' => $e->getMessage()], 400);
    } catch (Exception $e) {
        handleError($e);
    }
});

Flight::route('DELETE /api/subscriptions/@id', function($id) use ($subscriptionService) {
    try {
        AuthMiddleware::authenticate();
        AdminMiddleware::requireAdmin();
        $result = $subscriptionService->deleteSubscription($id);
        sendJsonResponse(['message' => 'Subscription deleted successfully']);
    } catch (NotFoundException $e) {
        sendJsonResponse(['error' => true, 'message' => $e->getMessage()], 404);
    } catch (ValidationException $e) {
        sendJsonResponse(['error' => true, 'message' => $e->getMessage()], 400);
    } catch (Exception $e) {
        handleError($e);
    }
});
?>

