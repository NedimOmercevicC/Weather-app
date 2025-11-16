<?php
$subscriptionService = new SubscriptionService();

Flight::route('GET /api/subscriptions', function() use ($subscriptionService) {
    try {
        $subscriptions = $subscriptionService->getAllSubscriptions();
        sendJsonResponse(['data' => $subscriptions]);
    } catch (Exception $e) {
        handleError($e);
    }
});

Flight::route('POST /api/subscriptions', function() use ($subscriptionService) {
    try {
        $data = json_decode(Flight::request()->getBody(), true);
        
        if (empty($data)) {
            sendJsonResponse(['error' => true, 'message' => 'Request body is empty'], 400);
            return;
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

// User-specific routes MUST come before generic {id} routes
Flight::route('GET /api/subscriptions/user/@userId/active', function($userId) use ($subscriptionService) {
    try {
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
        $subscription = $subscriptionService->getSubscriptionById($id);
        sendJsonResponse(['data' => $subscription]);
    } catch (NotFoundException $e) {
        sendJsonResponse(['error' => true, 'message' => $e->getMessage()], 404);
    } catch (ValidationException $e) {
        sendJsonResponse(['error' => true, 'message' => $e->getMessage()], 400);
    } catch (Exception $e) {
        handleError($e);
    }
});

Flight::route('PUT /api/subscriptions/@id', function($id) use ($subscriptionService) {
    try {
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

