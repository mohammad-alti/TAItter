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
require_once '../models/Post.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    http_response_code(500);
    echo json_encode(array("message" => "Database connection failed"));
    exit;
}

$post = new Post($db);

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        handleGetPosts($post);
        break;
    case 'POST':
        handleCreatePost($post);
        break;
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed"));
}

function handleGetPosts($post) {
    $action = isset($_GET['action']) ? $_GET['action'] : 'feed';
    
    switch($action) {
        case 'feed':
            if(isset($_SESSION['user_id'])) {
                $posts = $post->getPersonalizedFeed($_SESSION['user_id']);
            } else {
                $posts = $post->getAllPosts();
            }
            break;
        case 'user':
            if(isset($_GET['user_id'])) {
                $posts = $post->getPostsByUser($_GET['user_id']);
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "User ID required"));
                return;
            }
            break;
        case 'hashtag':
            if(isset($_GET['hashtag'])) {
                $posts = $post->getPostsByHashtag($_GET['hashtag']);
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "Hashtag required"));
                return;
            }
            break;
        default:
            http_response_code(400);
            echo json_encode(array("message" => "Invalid action"));
            return;
    }
    
    http_response_code(200);
    echo json_encode($posts);
}

function handleCreatePost($post) {
    if(!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(array("message" => "Authentication required"));
        return;
    }
    
    $data = json_decode(file_get_contents("php://input"));
    
    if(!empty($data->content)) {
        if(strlen($data->content) > 144) {
            http_response_code(400);
            echo json_encode(array("message" => "Post too long (max 144 characters)"));
            return;
        }
        
        $post->user_id = $_SESSION['user_id'];
        $post->content = $data->content;
        
        if($post->create()) {
            http_response_code(201);
            echo json_encode(array("message" => "Post created successfully"));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to create post"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Post content required"));
    }
}
?>
