<?php
$savedFilterService = new SavedFilterService();

Flight::route('GET /api/saved-filters', function() use ($savedFilterService) {
    try {
        $filters = $savedFilterService->getAllFilters();
        sendJsonResponse(['data' => $filters]);
    } catch (Exception $e) {
        handleError($e);
    }
});

Flight::route('POST /api/saved-filters', function() use ($savedFilterService) {
    try {
        $data = json_decode(Flight::request()->getBody(), true);
        
        if (empty($data)) {
            sendJsonResponse(['error' => true, 'message' => 'Request body is empty'], 400);
            return;
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

// User-specific routes MUST come before generic {id} routes
Flight::route('GET /api/saved-filters/user/@userId', function($userId) use ($savedFilterService) {
    try {
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
        $filter = $savedFilterService->getFilterById($id);
        sendJsonResponse(['data' => $filter]);
    } catch (NotFoundException $e) {
        sendJsonResponse(['error' => true, 'message' => $e->getMessage()], 404);
    } catch (ValidationException $e) {
        sendJsonResponse(['error' => true, 'message' => $e->getMessage()], 400);
    } catch (Exception $e) {
        handleError($e);
    }
});

Flight::route('PUT /api/saved-filters/@id', function($id) use ($savedFilterService) {
    try {
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

Flight::route('DELETE /api/saved-filters/@id', function($id) use ($savedFilterService) {
    try {
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

