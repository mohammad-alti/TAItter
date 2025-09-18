// TAItter - Modern Twitter-like Application
class TAItterApp {
    constructor() {
        this.currentUser = null;
        this.currentPage = 'home';
        this.viewedUserProfile = null; // Store the profile of the user being viewed
        this.pendingProfileUsername = null; // Username to load after navigation
        this.init();
    }

    init() {
        this.setupEventListeners();
        // Router: handle back/forward
        window.addEventListener('popstate', () => {
            this.routeFromUrl();
        });
        // Check if user is already logged in from PHP
        console.log('Initializing app, window.currentUser:', window.currentUser);
        if (window.currentUser) {
            this.currentUser = window.currentUser;
            console.log('Current user set to:', this.currentUser);
            this.updateUI();
        } else {
            console.log('No current user found in window.currentUser');
        }
        // Initial route
        this.routeFromUrl();
        
        // Test posts container after a short delay
        setTimeout(() => {
            const postsContainer = document.getElementById('posts');
            const profilePostsContainer = document.getElementById('profilePosts');
            console.log('Home posts container test - found:', !!postsContainer);
            console.log('Profile posts container test - found:', !!profilePostsContainer);
            
            if (postsContainer) {
                console.log('Home posts container element:', postsContainer);
                console.log('Home posts container visible:', postsContainer.offsetParent !== null);
            }
            
            if (profilePostsContainer) {
                console.log('Profile posts container element:', profilePostsContainer);
                console.log('Profile posts container visible:', profilePostsContainer.offsetParent !== null);
            }
        }, 1000);
    }

    // --- Simple query-string router ---
    routeFromUrl() {
        const params = new URLSearchParams(window.location.search);
        const page = params.get('page') || 'home';
        console.log('Routing to page:', page, 'with params:', Object.fromEntries(params));

        // Handle profile FIRST to avoid any search auto-load overriding it
        if (page === 'profile') {
            const username = params.get('username');
            console.log('Profile page requested for username:', username);
            this.navigateToPage('profile');
            if (username) {
                // Set the pending username and load the profile
                this.pendingProfileUsername = username;
                this.loadProfile();
            } else {
                this.loadProfile();
            }
            return;
        }

        if (page === 'search') {
            const q = params.get('q') || '';
            const tab = params.get('tab') || 'hashtag';
            this.navigateToPage('search');
            // Activate tab
            document.querySelectorAll('.search-tabs .tab-btn').forEach(btn => btn.classList.remove('active'));
            const tabBtn = document.querySelector(`.search-tabs .tab-btn[data-tab="${tab}"]`);
            if (tabBtn) tabBtn.classList.add('active');
            const input = document.getElementById('searchInput');
            if (input) input.value = q;
            if (q) {
                // Auto-detect tab if not specified
                if (!params.get('tab') && !q.startsWith('#') && !q.startsWith('@') && /^[a-zA-Z0-9_]+$/.test(q) && q.length > 2) {
                    document.querySelectorAll('.search-tabs .tab-btn').forEach(btn => btn.classList.remove('active'));
                    document.querySelector('[data-tab="user"]').classList.add('active');
                }
                this.handleSearch();
            } else if (tab === 'hashtag') this.loadPopularHashtags();
            return;
        }

        // default home
        this.navigateToPage('home');
    }

    pushRoute(page, extras = {}) {
        const params = new URLSearchParams();
        if (page && page !== 'home') params.set('page', page);
        Object.keys(extras).forEach(k => {
            if (extras[k] !== undefined && extras[k] !== null && `${extras[k]}` !== '') {
                params.set(k, extras[k]);
            }
        });
        const qs = params.toString();
        const url = qs ? `?${qs}` : '/TAItter/';
        if (window.location.search !== `?${qs}`) {
            history.pushState({}, '', url);
        }
    }

