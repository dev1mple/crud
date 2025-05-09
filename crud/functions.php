<?php
// Database connection function
function getDBConnection() {
    $host = '127.0.0.1';
    $dbname = 'php_crud';
    $username = 'root';
    $password = '';
    $port = '3306';

    try {
        $dsn = "mysql:host=$host;port=$port;dbname=$dbname";
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// User authentication functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

// Post management functions
function createPost($pdo, $title, $content, $user_id) {
    try {
        $stmt = $pdo->prepare("INSERT INTO posts (title, content, user_id, created_by, updated_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $content, $user_id, $user_id, $user_id]);
        return "Post created successfully!";
    } catch(PDOException $e) {
        return "Error creating post: " . $e->getMessage();
    }
}

function updatePost($pdo, $post_id, $title, $content, $user_id) {
    try {
        $stmt = $pdo->prepare("UPDATE posts SET title = ?, content = ?, updated_by = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$title, $content, $user_id, $post_id, $user_id]);
        return "Post updated successfully!";
    } catch(PDOException $e) {
        return "Error updating post: " . $e->getMessage();
    }
}

function deletePost($pdo, $post_id, $user_id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
        $stmt->execute([$post_id, $user_id]);
        return "Post deleted successfully!";
    } catch(PDOException $e) {
        return "Error deleting post: " . $e->getMessage();
    }
}

function getPosts($pdo, $user_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, 
                   creator.username as creator_name,
                   updater.username as updater_name
            FROM posts p
            LEFT JOIN users creator ON p.created_by = creator.id
            LEFT JOIN users updater ON p.updated_by = updater.id
            WHERE p.user_id = ?
            ORDER BY p.created_at DESC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return "Error fetching posts: " . $e->getMessage();
    }
}

// User management functions
function registerUser($pdo, $username, $email, $password, $is_admin = 0) {
    try {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return "Email already exists";
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, is_admin) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $email, $hashed_password, $is_admin]);
        return true;
    } catch(PDOException $e) {
        return "Error: " . $e->getMessage();
    }
}

function loginUser($pdo, $email, $password) {
    try {
        $stmt = $pdo->prepare("SELECT id, username, password, is_admin FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = $user['is_admin'];
            return true;
        }
        return "Invalid email or password";
    } catch(PDOException $e) {
        return "Error: " . $e->getMessage();
    }
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

function requireAdmin() {
    if (!isAdmin()) {
        header("Location: index.php");
        exit();
    }
}

// Admin functions
function getAllUsers($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT id, username, email, is_admin, created_at FROM users ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return "Error fetching users: " . $e->getMessage();
    }
}

function getAllPosts($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, 
                   u.username as creator_name,
                   updater.username as updater_name
            FROM posts p
            LEFT JOIN users u ON p.created_by = u.id
            LEFT JOIN users updater ON p.updated_by = updater.id
            ORDER BY p.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return "Error fetching posts: " . $e->getMessage();
    }
}

function updateUserRole($pdo, $user_id, $is_admin) {
    try {
        // First check if the user exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        if (!$stmt->fetch()) {
            return "User not found";
        }

        // Update the user's admin status
        $stmt = $pdo->prepare("UPDATE users SET is_admin = ? WHERE id = ?");
        $result = $stmt->execute([$is_admin, $user_id]);
        
        if ($result) {
            return $is_admin ? "User is now an admin" : "User is no longer an admin";
        } else {
            return "Error updating user role";
        }
    } catch(PDOException $e) {
        return "Error updating user role: " . $e->getMessage();
    }
}

function deleteUser($pdo, $user_id) {
    try {
        // First delete all posts by the user
        $stmt = $pdo->prepare("DELETE FROM posts WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        // Then delete the user
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        return "User deleted successfully!";
    } catch(PDOException $e) {
        return "Error deleting user: " . $e->getMessage();
    }
}

function adminDeletePost($pdo, $post_id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->execute([$post_id]);
        return "Post deleted successfully!";
    } catch(PDOException $e) {
        return "Error deleting post: " . $e->getMessage();
    }
}

// Helper functions
function formatDate($date) {
    return date('M d, Y H:i', strtotime($date));
}

function sanitizeInput($data) {
    return htmlspecialchars(trim($data));
}
?> 