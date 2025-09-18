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

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        handleCheckAuth($user);
        break;
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        
        if(isset($data->action)) {
            switch($data->action) {
                case 'register':
                    handleRegister($user, $data);
                    break;
                case 'login':
                    handleLogin($user, $data);
                    break;
                default:
                    http_response_code(400);
                    echo json_encode(array("message" => "Invalid action"));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Action not specified"));
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed"));
}

function handleCheckAuth($user) {
    session_start();
    
    // Debug logging
    error_log("Checking auth status. Session ID: " . session_id());
    error_log("Session data: " . json_encode($_SESSION));
    
    if(isset($_SESSION['user_id'])) {
        $user->id = $_SESSION['user_id'];
        if($user->getUserById()) {
            http_response_code(200);
            echo json_encode(array(
                "authenticated" => true,
                "user" => array(
                    "id" => $user->id,
                    "username" => $user->username,
                    "email" => $user->email,
                    "description" => $user->description
                )
            ));
        } else {
            // User ID in session but user not found in database
            error_log("User ID in session but user not found in database");
            session_destroy();
            http_response_code(401);
            echo json_encode(array("authenticated" => false));
        }
    } else {
        error_log("No user_id in session");
        http_response_code(401);
        echo json_encode(array("authenticated" => false));
    }
}

function handleRegister($user, $data) {
    // Debug logging
    error_log("Registration attempt: " . json_encode($data));
    
    if(!empty($data->username) && !empty($data->email) && !empty($data->password)) {
        $user->username = $data->username;
        $user->email = $data->email;
        $user->description = isset($data->description) ? $data->description : "";
        $user->password_hash = password_hash($data->password, PASSWORD_DEFAULT);

        // Check if username already exists
        if($user->usernameExists()) {
            error_log("Username already exists: " . $data->username);
            http_response_code(400);
            echo json_encode(array("message" => "Username already exists"));
            return;
        }

        // Check if email already exists
        if($user->emailExists()) {
            error_log("Email already exists: " . $data->email);
            http_response_code(400);
            echo json_encode(array("message" => "Email already exists"));
            return;
        }

        if($user->create()) {
            error_log("User created successfully: " . $data->username);
            http_response_code(201);
            echo json_encode(array("message" => "User created successfully"));
        } else {
            error_log("Failed to create user: " . $data->username);
            http_response_code(503);
            echo json_encode(array("message" => "Unable to create user"));
        }
    } else {
        error_log("Incomplete registration data");
        http_response_code(400);
        echo json_encode(array("message" => "Unable to create user. Data is incomplete."));
    }
}

function handleLogin($user, $data) {
    // Debug logging
    error_log("Login attempt for username: " . $data->username);
    
    if(!empty($data->username) && !empty($data->password)) {
        $user->username = $data->username;
        
        if($user->getUserByUsername()) {
            error_log("User found in database: " . $user->username . " (ID: " . $user->id . ")");
            
            if($user->verifyPassword($data->password)) {
                // Start session
                session_start();
                $_SESSION['user_id'] = $user->id;
                $_SESSION['username'] = $user->username;
                
                // Debug logging
                error_log("Login successful. Session ID: " . session_id());
                error_log("Session data after login: " . json_encode($_SESSION));
                
                http_response_code(200);
                echo json_encode(array(
                    "message" => "Login successful",
                    "user" => array(
                        "id" => $user->id,
                        "username" => $user->username,
                        "email" => $user->email,
                        "description" => $user->description
                    )
                ));
            } else {
                error_log("Invalid password for user: " . $data->username);
                http_response_code(401);
                echo json_encode(array("message" => "Invalid password"));
            }
        } else {
            error_log("User not found: " . $data->username);
            http_response_code(401);
            echo json_encode(array("message" => "User not found"));
        }
    } else {
        error_log("Incomplete login data - username: " . (isset($data->username) ? $data->username : 'empty') . ", password: " . (isset($data->password) ? 'provided' : 'empty'));
        http_response_code(400);
        echo json_encode(array("message" => "Unable to login. Data is incomplete."));
    }
}
?>
