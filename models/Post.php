<?php
require_once __DIR__ . '/../config/database.php';

class Post {
    private $conn;
    private $table_name = "posts";

    public $id;
    public $user_id;
    public $content;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create new post
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET user_id=:user_id, content=:content";

        $stmt = $this->conn->prepare($query);

        $this->content = htmlspecialchars(strip_tags($this->content));

        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":content", $this->content);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            $this->parseHashtagsAndMentions();
            return true;
        }
        return false;
    }

    // Parse hashtags and mentions from post content
    private function parseHashtagsAndMentions() {
        // Extract hashtags
        preg_match_all('/#(\w+)/', $this->content, $hashtag_matches);
        foreach($hashtag_matches[1] as $tag) {
            $this->addHashtag($tag);
        }

        // Extract mentions
        preg_match_all('/@(\w+)/', $this->content, $mention_matches);
        foreach($mention_matches[1] as $username) {
            $this->addMention($username);
        }
    }

    // Add hashtag to post
    private function addHashtag($tag) {
        // Insert or get hashtag
        $query = "INSERT IGNORE INTO hashtags (tag) VALUES (:tag)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":tag", $tag);
        $stmt->execute();

        // Get hashtag ID
        $query = "SELECT id FROM hashtags WHERE tag = :tag";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":tag", $tag);
        $stmt->execute();
        $hashtag_id = $stmt->fetch(PDO::FETCH_ASSOC)['id'];

        // Link post to hashtag
        $query = "INSERT IGNORE INTO post_hashtags (post_id, hashtag_id) VALUES (:post_id, :hashtag_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":post_id", $this->id);
        $stmt->bindParam(":hashtag_id", $hashtag_id);
        $stmt->execute();
    }

    // Add mention to post
    private function addMention($username) {
        // Check if user exists
        $query = "SELECT id FROM users WHERE username = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $mentioned_user_id = $stmt->fetch(PDO::FETCH_ASSOC)['id'];

            // Link post to mention
            $query = "INSERT IGNORE INTO mentions (post_id, mentioned_user_id) VALUES (:post_id, :mentioned_user_id)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":post_id", $this->id);
            $stmt->bindParam(":mentioned_user_id", $mentioned_user_id);
            $stmt->execute();
        }
    }

    // Get personalized feed for user
    public function getPersonalizedFeed($user_id, $limit = 50) {
        $query = "
            SELECT DISTINCT p.id, p.user_id, p.content, p.created_at, u.username,
                   COUNT(pl.id) as like_count,
                   CASE WHEN current_user_likes.id IS NOT NULL THEN 1 ELSE 0 END as is_liked
            FROM posts p
            JOIN users u ON p.user_id = u.id
            LEFT JOIN post_hashtags ph ON p.id = ph.post_id
            LEFT JOIN user_hashtag_follows uhf ON ph.hashtag_id = uhf.hashtag_id
            LEFT JOIN user_likes ul ON p.user_id = ul.liked_user_id
            LEFT JOIN mentions m ON p.id = m.post_id
            LEFT JOIN post_likes pl ON p.id = pl.post_id
            LEFT JOIN post_likes current_user_likes ON p.id = current_user_likes.post_id 
                AND current_user_likes.user_id = :user_id
            WHERE (
                uhf.user_id = :user_id OR
                ul.user_id = :user_id OR
                m.mentioned_user_id = :user_id
            )
            GROUP BY p.id, p.user_id, p.content, p.created_at, u.username
            ORDER BY p.created_at DESC
            LIMIT :limit
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // Get posts by user
    public function getPostsByUser($user_id, $limit = 50) {
        $query = "
            SELECT p.id, p.user_id, p.content, p.created_at, u.username,
                   COUNT(pl.id) as like_count,
                   CASE WHEN current_user_likes.id IS NOT NULL THEN 1 ELSE 0 END as is_liked
            FROM posts p
            JOIN users u ON p.user_id = u.id
            LEFT JOIN post_likes pl ON p.id = pl.post_id
            LEFT JOIN post_likes current_user_likes ON p.id = current_user_likes.post_id 
                AND current_user_likes.user_id = :current_user_id
            WHERE p.user_id = :user_id
            GROUP BY p.id, p.user_id, p.content, p.created_at, u.username
            ORDER BY p.created_at DESC
            LIMIT :limit
        ";

        $stmt = $this->conn->prepare($query);
        $current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":current_user_id", $current_user_id);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // Get posts by hashtag
    public function getPostsByHashtag($hashtag, $limit = 50) {
        $query = "
            SELECT p.id, p.user_id, p.content, p.created_at, u.username,
                   COUNT(pl.id) as like_count,
                   CASE WHEN current_user_likes.id IS NOT NULL THEN 1 ELSE 0 END as is_liked
            FROM posts p
            JOIN users u ON p.user_id = u.id
            JOIN post_hashtags ph ON p.id = ph.post_id
            JOIN hashtags h ON ph.hashtag_id = h.id
            LEFT JOIN post_likes pl ON p.id = pl.post_id
            LEFT JOIN post_likes current_user_likes ON p.id = current_user_likes.post_id 
                AND current_user_likes.user_id = :current_user_id
            WHERE h.tag = :hashtag
            GROUP BY p.id, p.user_id, p.content, p.created_at, u.username
            ORDER BY p.created_at DESC
            LIMIT :limit
        ";

        $stmt = $this->conn->prepare($query);
        $current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
        $stmt->bindParam(":hashtag", $hashtag);
        $stmt->bindParam(":current_user_id", $current_user_id);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // Get all posts (for general feed)
    public function getAllPosts($limit = 50) {
        $query = "
            SELECT p.id, p.user_id, p.content, p.created_at, u.username,
                   COUNT(pl.id) as like_count,
                   CASE WHEN current_user_likes.id IS NOT NULL THEN 1 ELSE 0 END as is_liked
            FROM posts p
            JOIN users u ON p.user_id = u.id
            LEFT JOIN post_likes pl ON p.id = pl.post_id
            LEFT JOIN post_likes current_user_likes ON p.id = current_user_likes.post_id 
                AND current_user_likes.user_id = :current_user_id
            GROUP BY p.id, p.user_id, p.content, p.created_at, u.username
            ORDER BY p.created_at DESC
            LIMIT :limit
        ";

        $stmt = $this->conn->prepare($query);
        $current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
        $stmt->bindParam(":current_user_id", $current_user_id);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
?>
