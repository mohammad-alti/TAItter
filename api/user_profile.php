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
require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../models/UserLike.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    http_response_code(500);
    echo json_encode(array("message" => "Database connection failed"));
    exit;
}

$user = new User($db);
$post = new Post($db);
$userLike = new UserLike($db);

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        handleGetUserProfile($user, $post, $userLike);
        break;
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed"));
}

function handleGetUserProfile($user, $post, $userLike) {
    if (!isset($_GET['username'])) {
        http_response_code(400);
        echo json_encode(array("message" => "Username required"));
        return;
    }
    
    $username = $_GET['username'];
    $user->username = $username;
    
    if (!$user->getUserByUsername()) {
        http_response_code(404);
        echo json_encode(array("message" => "User not found"));
        return;
    }
    
    // Get user posts with like information
    $posts = $post->getPostsByUser($user->id);
    
    // Get follower count
    $followers = $userLike->getUserFollowers($user->id);
    $followerCount = count($followers);
    
    // Get following count
    $following = $userLike->getUserLikedUsers($user->id);
    $followingCount = count($following);
    
    // Check if current user follows this user
    $isFollowing = false;
    if (isset($_SESSION['user_id'])) {
        $isFollowing = $userLike->isUserLiked($_SESSION['user_id'], $user->id);
    }
    
    $profile = array(
        'id' => $user->id,
        'username' => $user->username,
        'email' => $user->email,
        'description' => $user->description,
        'created_at' => $user->created_at,
        'posts' => $posts,
        'follower_count' => $followerCount,
        'following_count' => $followingCount,
        'is_following' => $isFollowing
    );
    
    http_response_code(200);
    echo json_encode($profile);
}
?>