    setupEventListeners() {
        // Navigation
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const page = e.currentTarget.dataset.page;
                this.navigateToPage(page);
            });
        });

        // Mobile navigation
        const navToggle = document.getElementById('navToggle');
        const navMenu = document.getElementById('navMenu');
        if (navToggle && navMenu) {
            navToggle.addEventListener('click', () => {
                navMenu.classList.toggle('active');
                navToggle.classList.toggle('active');
            });
        }

        // User dropdown
        const userBtn = document.getElementById('userBtn');
        const userDropdown = document.getElementById('userDropdown');
        if (userBtn && userDropdown) {
            userBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                userDropdown.classList.toggle('show');
            });

            document.addEventListener('click', () => {
                userDropdown.classList.remove('show');
            });
        }

        // Auth buttons
        const loginBtn = document.getElementById('loginBtn');
        if (loginBtn) {
            loginBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.showModal('loginModal');
            });
        }

        const registerBtn = document.getElementById('registerBtn');
        if (registerBtn) {
            registerBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.showModal('registerModal');
            });
        }

        const logoutBtn = document.getElementById('logoutBtn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.logout();
            });
        }

        // Modal close buttons
        document.querySelectorAll('.close-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const modal = e.target.closest('.modal');
                this.hideModal(modal.id);
            });
        });

        // Settings Save button
        const saveBtn = document.getElementById('saveProfileBtn');
        if (saveBtn) {
            saveBtn.addEventListener('click', () => this.saveProfile());
        }

        // Forms
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleLogin();
            });
        }

        const registerForm = document.getElementById('registerForm');
        if (registerForm) {
            registerForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleRegister();
            });
        }

        const postForm = document.getElementById('postForm');
        if (postForm) {
            postForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleCreatePost();
            });
        }

        // Post creation
        const newPostBtn = document.getElementById('newPostBtn');
        if (newPostBtn) {
            newPostBtn.addEventListener('click', () => {
                this.showPostCreate();
            });
        }

        const closePostCreate = document.getElementById('closePostCreate');
        if (closePostCreate) {
            closePostCreate.addEventListener('click', () => {
                this.hidePostCreate();
            });
        }

        // Character count
        const postContent = document.getElementById('postContent');
        const charCount = document.getElementById('charCount');
        if (postContent && charCount) {
            postContent.addEventListener('input', () => {
                charCount.textContent = postContent.value.length;
            });
        }

        // Search
        const searchBtn = document.getElementById('searchBtn');
        if (searchBtn) {
            searchBtn.addEventListener('click', () => {
                this.handleSearch();
            });
        }

        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    this.handleSearch();
                }
            });
        }

        // Search tabs
        document.querySelectorAll('.search-tabs .tab-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                document.querySelectorAll('.search-tabs .tab-btn').forEach(b => b.classList.remove('active'));
                const tabBtn = e.currentTarget;
                tabBtn.classList.add('active');
                if (this.currentPage === 'search') {
                    this.pushRoute('search', {
                        q: (document.getElementById('searchInput')?.value || '').trim(),
                        tab: tabBtn.dataset.tab
                    });
                }
            });
        });

        // Profile tabs
        document.querySelectorAll('.profile-tabs .tab-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                document.querySelectorAll('.profile-tabs .tab-btn').forEach(b => b.classList.remove('active'));
                e.target.classList.add('active');
                
                const tab = e.target.dataset.tab;
                document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
                document.getElementById(tab).classList.add('active');
                
                if (tab === 'followed-hashtags') {
                    this.loadFollowedHashtags();
                } else if (tab === 'liked-users') {
                    this.loadLikedUsers();
                }
            });
        });
    }

    async checkAuthStatus() {
        try {
            const response = await fetch('api/auth.php', {
                method: 'GET',
                credentials: 'include'
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data.authenticated && data.user) {
                    this.currentUser = data.user;
                    this.updateUI();
                }
            }
        } catch (error) {
            console.log('Not authenticated:', error);
        }
    }

    async loadInitialData() {
        if (this.currentUser) {
            await this.loadFeed();
        }
    }

    navigateToPage(page) {
        console.log('navigateToPage called with page:', page);
        // Hide all pages
        document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
        
        // Show selected page
        const pageElement = document.getElementById(page + 'Page');
        console.log('Looking for page element with ID:', page + 'Page');
        console.log('Found page element:', pageElement);
        if (pageElement) {
            pageElement.classList.add('active');
            console.log('Page element activated');
        } else {
            console.error('Page element not found:', page + 'Page');
        }
        
        // Update navigation
        document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
        document.querySelector(`[data-page="${page}"]`).classList.add('active');
        
        this.currentPage = page;
        
        // Clear viewed user profile when navigating away from profile page
        if (page !== 'profile') {
            this.viewedUserProfile = null;
        }
        
        // Load page-specific data
        switch(page) {
            case 'home':
                this.pushRoute('home');
                this.loadFeed();
                break;
            case 'search': {
                // Only load popular hashtags if the Hashtags tab is currently active
                const activeTabBtn = document.querySelector('.search-tabs .tab-btn.active');
                const activeTab = activeTabBtn ? activeTabBtn.dataset.tab : 'hashtag';
                if (activeTab === 'hashtag') {
                    this.loadPopularHashtags();
                }
                this.pushRoute('search', {
                    q: (document.getElementById('searchInput')?.value || '').trim(),
                    tab: activeTab
                });
                break;
            }
            case 'profile':
                console.log('Loading profile page...');
                this.pushRoute('profile');
                this.loadProfile();
                break;
        }
    }

    async loadFeed() {
        this.showLoading();
        try {
            const response = await fetch('api/posts.php?action=feed');
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const posts = await response.json();
            this.displayPosts(posts);
        } catch (error) {
            console.error('Error loading feed:', error);
            this.showToast('Error loading feed: ' + error.message, 'error');
        } finally {
            this.hideLoading();
        }
    }

    async loadPopularHashtags() {
        try {
            const response = await fetch('api/hashtags.php?action=popular');
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const hashtags = await response.json();
            this.displaySearchResults(hashtags, 'hashtag');
        } catch (error) {
            console.error('Error loading hashtags:', error);
            this.showToast('Error loading hashtags: ' + error.message, 'error');
        }
    }

    async loadProfile() {
        console.log('loadProfile called, pendingProfileUsername:', this.pendingProfileUsername);
        // If we have a pending username, load that user's profile now
        if (this.pendingProfileUsername) {
            try {
                const u = this.pendingProfileUsername;
                console.log('Loading profile for pending username:', u);
                this.pendingProfileUsername = null;
                const response = await fetch(`api/user_profile.php?username=${encodeURIComponent(u)}`);
                const profile = await response.json();
                if (response.ok) {
                    this.viewedUserProfile = profile;
                    this.displayUserProfile(profile);
                    return;
                } else {
                    console.error('Failed to load profile for user:', u, profile);
                }
            } catch (e) {
                console.error('Error loading pending profile:', e);
                // fall through to default
            }
        }

        // If we're viewing another user's profile object, display it
        if (this.viewedUserProfile) {
            this.displayUserProfile(this.viewedUserProfile);
            return;
        }
        
        // Otherwise, load current user's profile
        if (!this.currentUser) {
            console.log('No current user found');
            return;
        }
        
        console.log('Loading current user profile for:', this.currentUser.username);
        try {
            const response = await fetch(`api/user_profile.php?username=${this.currentUser.username}`);
            console.log('Current user profile API response status:', response.status);
            const profile = await response.json();
            console.log('Current user profile data:', profile);
            this.displayProfile(profile);
            // Also ensure followed hashtags and liked users are loaded
            this.loadFollowedHashtags();
            this.loadLikedUsers();
        } catch (error) {
            this.showToast('Error loading profile', 'error');
        }
    }

    // Save profile (username, email, description)
    async saveProfile() {
        const email = document.getElementById('settingsEmail')?.value || '';
        const username = document.getElementById('settingsUsername')?.value || '';
        const description = document.getElementById('settingsDescription')?.value || '';
        try {
            const response = await fetch('api/users.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'update_profile', email, username, description })
            });
            const data = await response.json();
            if (response.ok) {
                this.showToast('Profile saved', 'success');
                // Update currentUser and header
                this.currentUser = { ...this.currentUser, username, email, description };
                this.updateUI();
            } else {
                this.showToast(data.message || 'Failed to save profile', 'error');
            }
        } catch (e) {
            this.showToast('Failed to save profile', 'error');
        }
    }

    async loadFollowedHashtags() {
        if (!this.currentUser) return;
        
        try {
            const response = await fetch('api/hashtags.php?action=followed');
            const hashtags = await response.json();
            this.displayFollowedHashtags(hashtags);
        } catch (error) {
            this.showToast('Error loading followed hashtags', 'error');
        }
    }

    async loadLikedUsers() {
        if (!this.currentUser) return;
        
        try {
            const response = await fetch('api/users.php?action=liked');
            const users = await response.json();
            this.displayLikedUsers(users);
        } catch (error) {
            this.showToast('Error loading liked users', 'error');
        }
    }

    async loadUserPosts(userId) {
        console.log('loadUserPosts called for user ID:', userId);
        try {
            const response = await fetch(`api/posts.php?action=user&user_id=${userId}`);
            console.log('Posts API response status:', response.status);
            if (response.ok) {
                const posts = await response.json();
                console.log('Posts loaded from API:', posts);
                this.displayPosts(posts);
            } else {
                console.error('Failed to load user posts, status:', response.status);
                const postsContainer = this.currentPage === 'profile' ? document.getElementById('profilePosts') : document.getElementById('posts');
                if (postsContainer) {
                    postsContainer.innerHTML = '<div class="no-posts">No posts found.</div>';
                }
            }
        } catch (error) {
            console.error('Error loading user posts:', error);
            const postsContainer = this.currentPage === 'profile' ? document.getElementById('profilePosts') : document.getElementById('posts');
            if (postsContainer) {
                postsContainer.innerHTML = '<div class="no-posts">Error loading posts.</div>';
            }
        }
    }

    async handleLogin() {
        const usernameField = document.getElementById('loginUsername');
        const passwordField = document.getElementById('loginPassword');
        if (!usernameField || !passwordField) return;
        
        const username = usernameField.value;
        const password = passwordField.value;

        try {
            const response = await fetch('api/auth.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'login',
                    username: username,
                    password: password
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (data.user) {
                this.currentUser = data.user;
                this.updateUI();
                this.hideModal('loginModal');
                this.showToast('Login successful!', 'success');
                // Reload page to update server-side session
                window.location.reload();
            } else {
                this.showToast(data.message || 'Login failed', 'error');
            }
        } catch (error) {
            console.error('Login error:', error);
            this.showToast('Login failed: ' + error.message, 'error');
        }
    }

    async handleRegister() {
        const usernameField = document.getElementById('registerUsername');
        const emailField = document.getElementById('registerEmail');
        const passwordField = document.getElementById('registerPassword');
        const descriptionField = document.getElementById('registerDescription');
        
        if (!usernameField || !emailField || !passwordField || !descriptionField) return;
        
        const username = usernameField.value;
        const email = emailField.value;
        const password = passwordField.value;
        const description = descriptionField.value;

        try {
            const response = await fetch('api/auth.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'register',
                    username: username,
                    email: email,
                    password: password,
                    description: description
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (response.ok) {
                this.hideModal('registerModal');
                this.showToast('Registration successful! Please login.', 'success');
                // Reload page to refresh the interface
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                this.showToast(data.message || 'Registration failed', 'error');
            }
        } catch (error) {
            console.error('Registration error:', error);
            this.showToast('Registration failed: ' + error.message, 'error');
        }
    }

    async handleCreatePost() {
        const content = document.getElementById('postContent').value;

        try {
            const response = await fetch('api/posts.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    content: content
                })
            });

            const data = await response.json();

            if (response.ok) {
                this.showToast('Post created successfully!', 'success');
                document.getElementById('postContent').value = '';
                document.getElementById('charCount').textContent = '0';
                this.hidePostCreate();
                this.loadFeed();
            } else {
                this.showToast(data.message, 'error');
            }
        } catch (error) {
            this.showToast('Failed to create post', 'error');
        }
    }

    async handleSearch() {
        const queryInput = document.getElementById('searchInput');
        if (!queryInput) return;
        const query = queryInput.value.trim();
        if (!query) return;

        // Decide target based on prefix or selected tab
        const activeTabBtn = document.querySelector('.search-tabs .tab-btn.active');
        let target = activeTabBtn ? activeTabBtn.dataset.tab : 'hashtag';
        if (query.startsWith('#')) target = 'hashtag';
        if (query.startsWith('@')) target = 'user';

        // Auto-detect if query looks like a username (no spaces, no special chars except underscore)
        // and switch to user tab if it doesn't look like a hashtag
        if (!query.startsWith('#') && !query.startsWith('@') && /^[a-zA-Z0-9_]+$/.test(query) && query.length > 2) {
            target = 'user';
            // Switch to user tab
            document.querySelectorAll('.search-tabs .tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelector('[data-tab="user"]').classList.add('active');
        }

        // Update URL to reflect search state
        this.pushRoute('search', {
            q: target === 'hashtag' ? (query.startsWith('#') ? query.slice(1) : query) : (query.startsWith('@') ? query.slice(1) : query),
            tab: target
        });

        try {
            if (target === 'hashtag') {
                const hashtag = query.startsWith('#') ? query.slice(1) : query;
                const response = await fetch(`api/posts.php?action=hashtag&hashtag=${encodeURIComponent(hashtag)}`);
                const posts = await response.json();
                this.displaySearchPosts(posts);
            } else {
                const username = query.startsWith('@') ? query.slice(1) : query;
                const response = await fetch(`api/user_search.php?q=${encodeURIComponent(username)}`);
                if (response.ok) {
                    const data = await response.json();
                    this.displaySearchResults(data.users, 'user');
                } else {
                    const error = await response.json();
                    this.showToast(error.message, 'error');
                }
            }
        } catch (error) {
            this.showToast('Search failed', 'error');
        }
    }

    // Render posts inside the search results area (for hashtag searches)
    displaySearchPosts(posts) {
        const resultsContainer = document.getElementById('searchResults');
        if (!resultsContainer) return;
        resultsContainer.innerHTML = '';

        if (!Array.isArray(posts) || posts.length === 0) {
            resultsContainer.innerHTML = '<div class="no-results">No posts found for this hashtag.</div>';
            return;
        }

        posts.forEach(post => {
            const el = this.createPostElement(post);
            resultsContainer.appendChild(el);
        });
    }

    displayPosts(posts) {
        console.log('displayPosts called with posts:', posts);
        
        // Determine which posts container to use based on current page
        let postsContainer;
        if (this.currentPage === 'profile') {
            postsContainer = document.getElementById('profilePosts');
            console.log('Using profile posts container');
        } else {
            postsContainer = document.getElementById('posts');
            console.log('Using home posts container');
        }
        
        if (!postsContainer) {
            console.error('Posts container not found! Current page:', this.currentPage);
            return;
        }
        console.log('Posts container found, displaying posts...');
        console.log('Posts container element:', postsContainer);
        console.log('Posts container parent:', postsContainer.parentElement);
        postsContainer.innerHTML = '';

        if (posts.length === 0) {
            postsContainer.innerHTML = '<div class="no-posts">No posts found. Be the first to post!</div>';
            console.log('No posts to display');
            return;
        }

        posts.forEach((post, index) => {
            console.log(`Creating post element ${index + 1}:`, post);
            const postElement = this.createPostElement(post);
            postsContainer.appendChild(postElement);
        });
        console.log('Displayed', posts.length, 'posts');
        console.log('Posts container innerHTML after display:', postsContainer.innerHTML.substring(0, 200) + '...');
    }

    createPostElement(post) {
        console.log('createPostElement called with post:', post);
        console.log('Post username:', post.username);
        console.log('Post content:', post.content);
        
        const postDiv = document.createElement('div');
        postDiv.className = 'post';
        
        const timeAgo = this.getTimeAgo(new Date(post.created_at));
        const avatar = post.username ? post.username.charAt(0).toUpperCase() : '?';
        
        const isLiked = post.is_liked == 1 || post.is_liked === true;
        const likeCount = parseInt(post.like_count) || 0;
        
        postDiv.innerHTML = `
            <div class="post-header">
                <div class="post-avatar">${avatar}</div>
                <div>
                    <a href="#" class="post-user" data-username="${post.username || 'unknown'}">@${post.username || 'unknown'}</a>
                    <span class="post-time">${timeAgo}</span>
                </div>
            </div>
            <div class="post-content">${this.formatPostContent(post.content || 'No content')}</div>
            <div class="post-actions">
                
            </div>
        `;

        // Add event listeners for post actions
        postDiv.querySelectorAll('.post-action').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const action = e.currentTarget.dataset.action;
                this.handlePostAction(action, post);
            });
        });

        // Add event listeners for user links
        postDiv.querySelectorAll('.post-user').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const username = e.currentTarget.dataset.username;
                // Force navigation via URL to make sure profile opens
                window.location.href = `?page=profile&username=${encodeURIComponent(username)}`;
            });
        });

        // Add event listeners for hashtags
        postDiv.querySelectorAll('.hashtag').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const hashtag = e.currentTarget.dataset.hashtag;
                this.searchHashtag(hashtag);
            });
        });

        // Add event listeners for mentions
        postDiv.querySelectorAll('.mention').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const username = e.currentTarget.dataset.username;
                // Force navigation via URL
                window.location.href = `?page=profile&username=${encodeURIComponent(username)}`;
            });
        });

        console.log('Created post element:', postDiv);
        console.log('Post element innerHTML:', postDiv.innerHTML.substring(0, 100) + '...');
        return postDiv;
    }

    async handlePostAction(action, post) {
        if (action === 'like') {
            await this.toggleLike(post);
        } else if (action === 'share') {
            this.sharePost(post);
        }
    }

    async toggleLike(post) {
        if (!this.currentUser) {
            this.showToast('Please log in to like posts', 'error');
            return;
        }

        try {
            const response = await fetch('api/likes.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    post_id: post.id
                })
            });

            const data = await response.json();

            if (response.ok) {
                // Update the like button
                const likeBtn = document.querySelector(`[data-post-id="${post.id}"]`);
                if (likeBtn) {
                    const icon = likeBtn.querySelector('i');
                    const countSpan = likeBtn.querySelector('.like-count');
                    
                    if (likeBtn.classList.contains('liked')) {
                        likeBtn.classList.remove('liked');
                        icon.className = 'far fa-heart';
                    } else {
                        likeBtn.classList.add('liked');
                        icon.className = 'fas fa-heart';
                    }
                    
                    if (countSpan) {
                        countSpan.textContent = data.like_count;
                    }
                }
            } else {
                this.showToast(data.message, 'error');
            }
        } catch (error) {
            this.showToast('Failed to like post', 'error');
        }
    }

    sharePost(post) {
        if (navigator.share) {
            navigator.share({
                title: `Post by @${post.username}`,
                text: post.content,
                url: window.location.href
            });
        } else {
            // Fallback: copy to clipboard
            navigator.clipboard.writeText(`${post.content} - @${post.username}`);
            this.showToast('Post copied to clipboard', 'success');
        }
    }

    formatPostContent(content) {
        // Convert hashtags to clickable links
        content = content.replace(/#(\w+)/g, '<a href="#" class="hashtag" data-hashtag="$1">#$1</a>');
        
        // Convert mentions to clickable links
        content = content.replace(/@(\w+)/g, '<a href="#" class="mention" data-username="$1">@$1</a>');
        
        return content;
    }

    async viewUserProfile(username) {
        console.log('viewUserProfile called with username:', username);
        this.showLoading();
        try {
            const response = await fetch(`api/user_profile.php?username=${encodeURIComponent(username)}`);
            console.log('Profile API response status:', response.status);
            const profile = await response.json();
            console.log('Profile data received:', profile);
            if (response.ok) {
                this.viewedUserProfile = profile;
                this.displayUserProfile(profile);
            } else {
                console.error('Profile API error:', profile.message);
                this.showToast(profile.message, 'error');
            }
        } catch (error) {
            console.error('Error loading user profile:', error);
            this.showToast('Failed to load user profile', 'error');
        } finally {
            this.hideLoading();
        }
    }

    async searchHashtag(hashtag) {
        try {
            // Navigate to search page and search for hashtag
            this.navigateToPage('search');
            
            // Switch to hashtag tab
            document.querySelectorAll('.search-tabs .tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelector('[data-tab="hashtag"]').classList.add('active');
            
            // Set search input and trigger search
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.value = hashtag;
                // Update URL and run search
                this.pushRoute('search', { q: hashtag, tab: 'hashtag' });
                this.handleSearch();
            }
        } catch (error) {
            this.showToast('Failed to search hashtag', 'error');
        }
    }

    displayUserProfile(profile) {
        console.log('displayUserProfile called with profile:', profile);
        
        // Update page title
        const profilePageTitle = document.getElementById('profilePageTitle');
        if (profilePageTitle) {
            profilePageTitle.textContent = `@${profile.username}`;
        }
        
        const profileInfo = document.getElementById('profileInfo');
        if (!profileInfo) {
            console.error('Profile info element not found');
            return;
        }
        console.log('Profile info element found, displaying profile...');

        const avatar = profile.username.charAt(0).toUpperCase();
        const joinDate = new Date(profile.created_at).toLocaleDateString();
        
        // Check if this is the current user's profile
        const isCurrentUser = this.currentUser && this.currentUser.username === profile.username;
        
        profileInfo.innerHTML = `
            <div class="profile-header">
                <div class="user-avatar" style="width: 80px; height: 80px; font-size: 32px;">
                    ${avatar}
                </div>
                <div class="profile-details">
                    <h2>@${profile.username}</h2>
                    <p class="profile-description">${profile.description || 'No description'}</p>
                    <p class="join-date">Joined ${joinDate}</p>
                    <div class="profile-stats">
                        <div class="stat">
                            <span class="stat-number">${profile.follower_count}</span>
                            <span class="stat-label">Followers</span>
                        </div>
                        <div class="stat">
                            <span class="stat-number">${profile.following_count}</span>
                            <span class="stat-label">Following</span>
                        </div>
                        <div class="stat">
                            <span class="stat-number">${profile.posts.length}</span>
                            <span class="stat-label">Posts</span>
                        </div>
                    </div>
                    <div class="profile-actions">
                        ${this.currentUser && !isCurrentUser ? `
                            <button class="btn btn-primary follow-btn" data-user-id="${profile.id}" data-following="${profile.is_following}">
                                <i class="fas fa-${profile.is_following ? 'user-minus' : 'user-plus'}"></i>
                                ${profile.is_following ? 'Unfollow' : 'Follow'}
                            </button>
                        ` : this.currentUser ? '' : `
                            <button class="btn btn-primary" id="loginBtnInline">Log in to follow</button>
                        `}
                    </div>
                </div>
            </div>
        `;

        // Add follow/unfollow functionality
        if (this.currentUser && !isCurrentUser) {
            const followBtn = profileInfo.querySelector('.follow-btn');
            if (followBtn) {
                followBtn.addEventListener('click', () => {
                    this.toggleFollow(profile.id, followBtn);
                });
            }
        }
        const loginBtnInline = profileInfo.querySelector('#loginBtnInline');
        if (loginBtnInline) {
            loginBtnInline.addEventListener('click', (e) => {
                e.preventDefault();
                this.showModal('loginModal');
            });
        }

        // Display user's posts
        console.log('Profile posts data:', profile.posts);
        if (profile.posts && profile.posts.length > 0) {
            console.log('Displaying posts from profile data:', profile.posts.length, 'posts');
            this.displayPosts(profile.posts);
        } else {
            console.log('No posts in profile data, loading separately for user ID:', profile.id);
            // If no posts in profile data, try to load them separately
            this.loadUserPosts(profile.id);
        }
    }

    async toggleFollow(userId, button) {
        if (!this.currentUser) {
            this.showToast('Please log in to follow users', 'error');
            return;
        }

        const isFollowing = button.dataset.following === 'true';
        
        try {
            const response = await fetch('api/users.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: isFollowing ? 'unfollow' : 'follow',
                    user_id: userId
                })
            });

            const data = await response.json();

            if (response.ok) {
                // Update button state
                const newFollowing = !isFollowing;
                button.dataset.following = newFollowing;
                button.innerHTML = `
                    <i class="fas fa-${newFollowing ? 'user-minus' : 'user-plus'}"></i>
                    ${newFollowing ? 'Unfollow' : 'Follow'}
                `;
                
                // Update follower count
                const followerCount = document.querySelector('.stat-number');
                if (followerCount) {
                    const currentCount = parseInt(followerCount.textContent);
                    followerCount.textContent = newFollowing ? currentCount + 1 : currentCount - 1;
                }
                
                this.showToast(data.message, 'success');
            } else {
                this.showToast(data.message, 'error');
            }
        } catch (error) {
            this.showToast('Failed to update follow status', 'error');
        }
    }

    displaySearchResults(results, type) {
        const resultsContainer = document.getElementById('searchResults');
        if (!resultsContainer) {
            console.error('Search results container not found');
            return;
        }
        resultsContainer.innerHTML = '';

        console.log('Displaying search results:', results, 'type:', type);

        if (results.length === 0) {
            resultsContainer.innerHTML = '<div class="no-results">No results found.</div>';
            return;
        }

        if (type === 'hashtag') {
            results.forEach(result => {
                const resultElement = this.createHashtagResultElement(result);
                resultsContainer.appendChild(resultElement);
            });
        } else {
            results.forEach(result => {
                const resultElement = this.createUserResultElement(result);
                resultsContainer.appendChild(resultElement);
            });
        }
    }

    createHashtagResultElement(hashtag) {
        const resultDiv = document.createElement('div');
        resultDiv.className = 'search-result';
        
        resultDiv.innerHTML = `
            <div class="hashtag-info">
                <span class="hashtag-tag">#${hashtag.tag}</span>
                <span class="post-count">${hashtag.post_count || 0} posts</span>
            </div>
            <button class="btn btn-primary" data-hashtag-id="${hashtag.id}">
                <i class="fas fa-plus"></i>
                Follow
            </button>
        `;

        resultDiv.querySelector('button').addEventListener('click', (e) => {
            this.followHashtag(hashtag.id);
        });

        return resultDiv;
    }

    createUserResultElement(user) {
        console.log('Creating user result element for:', user);
        const resultDiv = document.createElement('div');
        resultDiv.className = 'user-result';
        
        const avatar = user.username.charAt(0).toUpperCase();
        
        resultDiv.innerHTML = `
            <div class="user-result-avatar">${avatar}</div>
            <div class="user-result-info">
                <h3>@${user.username}</h3>
                <p>${user.description || 'No description'}</p>
                <div class="user-stats">
                    <span>${user.follower_count || 0} followers</span>
                    <span>•</span>
                    <span>${user.following_count || 0} following</span>
                    <span>•</span>
                    <span>${user.posts_count || 0} posts</span>
                </div>
            </div>
        `;

        // Make the entire result div clickable
        resultDiv.style.cursor = 'pointer';
        resultDiv.addEventListener('click', (e) => {
            e.preventDefault();
            console.log('User result clicked:', user.username);
            // Use direct navigation to profile with username parameter
            window.location.href = `?page=profile&username=${encodeURIComponent(user.username)}`;
        });

        return resultDiv;
    }

    async displayProfile(profile) {
        // Update page title
        const profilePageTitle = document.getElementById('profilePageTitle');
        if (profilePageTitle) {
            profilePageTitle.textContent = `@${profile.username}`;
        }
        
        const profileInfo = document.getElementById('profileInfo');
        const avatar = profile.username.charAt(0).toUpperCase();
        
        profileInfo.innerHTML = `
            <div class="profile-header">
                <div class="user-avatar" style="width: 60px; height: 60px; font-size: 24px;">${avatar}</div>
                <div>
                    <h2>@${profile.username}</h2>
                    <p>${profile.description || 'No description'}</p>
                    <p class="join-date">Joined ${new Date(profile.created_at).toLocaleDateString()}</p>
                </div>
            </div>
            <div class="profile-stats">
                <div class="stat">
                    <div class="stat-number">${profile.follower_count || 0}</div>
                    <div class="stat-label">Followers</div>
                </div>
                <div class="stat">
                    <div class="stat-number">${profile.following_count || 0}</div>
                    <div class="stat-label">Following</div>
                </div>
                <div class="stat">
                    <div class="stat-number">${profile.posts?.length || 0}</div>
                    <div class="stat-label">Posts</div>
                </div>
            </div>
        `;

        // Load user's posts
        console.log('Current user profile posts data:', profile.posts);
        if (profile.posts && profile.posts.length > 0) {
            console.log('Displaying posts from current user profile data:', profile.posts.length, 'posts');
            this.displayPosts(profile.posts);
        } else {
            console.log('No posts in current user profile data, loading separately for user ID:', profile.id);
            // Try to load posts from API
            this.loadUserPosts(profile.id);
        }
    }

    displayFollowedHashtags(hashtags) {
        const hashtagList = document.getElementById('hashtagList');
        hashtagList.innerHTML = '';

        if (hashtags.length === 0) {
            hashtagList.innerHTML = '<div class="no-items">You are not following any hashtags yet.</div>';
            return;
        }

        hashtags.forEach(hashtag => {
            const hashtagElement = document.createElement('div');
            hashtagElement.className = 'hashtag-item';
            
            hashtagElement.innerHTML = `
                <div class="hashtag-info">
                    <span class="hashtag-tag">#${hashtag.tag}</span>
                </div>
                <button class="btn btn-secondary" data-hashtag-id="${hashtag.id}">
                    <i class="fas fa-minus"></i>
                    Unfollow
                </button>
            `;

            hashtagElement.querySelector('button').addEventListener('click', (e) => {
                this.unfollowHashtag(hashtag.id);
            });

            hashtagList.appendChild(hashtagElement);
        });
    }

    displayLikedUsers(users) {
        const userList = document.getElementById('userList');
        userList.innerHTML = '';

        if (users.length === 0) {
            userList.innerHTML = '<div class="no-items">You are not liking any users yet.</div>';
            return;
        }

        users.forEach(user => {
            const userElement = document.createElement('div');
            userElement.className = 'user-item';
            
            const avatar = user.username.charAt(0).toUpperCase();
            
            userElement.innerHTML = `
                <div class="user-info">
                    <div class="user-avatar">${avatar}</div>
                    <div class="user-details">
                        <h4>@${user.username}</h4>
                        <p>${user.description || 'No description'}</p>
                    </div>
                </div>
                <button class="btn btn-secondary" data-user-id="${user.id}">
                    <i class="fas fa-heart-broken"></i>
                    Unlike
                </button>
            `;

            userElement.querySelector('button').addEventListener('click', (e) => {
                this.unlikeUser(user.id);
            });

            userList.appendChild(userElement);
        });
    }

    async followHashtag(hashtagId) {
        try {
            const response = await fetch('api/hashtags.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'follow',
                    hashtag_id: hashtagId
                })
            });

            const data = await response.json();

            if (response.ok) {
                this.showToast('Hashtag followed successfully!', 'success');
            } else {
                this.showToast(data.message, 'error');
            }
        } catch (error) {
            this.showToast('Failed to follow hashtag', 'error');
        }
    }

    async unfollowHashtag(hashtagId) {
        try {
            const response = await fetch('api/hashtags.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'unfollow',
                    hashtag_id: hashtagId
                })
            });

            const data = await response.json();

            if (response.ok) {
                this.showToast('Hashtag unfollowed successfully!', 'success');
                this.loadFollowedHashtags();
            } else {
                this.showToast(data.message, 'error');
            }
        } catch (error) {
            this.showToast('Failed to unfollow hashtag', 'error');
        }
    }

    async likeUser(userId) {
        try {
            const response = await fetch('api/users.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'like',
                    user_id: userId
                })
            });

            const data = await response.json();

            if (response.ok) {
                this.showToast('User liked successfully!', 'success');
            } else {
                this.showToast(data.message, 'error');
            }
        } catch (error) {
            this.showToast('Failed to like user', 'error');
        }
    }

    async unlikeUser(userId) {
        try {
            const response = await fetch('api/users.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'unlike',
                    user_id: userId
                })
            });

            const data = await response.json();

            if (response.ok) {
                this.showToast('User unliked successfully!', 'success');
                this.loadLikedUsers();
            } else {
                this.showToast(data.message, 'error');
            }
        } catch (error) {
            this.showToast('Failed to unlike user', 'error');
        }
    }

    handlePostAction(action, post) {
        switch(action) {
            case 'like':
                this.showToast('Like functionality coming soon!', 'warning');
                break;
            case 'share':
                this.showToast('Share functionality coming soon!', 'warning');
                break;
        }
    }

    viewUserProfile(username) {
        this.navigateToPage('search');
        document.getElementById('searchInput').value = username;
        this.handleSearch();
    }

    updateUI() {
        const userNameEl = document.getElementById('userName');
        const loginBtnEl = document.getElementById('loginBtn');
        const registerBtnEl = document.getElementById('registerBtn');
        const logoutBtnEl = document.getElementById('logoutBtn');
        const postCreateEl = document.getElementById('postCreate');

        if (this.currentUser) {
            if (userNameEl) userNameEl.textContent = this.currentUser.username;
            if (loginBtnEl) loginBtnEl.style.display = 'none';
            if (registerBtnEl) registerBtnEl.style.display = 'none';
            if (logoutBtnEl) logoutBtnEl.style.display = 'block';
            if (postCreateEl) postCreateEl.style.display = 'block';
        } else {
            if (userNameEl) userNameEl.textContent = 'Guest';
            if (loginBtnEl) loginBtnEl.style.display = 'block';
            if (registerBtnEl) registerBtnEl.style.display = 'block';
            if (logoutBtnEl) logoutBtnEl.style.display = 'none';
            if (postCreateEl) postCreateEl.style.display = 'none';
        }
    }

    async logout() {
        try {
            // Call logout API
            const response = await fetch('api/logout.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            });
            
            if (response.ok) {
                this.currentUser = null;
                this.updateUI();
                this.showToast('Logged out successfully', 'success');
                this.navigateToPage('home');
                // Reload page to clear server-side session
                window.location.reload();
            } else {
                this.showToast('Logout failed', 'error');
            }
        } catch (error) {
            console.error('Logout error:', error);
            this.showToast('Logout failed', 'error');
        }
    }

    showModal(modalId) {
        document.getElementById(modalId).classList.add('show');
    }

    hideModal(modalId) {
        document.getElementById(modalId).classList.remove('show');
    }

    showPostCreate() {
        const postCreate = document.getElementById('postCreate');
        const postContent = document.getElementById('postContent');
        if (postCreate) {
            postCreate.style.display = 'block';
        }
        if (postContent) {
            postContent.focus();
        }
    }

    hidePostCreate() {
        const postCreate = document.getElementById('postCreate');
        if (postCreate) {
            postCreate.style.display = 'none';
        }
    }

    showLoading() {
        const loading = document.getElementById('loading');
        if (loading) {
            loading.classList.add('show');
        }
    }

    hideLoading() {
        const loading = document.getElementById('loading');
        if (loading) {
            loading.classList.remove('show');
        }
    }

    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        
        document.getElementById('toastContainer').appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 5000);
    }

    getTimeAgo(date) {
        const now = new Date();
        const diff = now - date;
        const seconds = Math.floor(diff / 1000);
        const minutes = Math.floor(seconds / 60);
        const hours = Math.floor(minutes / 60);
        const days = Math.floor(hours / 24);

        if (days > 0) return `${days}d ago`;
        if (hours > 0) return `${hours}h ago`;
        if (minutes > 0) return `${minutes}m ago`;
        return 'Just now';
    }
}

// Initialize the application
document.addEventListener('DOMContentLoaded', () => {
    new TAItterApp();
});
