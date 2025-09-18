-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 18, 2025 at 01:13 PM
-- Server version: 10.4.6-MariaDB
-- PHP Version: 7.3.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `taitter`
--

-- --------------------------------------------------------

--
-- Table structure for table `hashtags`
--

CREATE TABLE `hashtags` (
  `id` int(11) NOT NULL,
  `tag` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hashtags`
--

INSERT INTO `hashtags` (`id`, `tag`, `created_at`) VALUES
(1, 'yo', '2025-09-18 07:11:23');

-- --------------------------------------------------------

--
-- Table structure for table `mentions`
--

CREATE TABLE `mentions` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `mentioned_user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` varchar(144) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `post_hashtags`
--

CREATE TABLE `post_hashtags` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `hashtag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `post_likes`
--

CREATE TABLE `post_likes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `description`, `created_at`, `updated_at`) VALUES
(1, 'alice_dev', 'alice@example.com', '$2y$10$RjHrG90IA3TqoG0dC239muYCEE0x3131MwvN2kXngVkdvbx9VLDW2', 'Full-stack developer passionate about clean code', '2025-09-18 05:21:57', '2025-09-18 06:23:20'),
(2, 'bob_designer', 'bob@example.com', '$2y$10$Y9IOqIY4cuAbVUmSPE.7TusTa5ZLgUSCrvFt5DcWjLOjb4IXdkeiK', 'UI/UX designer creating beautiful experiences', '2025-09-18 05:21:57', '2025-09-18 06:23:20'),
(3, 'charlie_ai', 'charlie@example.com', '$2y$10$AU4YictQTz7cIbDn32IEieABLeVy5TS6J6xF7JMuY/B22y/tVSQE.', 'AI researcher exploring the future of technology', '2025-09-18 05:21:57', '2025-09-18 06:23:20'),
(4, 'diana_tech', 'diana@example.com', '$2y$10$TJPCvRMPS4kXDNCzcUFgEuyAlt.6Sq0UlYvynWmco.GvelO7xaldi', 'Tech journalist covering the latest innovations', '2025-09-18 05:21:57', '2025-09-18 06:23:20'),
(5, 'eve_startup', 'eve@example.com', '$2y$10$QGMcGNlJcOQMojTa30kYruJv5Zhjq9BYHXM3.b5TE7qdsPFnBDAgy', 'Startup founder building the next big thing', '2025-09-18 05:21:57', '2025-09-18 06:23:20'),
(6, 'testuser1758173128', 'test1758173128@example.com', '$2y$10$LrkMP8PLGDXLMK2rXeeyPuOITrmaSugAfuAVtgi6MbI8PM.WQtpai', 'Test user', '2025-09-18 05:25:28', '2025-09-18 06:23:20'),
(7, 'testuser1758173129', 'test1758173129@example.com', '$2y$10$7kFXwLDB1hpL40N4uLwHceDcdJQIaA/g1QUub6gVFKu24xfgAf2HG', 'Test user', '2025-09-18 05:25:29', '2025-09-18 06:23:20'),
(8, 'testuser1758173130', 'test1758173130@example.com', '$2y$10$BIYFal0wJ4sCoCicRXjHW.sLyv4IWRKmMevivNzRTbsCiHohX3Mau', 'Test user', '2025-09-18 05:25:30', '2025-09-18 06:23:21'),
(9, 'testuser1758173131', 'test1758173131@example.com', '$2y$10$xpnyfQ4RgeBWoz/9Czf0curFzJ6vJDUVtcv48mhUg.CKxlLyO6prm', 'Test user', '2025-09-18 05:25:31', '2025-09-18 06:23:21'),
(10, 'testuser1758173240', 'test1758173240@example.com', '$2y$10$pcq7FEEg7sIZKuKYe.yKY.rukQKDPkRgz8YOnclXGMNBNwAkiS4Em', 'Test user', '2025-09-18 05:27:20', '2025-09-18 06:23:21'),
(11, 'testuser1758173322', 'test1758173322@example.com', '$2y$10$hB8U2V8OHTc9nK6/Vr0ceuoSU..Tn7i4UNU5xWITcJTdFmoAATIQC', 'Test user', '2025-09-18 05:28:42', '2025-09-18 06:23:21'),
(12, 'testuser1758173331', 'test1758173331@example.com', '$2y$10$elnmYCaEuXzxCH.qb.zuWuBeyN3GsrnR/H3PvgGYz8ed5URRwWM0i', 'Test user', '2025-09-18 05:28:51', '2025-09-18 06:23:21'),
(13, 'mohammad', 'mohammad@gmail.com', '$2y$10$CQMjDJauXoF437kfDzSla.VgC8/v/cPD4BJDTS7IsRZYjctv4hzJq', '', '2025-09-18 05:29:21', '2025-09-18 06:23:21'),
(14, 'testuser1758173639816', 'test1758173639816@example.com', '$2y$10$f.p/lcasaTFB8fP2rN.x7eE5bbUq.83jq29DgsWtMU.5z4PrScbkq', 'Test user', '2025-09-18 05:33:59', '2025-09-18 06:23:21'),
(15, 'testuser1758173656440', 'test1758173656441@example.com', '$2y$10$yn2bE1GR.B31ZEiQr/GRsOajGXnoDcMqbg/nW/GyU8YlJoksTXdmi', 'Test user', '2025-09-18 05:34:16', '2025-09-18 06:23:21'),
(16, 'testuser1758173725481', 'test1758173725481@example.com', '$2y$10$4oaDADy4NWEJ3Y9H68cTXu0BoS1KY2YAeoxaqly2JHRMC.i2zjJ4W', 'Test user', '2025-09-18 05:35:25', '2025-09-18 06:23:22'),
(17, 'testuser1758174136957', 'test1758174136957@example.com', '$2y$10$mPpnwwgaPGs8bCEpwg9gpehak72n4DMdJ.aM549zMJ./.EkFUIHky', 'Test user', '2025-09-18 05:42:17', '2025-09-18 06:23:22'),
(18, 'testuser1758174170', 'test1758174170@example.com', '$2y$10$Lfo1POf0IgRd49v5SBam9uHPoW6R8NRB2tY2wBwC3hVwlfqINxVqS', 'Test user', '2025-09-18 05:42:50', '2025-09-18 06:23:22'),
(19, 'apitest1758174170', 'apitest1758174170@example.com', '$2y$10$js2Sb1SwVC2l0DIOXcklHegiEUzjWWRPhhfMblHfTVjTtOObEr6ga', 'API test user', '2025-09-18 05:42:50', '2025-09-18 06:23:22'),
(20, 'testuser1758174208301', 'test1758174208301@example.com', '$2y$10$i8dII6jANLKzgAzj9LTBsO9OJ9.xBwmxvApWbttv556RZEIQsSrm6', 'Test user', '2025-09-18 05:43:28', '2025-09-18 06:23:22'),
(21, 'testuser1758174573178', 'test1758174573178@example.com', '$2y$10$JUDmVh3JkunROe3B566Tauhxa2HeoK6v1TtW8MZN9IU5.d9223lA.', 'Test user for debugging', '2025-09-18 05:49:33', '2025-09-18 06:23:22'),
(22, 'testposter1758174576409', 'testposter1758174576409@example.com', '$2y$10$1bohvyXmYrm3vz.pFE8I3O/I1hitghh5T/R19lgKju.xSyBiBvvGW', 'Test poster for debugging', '2025-09-18 05:49:36', '2025-09-18 06:23:22'),
(23, 'banad1', 'banad1@gmail.com', '$2y$10$IvKBcSGAzUimZcOunbfVGO1.xgp52cCodkXuMfQFmiSBhUJhNIOZO', '', '2025-09-18 07:08:07', '2025-09-18 07:08:07');

-- --------------------------------------------------------

--
-- Table structure for table `user_hashtag_follows`
--

CREATE TABLE `user_hashtag_follows` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `hashtag_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_hashtag_follows`
--

INSERT INTO `user_hashtag_follows` (`id`, `user_id`, `hashtag_id`, `created_at`) VALUES
(14, 13, 1, '2025-09-18 07:30:24'),
(17, 23, 1, '2025-09-18 10:56:38');

-- --------------------------------------------------------

--
-- Table structure for table `user_likes`
--

CREATE TABLE `user_likes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `liked_user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_likes`
--

INSERT INTO `user_likes` (`id`, `user_id`, `liked_user_id`, `created_at`) VALUES
(1, 1, 2, '2025-09-18 05:21:57'),
(2, 1, 3, '2025-09-18 05:21:57'),
(3, 1, 4, '2025-09-18 05:21:57'),
(5, 13, 23, '2025-09-18 10:52:50'),
(7, 23, 13, '2025-09-18 10:56:51');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `hashtags`
--
ALTER TABLE `hashtags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tag` (`tag`),
  ADD KEY `idx_hashtags_tag` (`tag`);

--
-- Indexes for table `mentions`
--
ALTER TABLE `mentions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_post_mention` (`post_id`,`mentioned_user_id`),
  ADD KEY `idx_mentions_mentioned_user` (`mentioned_user_id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_posts_created_at` (`created_at`),
  ADD KEY `idx_posts_user_id` (`user_id`);

--
-- Indexes for table `post_hashtags`
--
ALTER TABLE `post_hashtags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_post_hashtag` (`post_id`,`hashtag_id`),
  ADD KEY `hashtag_id` (`hashtag_id`);

--
-- Indexes for table `post_likes`
--
ALTER TABLE `post_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_like` (`user_id`,`post_id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_hashtag_follows`
--
ALTER TABLE `user_hashtag_follows`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_hashtag_follow` (`user_id`,`hashtag_id`),
  ADD KEY `hashtag_id` (`hashtag_id`),
  ADD KEY `idx_user_hashtag_follows_user` (`user_id`);

--
-- Indexes for table `user_likes`
--
ALTER TABLE `user_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_like` (`user_id`,`liked_user_id`),
  ADD KEY `idx_user_likes_user` (`user_id`),
  ADD KEY `idx_user_likes_liked` (`liked_user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `hashtags`
--
ALTER TABLE `hashtags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `mentions`
--
ALTER TABLE `mentions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `post_hashtags`
--
ALTER TABLE `post_hashtags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `post_likes`
--
ALTER TABLE `post_likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `user_hashtag_follows`
--
ALTER TABLE `user_hashtag_follows`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `user_likes`
--
ALTER TABLE `user_likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `mentions`
--
ALTER TABLE `mentions`
  ADD CONSTRAINT `mentions_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mentions_ibfk_2` FOREIGN KEY (`mentioned_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `post_hashtags`
--
ALTER TABLE `post_hashtags`
  ADD CONSTRAINT `post_hashtags_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `post_hashtags_ibfk_2` FOREIGN KEY (`hashtag_id`) REFERENCES `hashtags` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `post_likes`
--
ALTER TABLE `post_likes`
  ADD CONSTRAINT `post_likes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `post_likes_ibfk_2` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_hashtag_follows`
--
ALTER TABLE `user_hashtag_follows`
  ADD CONSTRAINT `user_hashtag_follows_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_hashtag_follows_ibfk_2` FOREIGN KEY (`hashtag_id`) REFERENCES `hashtags` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_likes`
--
ALTER TABLE `user_likes`
  ADD CONSTRAINT `user_likes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_likes_ibfk_2` FOREIGN KEY (`liked_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
