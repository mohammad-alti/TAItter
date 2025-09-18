<?php
header("Content-Type: application/json; charset=UTF-8");

// Simple test API
echo json_encode([
    "status" => "success",
    "message" => "API is working",
    "method" => $_SERVER['REQUEST_METHOD'],
    "timestamp" => date('Y-m-d H:i:s')
]);
?>
