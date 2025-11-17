<?php
$paymentService = new PaymentService();

Flight::route('GET /api/payments', function() use ($paymentService) {
    try {
        $payments = $paymentService->getAllPayments();
        sendJsonResponse(['data' => $payments]);
    } catch (Exception $e) {
        handleError($e);
    }
});

Flight::route('POST /api/payments', function() use ($paymentService) {
    try {
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

// Specific routes MUST come before generic {id} routes
Flight::route('GET /api/payments/subscription/@subscriptionId', function($subscriptionId) use ($paymentService) {
    try {
        if (!is_numeric($subscriptionId) || $subscriptionId <= 0) {
            sendJsonResponse(['error' => true, 'message' => 'Invalid subscription ID'], 400);
            return;
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
        $payment = $paymentService->getPaymentById($id);
        sendJsonResponse(['data' => $payment]);
    } catch (NotFoundException $e) {
        sendJsonResponse(['error' => true, 'message' => $e->getMessage()], 404);
    } catch (ValidationException $e) {
        sendJsonResponse(['error' => true, 'message' => $e->getMessage()], 400);
    } catch (Exception $e) {
        handleError($e);
    }
});

Flight::route('PUT /api/payments/@id', function($id) use ($paymentService) {
    try {
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

