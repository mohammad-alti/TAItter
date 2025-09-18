-- TAItter Database Schema
-- Twitter-like social media application

CREATE DATABASE IF NOT EXISTS taitter CHARACTER SET utf8 COLLATE utf8_unicode_ci;
USE taitter;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Posts table
CREATE TABLE posts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    content VARCHAR(144) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Hashtags table
CREATE TABLE hashtags (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tag VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Post hashtags relationship
CREATE TABLE post_hashtags (
    id INT PRIMARY KEY AUTO_INCREMENT,
    post_id INT NOT NULL,
    hashtag_id INT NOT NULL,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (hashtag_id) REFERENCES hashtags(id) ON DELETE CASCADE,
    UNIQUE KEY unique_post_hashtag (post_id, hashtag_id)
);

-- Mentions table (user mentions in posts)
CREATE TABLE mentions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    post_id INT NOT NULL,
    mentioned_user_id INT NOT NULL,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (mentioned_user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_post_mention (post_id, mentioned_user_id)
);

-- User follows hashtags
CREATE TABLE user_hashtag_follows (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    hashtag_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (hashtag_id) REFERENCES hashtags(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_hashtag_follow (user_id, hashtag_id)
);

-- User likes other users
CREATE TABLE user_likes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    liked_user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (liked_user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_like (user_id, liked_user_id)
);

-- Indexes for better performance
CREATE INDEX idx_posts_created_at ON posts(created_at DESC);
CREATE INDEX idx_posts_user_id ON posts(user_id);
CREATE INDEX idx_hashtags_tag ON hashtags(tag);
CREATE INDEX idx_mentions_mentioned_user ON mentions(mentioned_user_id);
CREATE INDEX idx_user_hashtag_follows_user ON user_hashtag_follows(user_id);
CREATE INDEX idx_user_likes_user ON user_likes(user_id);
CREATE INDEX idx_user_likes_liked ON user_likes(liked_user_id);
