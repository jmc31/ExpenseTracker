<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/ExpenseController.php';

Flight::route('GET /expenses', ['ExpenseController', 'index']);
Flight::route('POST /expenses', ['ExpenseController', 'store']);
Flight::route('GET /expenses/@id', ['ExpenseController', 'show']);
Flight::route('PUT /expenses/@id', ['ExpenseController', 'update']);
Flight::route('DELETE /expenses/@id', ['ExpenseController', 'delete']);

// ✅ INSERT SUMMARY ROUTE HERE
Flight::route('GET /summary', function() {
    $range = $_GET['range'] ?? 'monthly';
    
    // Only allow weekly or monthly for security purposes
    if (!in_array($range, ['weekly', 'monthly'])) {
        Flight::json(['error' => 'Invalid range parameter'], 400);
        return;
    }
    
    // Call respective controller method
    if ($range === 'weekly') {
        return ExpenseController::getWeeklySummary();
    } else {
        return ExpenseController::getMonthlySummary();
    }
});


Flight::start();
