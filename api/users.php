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
require_once '../models/User.php';
require_once '../models/UserLike.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    http_response_code(500);
    echo json_encode(array("message" => "Database connection failed"));
    exit;
}

$user = new User($db);
$userLike = new UserLike($db);

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        handleGetUsers($user, $userLike);
        break;
    case 'POST':
        handleUserAction($userLike);
        break;
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed"));
}

function handleGetUsers($user, $userLike) {
    $action = isset($_GET['action']) ? $_GET['action'] : 'profile';
    
    switch($action) {
        case 'profile':
            if(isset($_GET['username'])) {
                $user->username = $_GET['username'];
                if($user->getUserByUsername()) {
                    $stats = $user->getUserStats();
                    $user_data = array(
                        "id" => $user->id,
                        "username" => $user->username,
                        "description" => $user->description,
                        "created_at" => $user->created_at,
                        "stats" => $stats
                    );
                    http_response_code(200);
                    echo json_encode($user_data);
                } else {
                    http_response_code(404);
                    echo json_encode(array("message" => "User not found"));
                }
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "Username required"));
            }
            break;
        case 'liked':
            if(!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode(array("message" => "Authentication required"));
                return;
            }
            $liked_users = $userLike->getUserLikedUsers($_SESSION['user_id']);
            http_response_code(200);
            echo json_encode($liked_users);
            break;
        case 'followers':
            if(isset($_GET['user_id'])) {
                $followers = $userLike->getUserFollowers($_GET['user_id']);
                http_response_code(200);
                echo json_encode($followers);
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "User ID required"));
            }
            break;
        default:
            http_response_code(400);
            echo json_encode(array("message" => "Invalid action"));
    }
}

function handleUserAction($userLike) {
    if(!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(array("message" => "Authentication required"));
        return;
    }
    
    $data = json_decode(file_get_contents("php://input"));
    
    if(!isset($data->action)) {
        http_response_code(400);
        echo json_encode(array("message" => "Action required"));
        return;
    }
    
    switch($data->action) {
        case 'update_profile':
            $user = new User($userLike->conn ?? $GLOBALS['db']);
            $user->id = $_SESSION['user_id'];
            $user->username = $data->username ?? '';
            $user->email = $data->email ?? '';
            $user->description = $data->description ?? '';
            if($user->updateProfile()) {
                http_response_code(200);
                echo json_encode(array("message" => "Profile updated"));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to update profile"));
            }
            break;
        case 'like':
        case 'follow':
            if (!isset($data->user_id)) { http_response_code(400); echo json_encode(["message"=>"user_id required"]); return; }
            $userLike->user_id = $_SESSION['user_id'];
            $userLike->liked_user_id = $data->user_id;
            if($userLike->likeUser()) {
                http_response_code(200);
                echo json_encode(array("message" => "User followed successfully"));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to follow user"));
            }
            break;
        case 'unlike':
        case 'unfollow':
            if (!isset($data->user_id)) { http_response_code(400); echo json_encode(["message"=>"user_id required"]); return; }
            $userLike->user_id = $_SESSION['user_id'];
            $userLike->liked_user_id = $data->user_id;
            if($userLike->unlikeUser()) {
                http_response_code(200);
                echo json_encode(array("message" => "User unfollowed successfully"));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to unfollow user"));
            }
            break;
        default:
            http_response_code(400);
            echo json_encode(array("message" => "Invalid action"));
    }
}
?>
