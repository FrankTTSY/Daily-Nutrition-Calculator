<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// ข้อมูลการเชื่อมต่อจาก Aiven Cloud MySQL
$host = "mysql-xxxx-xxxx.aivencloud.com"; // นำ Host จาก Aiven มาใส่
$port = "12345";                         // นำ Port จาก Aiven มาใส่
$dbname = "defaultdb";                    // ชื่อ Database บน Aiven
$user = "avnadmin";                       // Username จาก Aiven
$pass = "YOUR_AIVEN_PASSWORD";            // Password จาก Aiven

try {
    // Aiven บังคับใช้การเชื่อมต่อแบบปลอดภัย SSL
    $options = [
        PDO::MYSQL_ATTR_SSL_CA => true,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ];
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $user, $pass, $options);
    
    // สร้างตารางอัตโนมัติหากยังไม่มีใน Aiven
    $pdo->exec("CREATE TABLE IF NOT EXISTS nutrition_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        date VARCHAR(100) NOT NULL,
        goal VARCHAR(255) NOT NULL,
        calories INT NOT NULL,
        p INT NOT NULL,
        c INT NOT NULL,
        f INT NOT NULL
    )");
} catch (PDOException $e) {
    echo json_encode(["error" => "Connection failed: " . $e->getMessage()]);
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
        echo json_encode(["status" => "success", "message" => "Saved to Aiven MySQL"]);
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