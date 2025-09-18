<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $username;
    public $email;
    public $password_hash;
    public $description;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create new user
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET username=:username, email=:email, password_hash=:password_hash, description=:description";

        $stmt = $this->conn->prepare($query);

        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->description = htmlspecialchars(strip_tags($this->description));

        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password_hash", $this->password_hash);
        $stmt->bindParam(":description", $this->description);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Check if username exists
    public function usernameExists() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE username = :username LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $this->username);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // Check if email exists
    public function emailExists() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = :email LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // Get user by username
    public function getUserByUsername() {
        $query = "SELECT id, username, email, password_hash, description, created_at FROM " . $this->table_name . " WHERE username = :username LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $this->username);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->email = $row['email'];
            $this->password_hash = $row['password_hash'];
            $this->description = $row['description'];
            $this->created_at = $row['created_at'];
            return true;
        }
        return false;
    }

    // Get user by ID
    public function getUserById() {
        $query = "SELECT id, username, email, description, created_at FROM " . $this->table_name . " WHERE id = :id LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->description = $row['description'];
            $this->created_at = $row['created_at'];
            return true;
        }
        return false;
    }

    // Verify password
    public function verifyPassword($password) {
        return password_verify($password, $this->password_hash);
    }

    // Get user stats (followers, following, posts count)
    public function getUserStats() {
        $stats = array();

        // Get followers count (users who like this user)
        $query = "SELECT COUNT(*) as followers FROM user_likes WHERE liked_user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->id);
        $stmt->execute();
        $stats['followers'] = $stmt->fetch(PDO::FETCH_ASSOC)['followers'];

        // Get following count (users this user likes)
        $query = "SELECT COUNT(*) as following FROM user_likes WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->id);
        $stmt->execute();
        $stats['following'] = $stmt->fetch(PDO::FETCH_ASSOC)['following'];

        // Get posts count
        $query = "SELECT COUNT(*) as posts FROM posts WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->id);
        $stmt->execute();
        $stats['posts'] = $stmt->fetch(PDO::FETCH_ASSOC)['posts'];

        return $stats;
    }

    // Search users by username
    public function searchUsers($query) {
        $searchQuery = "%" . $query . "%";
        $sql = "SELECT u.id, u.username, u.email, u.description, u.created_at,
                       COUNT(DISTINCT ul_followers.id) as follower_count,
                       COUNT(DISTINCT ul_following.id) as following_count,
                       COUNT(DISTINCT p.id) as posts_count
                FROM " . $this->table_name . " u
                LEFT JOIN user_likes ul_followers ON u.id = ul_followers.liked_user_id
                LEFT JOIN user_likes ul_following ON u.id = ul_following.user_id
                LEFT JOIN posts p ON u.id = p.user_id
                WHERE u.username LIKE :query 
                GROUP BY u.id, u.username, u.email, u.description, u.created_at
                ORDER BY u.username ASC 
                LIMIT 10";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":query", $searchQuery);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update basic profile fields for current user id
    public function updateProfile() {
        $query = "UPDATE " . $this->table_name . "
                  SET username = :username,
                      email = :email,
                      description = :description
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->description = htmlspecialchars(strip_tags($this->description));

        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }
}
?>
