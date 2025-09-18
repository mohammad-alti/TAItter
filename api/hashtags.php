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

require_once '../config/database.php';
require_once '../models/Hashtag.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    http_response_code(500);
    echo json_encode(array("message" => "Database connection failed"));
    exit;
}

$hashtag = new Hashtag($db);

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        handleGetHashtags($hashtag);
        break;
    case 'POST':
        handleHashtagAction($hashtag);
        break;
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed"));
}

function handleGetHashtags($hashtag) {
    $action = isset($_GET['action']) ? $_GET['action'] : 'popular';
    
    switch($action) {
        case 'popular':
            $hashtags = $hashtag->getPopularHashtags();
            break;
        case 'followed':
            if(!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode(array("message" => "Authentication required"));
                return;
            }
            $hashtags = $hashtag->getUserFollowedHashtags($_SESSION['user_id']);
            break;
        default:
            http_response_code(400);
            echo json_encode(array("message" => "Invalid action"));
            return;
    }
    
    http_response_code(200);
    echo json_encode($hashtags);
}

function handleHashtagAction($hashtag) {
    if(!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(array("message" => "Authentication required"));
        return;
    }
    
    $data = json_decode(file_get_contents("php://input"));
    
    if(!isset($data->action) || !isset($data->hashtag_id)) {
        http_response_code(400);
        echo json_encode(array("message" => "Action and hashtag_id required"));
        return;
    }
    
    switch($data->action) {
        case 'follow':
            if($hashtag->followHashtag($_SESSION['user_id'], $data->hashtag_id)) {
                http_response_code(200);
                echo json_encode(array("message" => "Hashtag followed successfully"));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to follow hashtag"));
            }
            break;
        case 'unfollow':
            if($hashtag->unfollowHashtag($_SESSION['user_id'], $data->hashtag_id)) {
                http_response_code(200);
                echo json_encode(array("message" => "Hashtag unfollowed successfully"));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to unfollow hashtag"));
            }
            break;
        default:
            http_response_code(400);
            echo json_encode(array("message" => "Invalid action"));
    }
}
?>
