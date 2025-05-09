<?php
require_once 'functions.php';

function handleLoginForm($pdo) {
    if (!isset($_POST['login'])) {
        return '';
    }

    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    
    $result = loginUser($pdo, $email, $password);
    if ($result === true) {
        header("Location: index.php");
        exit();
    }
    return $result;
}

function handleRegisterForm($pdo) {
    if (!isset($_POST['register'])) {
        return '';
    }

    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $admin_code = isset($_POST['admin_code']) ? $_POST['admin_code'] : '';
    
    if ($password !== $confirm_password) {
        return "Passwords do not match";
    }

    // Check admin code if provided
    $is_admin = 0;
    if (!empty($admin_code)) {
        // You should change this to a more secure admin code
        if ($admin_code === 'admin') {
            $is_admin = 1;
        } else {
            return "Invalid admin registration code";
        }
    }

    $result = registerUser($pdo, $username, $email, $password, $is_admin);
    if ($result === true) {
        header("Location: login.php");
        exit();
    }
    return $result;
}

function handlePostForm($pdo) {
    if (!isset($_SESSION['user_id'])) {
        return "You must be logged in to perform this action";
    }

    $user_id = $_SESSION['user_id'];
    $message = '';

    // Create Post
    if (isset($_POST['create'])) {
        $title = sanitizeInput($_POST['title']);
        $content = sanitizeInput($_POST['content']);
        $message = createPost($pdo, $title, $content, $user_id);
    }

    // Update Post
    if (isset($_POST['update'])) {
        $post_id = $_POST['post_id'];
        $title = sanitizeInput($_POST['title']);
        $content = sanitizeInput($_POST['content']);
        $message = updatePost($pdo, $post_id, $title, $content, $user_id);
    }

    // Delete Post
    if (isset($_GET['delete'])) {
        $post_id = $_GET['delete'];
        $message = deletePost($pdo, $post_id, $user_id);
    }

    return $message;
}

function getPostData($pdo, $post_id = null) {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    if ($post_id) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
            $stmt->execute([$post_id, $_SESSION['user_id']]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return null;
        }
    }

    return getPosts($pdo, $_SESSION['user_id']);
}
?> 