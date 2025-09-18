<?php
require_once __DIR__ . '/../config/database.php';

class PostLike {
    private $conn;
    private $table_name = "post_likes";

    public $id;
    public $user_id;
    public $post_id;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Toggle like on a post
    public function toggleLike() {
        // Check if like already exists
        $query = "SELECT id FROM " . $this->table_name . " WHERE user_id = :user_id AND post_id = :post_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":post_id", $this->post_id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            // Unlike the post
            return $this->unlikePost();
        } else {
            // Like the post
            return $this->likePost();
        }
    }

    // Like a post
    public function likePost() {
        $query = "INSERT INTO " . $this->table_name . " (user_id, post_id) VALUES (:user_id, :post_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":post_id", $this->post_id);
        return $stmt->execute();
    }

    // Unlike a post
    public function unlikePost() {
        $query = "DELETE FROM " . $this->table_name . " WHERE user_id = :user_id AND post_id = :post_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":post_id", $this->post_id);
        return $stmt->execute();
    }

    // Check if user likes a post
    public function isPostLiked($user_id, $post_id) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE user_id = :user_id AND post_id = :post_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":post_id", $post_id);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // Get user's liked posts
    public function getUserLikes() {
        $query = "
            SELECT p.id, p.content, p.created_at, u.username
            FROM posts p
            JOIN post_likes pl ON p.id = pl.post_id
            JOIN users u ON p.user_id = u.id
            WHERE pl.user_id = :user_id
            ORDER BY pl.created_at DESC
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // Get like count for a post
    public function getPostLikeCount($post_id) {
        $query = "SELECT COUNT(*) as like_count FROM " . $this->table_name . " WHERE post_id = :post_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":post_id", $post_id);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['like_count'];
    }
}
?>
