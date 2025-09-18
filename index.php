<?php
// Start session for authentication
session_start();

// Check if user is logged in
$currentUser = null;
if (isset($_SESSION['user_id'])) {
    require_once 'config/database.php';
    require_once 'models/User.php';
    
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $user = new User($db);
        $user->id = $_SESSION['user_id'];
        if ($user->getUserById()) {
            $currentUser = [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'description' => $user->description
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TAItter - Twitter is dead, long live TAItter!</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <i class="fas fa-bolt"></i>
                <span>TAItter</span>
            </div>
            <div class="nav-menu" id="navMenu">
                <a href="#" class="nav-link active" data-page="home">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </a>
                <a href="#" class="nav-link" data-page="search">
                    <i class="fas fa-search"></i>
                    <span>Search</span>
                </a>
                <a href="#" class="nav-link" data-page="profile">
                    <i class="fas fa-user"></i>
                    <span>Profile</span>
                </a>
                <a href="#" class="nav-link" data-page="settings">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </div>
            <div class="nav-user" id="navUser">
                <div class="user-menu">
                    <button class="user-btn" id="userBtn">
                        <i class="fas fa-user-circle"></i>
                        <span id="userName"><?php echo $currentUser ? htmlspecialchars($currentUser['username']) : 'Guest'; ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="user-dropdown" id="userDropdown">
                        <?php if ($currentUser): ?>
                            <a href="#" id="logoutBtn">Logout</a>
                        <?php else: ?>
                            <a href="#" id="loginBtn">Login</a>
                            <a href="#" id="registerBtn">Register</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="nav-toggle" id="navToggle">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Home Page -->
        <div class="page active" id="homePage">
            <div class="container">
                <div class="feed-container">
                    <!-- Post Creation -->
                    <?php if ($currentUser): ?>
                    <div class="post-create" id="postCreate">
                        <div class="post-create-header">
                            <h3>Create New Post</h3>
                            <button class="close-btn" id="closePostCreate">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <form id="postForm">
                            <div class="form-group">
                                <textarea id="postContent" placeholder="What's happening? (max 144 characters)" maxlength="144" required></textarea>
                                <div class="char-count">
                                    <span id="charCount">0</span>/144
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i>
                                    Post
                                </button>
                            </div>
                        </form>
                    </div>
                    <?php endif; ?>

                    <!-- Feed -->
                    <div class="feed" id="feed">
                        <div class="feed-header">
                            <h2>Your Feed</h2>
                            <?php if ($currentUser): ?>
                            <button class="btn btn-primary" id="newPostBtn">
                                <i class="fas fa-plus"></i>
                                New Post
                            </button>
                            <?php endif; ?>
                        </div>
                        <div class="posts" id="posts">
                            <!-- Posts will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Page -->
        <div class="page" id="searchPage">
            <div class="container">
                <div class="search-container">
                    <div class="search-header">
                        <h2>Search</h2>
                        <div class="search-tabs">
                            <button class="tab-btn active" data-tab="hashtag">Hashtags</button>
                            <button class="tab-btn" data-tab="user">Users</button>
                        </div>
                    </div>
                    
                    <div class="search-content">
                        <div class="search-form">
                            <div class="form-group">
                                <input type="text" id="searchInput" placeholder="Search hashtags or users...">
                                <button class="btn btn-primary" id="searchBtn">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="search-results" id="searchResults">
                            <!-- Search results will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Page -->
        <div class="page" id="profilePage">
            <div class="container">
                <div class="profile-container">
                    <div class="profile-header">
                        <h2 id="profilePageTitle">Your Profile</h2>
                    </div>
                    
                    <div class="profile-content">
                        <div class="profile-info" id="profileInfo">
                            <?php if ($currentUser): ?>
                            <div class="profile-header">
                                <div class="user-avatar" style="width: 60px; height: 60px; font-size: 24px;">
                                    <?php echo strtoupper(substr($currentUser['username'], 0, 1)); ?>
                                </div>
                                <div>
                                    <h2>@<?php echo htmlspecialchars($currentUser['username']); ?></h2>
                                    <p><?php echo htmlspecialchars($currentUser['description'] ?: 'No description'); ?></p>
                                    <p class="join-date">Joined <?php echo date('F j, Y', strtotime($currentUser['created_at'] ?? 'now')); ?></p>
                                </div>
                            </div>
                            <?php else: ?>
                            <p>Please log in to view your profile.</p>
                            <?php endif; ?>
                        </div>
                        
                        <!-- User Posts Section -->
                        <div class="profile-posts">
                            <h3>Posts</h3>
                            <div class="posts" id="profilePosts">
                                <!-- User posts will be loaded here -->
                            </div>
                        </div>
                        
                        <div class="profile-tabs">
                            <button class="tab-btn active" data-tab="followed-hashtags">Followed Hashtags</button>
                            <button class="tab-btn" data-tab="liked-users">Liked Users</button>
                        </div>
                        
                        <div class="profile-tab-content">
                            <div class="tab-pane active" id="followed-hashtags">
                                <div class="hashtag-list" id="hashtagList">
                                    <!-- Followed hashtags will be loaded here -->
                                </div>
                            </div>
                            
                            <div class="tab-pane" id="liked-users">
                                <div class="user-list" id="userList">
                                    <!-- Liked users will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings Page -->
        <div class="page" id="settingsPage">
            <div class="container">
                <div class="settings-container">
                    <div class="settings-header">
                        <h2>Settings</h2>
                    </div>
                    
                    <div class="settings-content">
                        <div class="settings-section">
                            <h3>Account Information</h3>
                            <form id="settingsForm">
                                <div class="form-group">
                                    <label for="settingsUsername">Username</label>
                                    <input type="text" id="settingsUsername" value="<?php echo $currentUser ? htmlspecialchars($currentUser['username']) : ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="settingsEmail">Email</label>
                                    <input type="email" id="settingsEmail" value="<?php echo $currentUser ? htmlspecialchars($currentUser['email']) : ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label for="settingsDescription">Description</label>
                                    <textarea id="settingsDescription" placeholder="Tell us about yourself..."><?php echo $currentUser ? htmlspecialchars($currentUser['description']) : ''; ?></textarea>
                                </div>
                                <?php if ($currentUser): ?>
                                <div class="form-actions">
                                    <button type="button" class="btn btn-primary" id="saveProfileBtn">Save</button>
                                </div>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Login Modal -->
    <div class="modal" id="loginModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Login to TAItter</h3>
                <button class="close-btn" id="closeLoginModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="loginForm">
                <div class="form-group">
                    <label for="loginUsername">Username</label>
                    <input type="text" id="loginUsername" required>
                </div>
                <div class="form-group">
                    <label for="loginPassword">Password</label>
                    <input type="password" id="loginPassword" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Login</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Register Modal -->
    <div class="modal" id="registerModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Join TAItter</h3>
                <button class="close-btn" id="closeRegisterModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="registerForm">
                <div class="form-group">
                    <label for="registerUsername">Username</label>
                    <input type="text" id="registerUsername" required>
                </div>
                <div class="form-group">
                    <label for="registerEmail">Email</label>
                    <input type="email" id="registerEmail" required>
                </div>
                <div class="form-group">
                    <label for="registerPassword">Password</label>
                    <input type="password" id="registerPassword" required>
                </div>
                <div class="form-group">
                    <label for="registerDescription">Description (optional)</label>
                    <textarea id="registerDescription" placeholder="Tell us about yourself..."></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Register</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Loading Spinner -->
    <div class="loading" id="loading">
        <div class="spinner"></div>
    </div>

    <!-- Toast Notifications -->
    <div class="toast-container" id="toastContainer"></div>

    <script>
        // Pass current user data to JavaScript
        window.currentUser = <?php echo json_encode($currentUser); ?>;
    </script>
    <script src="assets/js/app.js?v=<?php echo time(); ?>"></script>
</body>
</html>
