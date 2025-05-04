<?php

require_once 'Database.php';

class ExpenseController {

    // Get all expenses, with optional category filtering
    public static function index() {
        $db = Database::connect();
        
        // Get category from query parameter if available
        $category = Flight::request()->query['category'] ?? null;
        if ($category) {
            // Filter by category
            $stmt = $db->prepare("SELECT * FROM expenses WHERE category = ? ORDER BY date DESC");
            $stmt->execute([$category]);
        } else {
            // No category filter, get all expenses
            $stmt = $db->query("SELECT * FROM expenses ORDER BY date DESC");
        }

        $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        Flight::json($expenses);
    }

    // Store a new expense
    public static function store() {
        $data = Flight::request()->data;

        // Basic validation
        if (empty($data->amount) || empty($data->category) || empty($data->date)) {
            Flight::json(["error" => "Amount, category, and date are required"], 400);
            return;
        }

        $db = Database::connect();
        $stmt = $db->prepare("INSERT INTO expenses (amount, category, description, date) VALUES (?, ?, ?, ?)");
        $stmt->execute([$data->amount, $data->category, $data->description, $data->date]);
        Flight::json(["success" => true, "id" => $db->lastInsertId()]);
    }

    // Show a specific expense by ID
    public static function show($id) {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM expenses WHERE id = ?");
        $stmt->execute([$id]);
        $expense = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($expense) {
            Flight::json($expense);
        } else {
            Flight::json(["error" => "Expense not found"], 404);
        }
    }

    // Update an existing expense
    public static function update($id) {
        $data = Flight::request()->data;

        // Basic validation
        if (empty($data->amount) || empty($data->category) || empty($data->date)) {
            Flight::json(["error" => "Amount, category, and date are required"], 400);
            return;
        }

        $db = Database::connect();
        $stmt = $db->prepare("UPDATE expenses SET amount = ?, category = ?, description = ?, date = ? WHERE id = ?");
        $stmt->execute([$data->amount, $data->category, $data->description, $data->date, $id]);
        Flight::json(["success" => true]);
    }

    // Delete a specific expense
    public static function delete($id) {
        $db = Database::connect();
        $stmt = $db->prepare("DELETE FROM expenses WHERE id = ?");
        $stmt->execute([$id]);
        Flight::json(["success" => true]);
    }

    // Get weekly total expenses
    public static function getWeeklySummary() {
        $db = Database::connect();

        // Start of this week (Monday)
        $startOfWeek = date('Y-m-d', strtotime('last monday'));
        $endOfWeek = date('Y-m-d', strtotime('next sunday'));

        // Fetch the sum of expenses within this week
        $stmt = $db->prepare("SELECT SUM(amount) as total FROM expenses WHERE date BETWEEN ? AND ?");
        $stmt->execute([$startOfWeek, $endOfWeek]);

        // Fetch the result
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Return the result as JSON
        Flight::json(['total' => $result['total'] ?? 0]);
    }

    // Get monthly total expenses
    public static function getMonthlySummary() {
        $db = Database::connect();

        // First day of the current month
        $startOfMonth = date('Y-m-01');
        $endOfMonth = date('Y-m-t');  // Last day of the current month

        // Fetch the sum of expenses within this month
        $stmt = $db->prepare("SELECT SUM(amount) as total FROM expenses WHERE date BETWEEN ? AND ?");
        $stmt->execute([$startOfMonth, $endOfMonth]);

        // Fetch the result
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Return the result as JSON
        Flight::json(['total' => $result['total'] ?? 0]);
    }
}
