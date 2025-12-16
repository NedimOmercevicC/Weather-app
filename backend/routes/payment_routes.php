<?php
$paymentService = new PaymentService();

// Admin-only routes
Flight::route('GET /api/payments', function() use ($paymentService) {
    try {
        AuthMiddleware::authenticate();
        AdminMiddleware::requireAdmin();
        $payments = $paymentService->getAllPayments();
        sendJsonResponse(['data' => $payments]);
    } catch (Exception $e) {
        handleError($e);
    }
});

// Authenticated users can create payments
Flight::route('POST /api/payments', function() use ($paymentService) {
    try {
        AuthMiddleware::authenticate();
        $data = json_decode(Flight::request()->getBody(), true);
        
        if (empty($data)) {
            sendJsonResponse(['error' => true, 'message' => 'Request body is empty'], 400);
            return;
        }
        
        $result = $paymentService->createPayment($data);
        sendJsonResponse(['message' => 'Payment created successfully', 'data' => $result], 201);
    } catch (ValidationException $e) {
        sendJsonResponse(['error' => true, 'message' => $e->getMessage()], 400);
    } catch (NotFoundException $e) {
        sendJsonResponse(['error' => true, 'message' => $e->getMessage()], 404);
    } catch (Exception $e) {
        handleError($e);
    }
});

// User-specific routes - users can see their own, admins can see any
Flight::route('GET /api/payments/subscription/@subscriptionId', function($subscriptionId) use ($paymentService) {
    try {
        AuthMiddleware::authenticate();
        if (!is_numeric($subscriptionId) || $subscriptionId <= 0) {
            sendJsonResponse(['error' => true, 'message' => 'Invalid subscription ID'], 400);
            return;
        }
        // Get subscription to check ownership
        $subscription = (new SubscriptionService())->getSubscriptionById($subscriptionId);
        $currentUserId = AuthMiddleware::getUserId();
        $isAdmin = AuthMiddleware::isAdmin();
        
        if (!$isAdmin && $subscription['user_id'] != $currentUserId) {
            Flight::halt(403, json_encode(['error' => true, 'message' => 'Access denied']));
        }
        
        $payments = $paymentService->getPaymentsBySubscriptionId($subscriptionId);
        sendJsonResponse(['data' => $payments]);
    } catch (ValidationException $e) {
        sendJsonResponse(['error' => true, 'message' => $e->getMessage()], 400);
    } catch (Exception $e) {
        handleError($e);
    }
});

Flight::route('GET /api/payments/user/@userId', function($userId) use ($paymentService) {
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
        $payments = $paymentService->getPaymentsByUserId($userId);
        sendJsonResponse(['data' => $payments]);
    } catch (ValidationException $e) {
        sendJsonResponse(['error' => true, 'message' => $e->getMessage()], 400);
    } catch (Exception $e) {
        handleError($e);
    }
});

Flight::route('GET /api/payments/@id', function($id) use ($paymentService) {
    try {
        AuthMiddleware::authenticate();
        $payment = $paymentService->getPaymentById($id);
        // Get subscription to check ownership
        $subscription = (new SubscriptionService())->getSubscriptionById($payment['subscription_id']);
        $currentUserId = AuthMiddleware::getUserId();
        $isAdmin = AuthMiddleware::isAdmin();
        
        if (!$isAdmin && $subscription['user_id'] != $currentUserId) {
            Flight::halt(403, json_encode(['error' => true, 'message' => 'Access denied']));
        }
        
        sendJsonResponse(['data' => $payment]);
    } catch (NotFoundException $e) {
        sendJsonResponse(['error' => true, 'message' => $e->getMessage()], 404);
    } catch (ValidationException $e) {
        sendJsonResponse(['error' => true, 'message' => $e->getMessage()], 400);
    } catch (Exception $e) {
        handleError($e);
    }
});

// Admin-only routes
Flight::route('PUT /api/payments/@id', function($id) use ($paymentService) {
    try {
        AuthMiddleware::authenticate();
        AdminMiddleware::requireAdmin();
        $data = json_decode(Flight::request()->getBody(), true);
        
        if (empty($data)) {
            sendJsonResponse(['error' => true, 'message' => 'Request body is empty'], 400);
            return;
        }
        
        $result = $paymentService->updatePayment($id, $data);
        sendJsonResponse(['message' => 'Payment updated successfully', 'data' => $result]);
    } catch (NotFoundException $e) {
        sendJsonResponse(['error' => true, 'message' => $e->getMessage()], 404);
    } catch (ValidationException $e) {
        sendJsonResponse(['error' => true, 'message' => $e->getMessage()], 400);
    } catch (Exception $e) {
        handleError($e);
    }
});

Flight::route('DELETE /api/payments/@id', function($id) use ($paymentService) {
    try {
        AuthMiddleware::authenticate();
        AdminMiddleware::requireAdmin();
        $result = $paymentService->deletePayment($id);
        sendJsonResponse(['message' => 'Payment deleted successfully']);
    } catch (NotFoundException $e) {
        sendJsonResponse(['error' => true, 'message' => $e->getMessage()], 404);
    } catch (ValidationException $e) {
        sendJsonResponse(['error' => true, 'message' => $e->getMessage()], 400);
    } catch (Exception $e) {
        handleError($e);
    }
});
?>

