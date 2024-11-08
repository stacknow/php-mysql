<?php
// Database connection configuration
$host = 'localhost';
$db = 'php_mysql_app';
$user = 'yourUsername';
$pass = 'yourPassword';

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Helper function to send JSON responses
function send_json($data, $status_code = 200) {
    header("Content-Type: application/json");
    http_response_code($status_code);
    echo json_encode($data);
}

// Route to get all users
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_SERVER['REQUEST_URI'] === '/users') {
    $stmt = $pdo->query("SELECT * FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    send_json($users);
}

// Route to create a new user
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['REQUEST_URI'] === '/users') {
    $input = json_decode(file_get_contents("php://input"), true);

    if (!isset($input['name'], $input['email'])) {
        send_json(["error" => "Name and email are required"], 400);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO users (name, email) VALUES (:name, :email)");
    $stmt->bindParam(':name', $input['name']);
    $stmt->bindParam(':email', $input['email']);

    try {
        $stmt->execute();
        $user_id = $pdo->lastInsertId();
        send_json(["id" => $user_id, "name" => $input['name'], "email" => $input['email']], 201);
    } catch (PDOException $e) {
        send_json(["error" => "User creation failed: " . $e->getMessage()], 500);
    }
}

// If the route is not found
else {
    send_json(["error" => "Route not found"], 404);
}
?>
