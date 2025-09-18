<?php
require_once __DIR__ . '/../config/database.php';

class UserLike {
    private $conn;
    private $table_name = "user_likes";

    public $id;
    public $user_id;
    public $liked_user_id;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Like a user
    public function likeUser() {
        $query = "INSERT IGNORE INTO " . $this->table_name . " (user_id, liked_user_id) VALUES (:user_id, :liked_user_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":liked_user_id", $this->liked_user_id);
        return $stmt->execute();
    }

    // Unlike a user
    public function unlikeUser() {
        $query = "DELETE FROM " . $this->table_name . " WHERE user_id = :user_id AND liked_user_id = :liked_user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":liked_user_id", $this->liked_user_id);
        return $stmt->execute();
    }

    // Check if user likes another user
    public function isUserLiked($user_id, $liked_user_id) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE user_id = :user_id AND liked_user_id = :liked_user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":liked_user_id", $liked_user_id);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // Get user's liked users
    public function getUserLikedUsers($user_id) {
        $query = "
            SELECT u.id, u.username, u.description, ul.created_at
            FROM users u
            JOIN user_likes ul ON u.id = ul.liked_user_id
            WHERE ul.user_id = :user_id
            ORDER BY ul.created_at DESC
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // Get users who like a specific user
    public function getUserFollowers($liked_user_id) {
        $query = "
            SELECT u.id, u.username, u.description, ul.created_at
            FROM users u
            JOIN user_likes ul ON u.id = ul.user_id
            WHERE ul.liked_user_id = :liked_user_id
            ORDER BY ul.created_at DESC
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":liked_user_id", $liked_user_id);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
?>
