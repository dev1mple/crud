<?php
session_start();
require_once 'functions.php';
require_once 'handleForms.php';

// Get database connection
$pdo = getDBConnection();

// Check if user is logged in
requireLogin();

$user_id = $_SESSION['user_id'];

// Handle post form (create, update, delete)
$message = handlePostForm($pdo);

// If there was a successful update, redirect to remove the edit parameter
if (isset($_POST['update']) && strpos($message, 'successfully') !== false) {
    header("Location: index.php?message=" . urlencode($message));
    exit();
}

// Get posts data
$posts = getAllPosts($pdo);

// If editing a post, get its data
$edit_post = null;
if (isset($_GET['edit'])) {
    $edit_post = getPostData($pdo, $_GET['edit']);
}

// Get message from URL if it exists
if (isset($_GET['message'])) {
    $message = $_GET['message'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Posts</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Clear form after successful submission
            $('form').on('submit', function() {
                if (!$(this).find('input[name="post_id"]').length) {
                    // Only clear if it's not an edit form
                    setTimeout(function() {
                        $('form')[0].reset();
                    }, 100);
                }
            });

            // Handle edit cancel
            $('.btn-secondary').click(function(e) {
                e.preventDefault();
                window.location.href = 'index.php';
            });
        });
    </script>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Posts</h1>
            <div class="welcome-message">
                <span>Welcome, <?php echo sanitizeInput($_SESSION['username']); ?></span>
                <?php if (isAdmin()): ?>
                    <a href="admin.php" class="btn btn-primary">Admin Dashboard</a>
                <?php endif; ?>
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>

        <!-- Create/Update Post Form -->
        <div class="card">
            <h2><?php echo isset($_GET['edit']) ? 'Edit Post' : 'Create New Post'; ?></h2>
            <form method="POST" id="postForm">
                <?php if (isset($_GET['edit'])): ?>
                    <input type="hidden" name="post_id" value="<?php echo $_GET['edit']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" value="<?php echo $edit_post ? sanitizeInput($edit_post['title']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="content">Content</label>
                    <textarea id="content" name="content" required><?php echo $edit_post ? sanitizeInput($edit_post['content']) : ''; ?></textarea>
                </div>
                
                <button type="submit" name="<?php echo isset($_GET['edit']) ? 'update' : 'create'; ?>" class="btn btn-primary">
                    <?php echo isset($_GET['edit']) ? 'Update Post' : 'Create Post'; ?>
                </button>
                
                <?php if (isset($_GET['edit'])): ?>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Posts List -->
        <div class="post-grid">
            <?php foreach ($posts as $post): ?>
                <div class="post-card <?php echo $post['user_id'] == $_SESSION['user_id'] ? 'own-post' : ''; ?>">
                    <h3 class="post-title"><?php echo sanitizeInput($post['title']); ?></h3>
                    <p class="post-content"><?php echo sanitizeInput($post['content']); ?></p>
                    <div class="post-meta">
                        <p>Created by: <?php echo sanitizeInput($post['creator_name']); ?></p>
                        <p>Created on: <?php echo formatDate($post['created_at']); ?></p>
                        <p>Last updated by: <?php echo sanitizeInput($post['updater_name']); ?></p>
                        <p>Last updated: <?php echo formatDate($post['updated_at']); ?></p>
                    </div>
                    <div class="post-actions">
                        <?php if ($post['user_id'] == $_SESSION['user_id']): ?>
                            <a href="index.php?edit=<?php echo $post['id']; ?>" class="btn btn-primary">Edit</a>
                            <a href="index.php?delete=<?php echo $post['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this post?')">Delete</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>