<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    http_response_code(500);
    echo json_encode(array("message" => "Database connection failed"));
    exit;
}

$user = new User($db);

if (!isset($_GET['q'])) {
    http_response_code(400);
    echo json_encode(array("message" => "Search query required"));
    exit;
}

$query = $_GET['q'];
$users = $user->searchUsers($query);

http_response_code(200);
echo json_encode(array("users" => $users));
?>
