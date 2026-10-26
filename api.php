<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// เชื่อมต่อฐานข้อมูล MySQL ผ่าน PDO
try {
    $pdo = new PDO("mysql:host=localhost;dbname=nutrition_db;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["error" => "Database connection failed: " . $e->getMessage()]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// 1. บันทึกข้อมูล (POST)
if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    if ($data) {
        $stmt = $pdo->prepare("INSERT INTO nutrition_history (name, date, goal, calories, p, c, f) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['name'] ?? 'ผู้ใช้',
            $data['date'] ?? date('Y-m-d H:i:s'),
            $data['goal'] ?? '-',
            $data['calories'] ?? 0,
            $data['p'] ?? 0,
            $data['c'] ?? 0,
            $data['f'] ?? 0
        ]);
        echo json_encode(["status" => "success", "message" => "Saved successfully"]);
    }
} 
// 2. ดึงข้อมูลประวัติ (GET)
else if ($method === 'GET') {
    $stmt = $pdo->query("SELECT id, name, date, goal, calories, p, c, f FROM nutrition_history ORDER BY id DESC LIMIT 10");
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($history);
}
// 3. ลบข้อมูลประวัติ (DELETE)
else if ($method === 'DELETE') {
    if (isset($_GET['action']) && $_GET['action'] === 'clear_all') {
        $pdo->query("TRUNCATE TABLE nutrition_history");
        echo json_encode(["status" => "success", "message" => "All records cleared"]);
    } else if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("DELETE FROM nutrition_history WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        echo json_encode(["status" => "success", "message" => "Record deleted"]);
    }
}
?>
