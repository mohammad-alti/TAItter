<?php
require_once __DIR__ . '/../config/database.php';

class Hashtag {
    private $conn;
    private $table_name = "hashtags";

    public $id;
    public $tag;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Follow hashtag
    public function followHashtag($user_id, $hashtag_id) {
        $query = "INSERT IGNORE INTO user_hashtag_follows (user_id, hashtag_id) VALUES (:user_id, :hashtag_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":hashtag_id", $hashtag_id);
        return $stmt->execute();
    }

    // Unfollow hashtag
    public function unfollowHashtag($user_id, $hashtag_id) {
        $query = "DELETE FROM user_hashtag_follows WHERE user_id = :user_id AND hashtag_id = :hashtag_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":hashtag_id", $hashtag_id);
        return $stmt->execute();
    }

    // Get hashtag by tag name
    public function getHashtagByTag() {
        $query = "SELECT id, tag, created_at FROM " . $this->table_name . " WHERE tag = :tag LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":tag", $this->tag);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->created_at = $row['created_at'];
            return true;
        }
        return false;
    }

    // Get user's followed hashtags
    public function getUserFollowedHashtags($user_id) {
        $query = "
            SELECT h.id, h.tag, h.created_at
            FROM hashtags h
            JOIN user_hashtag_follows uhf ON h.id = uhf.hashtag_id
            WHERE uhf.user_id = :user_id
            ORDER BY uhf.created_at DESC
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // Check if user follows hashtag
    public function isUserFollowingHashtag($user_id, $hashtag_id) {
        $query = "SELECT id FROM user_hashtag_follows WHERE user_id = :user_id AND hashtag_id = :hashtag_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":hashtag_id", $hashtag_id);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // Get popular hashtags
    public function getPopularHashtags($limit = 20) {
        $query = "
            SELECT h.id, h.tag, COUNT(ph.post_id) as post_count
            FROM hashtags h
            LEFT JOIN post_hashtags ph ON h.id = ph.hashtag_id
            GROUP BY h.id, h.tag
            ORDER BY post_count DESC, h.tag ASC
            LIMIT :limit
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
?>
