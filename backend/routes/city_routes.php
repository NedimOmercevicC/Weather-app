<?php
$cityService = new CityService();

Flight::route('GET /api/cities', function() use ($cityService) {
    try {
        $cities = $cityService->getAllCities();
        sendJsonResponse(['data' => $cities]);
    } catch (Exception $e) {
        handleError($e);
    }
});

Flight::route('POST /api/cities', function() use ($cityService) {
    try {
        $data = json_decode(Flight::request()->getBody(), true);
        
        if (empty($data)) {
            sendJsonResponse(['error' => true, 'message' => 'Request body is empty'], 400);
            return;
        }
        
        $result = $cityService->createCity($data);
        sendJsonResponse(['message' => 'City created successfully', 'data' => $result], 201);
    } catch (ValidationException $e) {
        sendJsonResponse(['error' => true, 'message' => $e->getMessage()], 400);
    } catch (Exception $e) {
        handleError($e);
    }
});

// Search route MUST come before {id} route to avoid conflicts
Flight::route('GET /api/cities/search/@term', function($term) use ($cityService) {
    try {
        if (empty($term)) {
            sendJsonResponse(['error' => true, 'message' => 'Search term cannot be empty'], 400);
            return;
        }
        $cities = $cityService->searchCities($term);
        sendJsonResponse(['data' => $cities]);
    } catch (ValidationException $e) {
        sendJsonResponse(['error' => true, 'message' => $e->getMessage()], 400);
    } catch (Exception $e) {
        handleError($e);
    }
});

Flight::route('GET /api/cities/@id', function($id) use ($cityService) {
    try {
        $city = $cityService->getCityById($id);
        sendJsonResponse(['data' => $city]);
    } catch (NotFoundException $e) {
        sendJsonResponse(['error' => true, 'message' => $e->getMessage()], 404);
    } catch (ValidationException $e) {
        sendJsonResponse(['error' => true, 'message' => $e->getMessage()], 400);
    } catch (Exception $e) {
        handleError($e);
    }
});

Flight::route('PUT /api/cities/@id', function($id) use ($cityService) {
    try {
        $data = json_decode(Flight::request()->getBody(), true);
        
        if (empty($data)) {
            sendJsonResponse(['error' => true, 'message' => 'Request body is empty'], 400);
            return;
        }
        
        $result = $cityService->updateCity($id, $data);
        sendJsonResponse(['message' => 'City updated successfully', 'data' => $result]);
    } catch (NotFoundException $e) {
        sendJsonResponse(['error' => true, 'message' => $e->getMessage()], 404);
    } catch (ValidationException $e) {
        sendJsonResponse(['error' => true, 'message' => $e->getMessage()], 400);
    } catch (Exception $e) {
        handleError($e);
    }
});

Flight::route('DELETE /api/cities/@id', function($id) use ($cityService) {
    try {
        $result = $cityService->deleteCity($id);
        sendJsonResponse(['message' => 'City deleted successfully']);
    } catch (NotFoundException $e) {
        sendJsonResponse(['error' => true, 'message' => $e->getMessage()], 404);
    } catch (ValidationException $e) {
        sendJsonResponse(['error' => true, 'message' => $e->getMessage()], 400);
    } catch (Exception $e) {
        handleError($e);
    }
});
?>

