<?php


require_once 'UserDao.php';
require_once 'CityDao.php';
require_once 'SavedFilterDao.php';
require_once 'SubscriptionDao.php';
require_once 'PaymentDao.php';

echo "<h2>Weather App DAO Test</h2>";

try {
    // Test UserDao
    echo "<h3>Testing UserDao</h3>";
    $userDao = new UserDao();
    
    // Get all users
    $users = $userDao->getAll();
    echo "Total users: " . count($users) . "<br>";
    
    // Get user by email
    $user = $userDao->getByEmail('john@example.com');
    if ($user) {
        echo "Found user: " . $user['fname'] . " " . $user['lname'] . "<br>";
    }
    
    // Test CityDao
    echo "<h3>Testing CityDao</h3>";
    $cityDao = new CityDao();
    
    // Get all cities
    $cities = $cityDao->getAll();
    echo "Total cities: " . count($cities) . "<br>";
    
    // Search cities
    $searchResults = $cityDao->searchByName('Sarajevo');
    echo "Cities found for 'Sarajevo': " . count($searchResults) . "<br>";
    
    // Test SavedFilterDao
    echo "<h3>Testing SavedFilterDao</h3>";
    $filterDao = new SavedFilterDao();
    
    // Get filters for user 1
    $filters = $filterDao->getByUserId(1);
    echo "Saved filters for user 1: " . count($filters) . "<br>";
    
    // Test SubscriptionDao
    echo "<h3>Testing SubscriptionDao</h3>";
    $subDao = new SubscriptionDao();
    
    // Check if user has active subscription
    $hasActive = $subDao->hasActiveSubscription(1);
    echo "User 1 has active subscription: " . ($hasActive ? 'Yes' : 'No') . "<br>";
    
    // Test PaymentDao
    echo "<h3>Testing PaymentDao</h3>";
    $paymentDao = new PaymentDao();
    
    // Get total amount paid by user
    $totalAmount = $paymentDao->getTotalAmountByUserId(1);
    echo "Total amount paid by user 1: $" . $totalAmount . "<br>";
    
    echo "<h3>All tests completed successfully!</h3>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
