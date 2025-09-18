<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/PostLike.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    http_response_code(500);
    echo json_encode(array("message" => "Database connection failed"));
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(array("message" => "Authentication required"));
    exit;
}

$postLike = new PostLike($db);
$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'POST':
        handleLikePost($postLike);
        break;
    case 'GET':
        handleGetLikes($postLike);
        break;
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed"));
}

function handleLikePost($postLike) {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($data['post_id'])) {
        http_response_code(400);
        echo json_encode(array("message" => "Post ID required"));
        return;
    }
    
    $postLike->user_id = $_SESSION['user_id'];
    $postLike->post_id = $data['post_id'];
    
    if ($postLike->toggleLike()) {
        $likeCount = $postLike->getPostLikeCount($data['post_id']);
        http_response_code(200);
        echo json_encode(array("message" => "Like toggled successfully", "like_count" => $likeCount));
    } else {
        http_response_code(500);
        echo json_encode(array("message" => "Failed to toggle like"));
    }
}

function handleGetLikes($postLike) {
    $postLike->user_id = $_SESSION['user_id'];
    $likes = $postLike->getUserLikes();
    
    http_response_code(200);
    echo json_encode(array("likes" => $likes));
}
?>
