<?php
session_start();
require_once 'functions.php';
require_once 'handleForms.php';

// Get database connection
$pdo = getDBConnection();

// Check if user is admin
requireAdmin();

$message = '';

// Handle admin actions
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'toggle_admin':
            if (isset($_GET['user_id'])) {
                $user_id = $_GET['user_id'];
                // Only allow making users admin, not removing admin status
                $is_admin = 1;
                
                // Update the user's role
                $result = updateUserRole($pdo, $user_id, $is_admin);
                
                // Force refresh the page
                header("Location: admin.php?message=" . urlencode($result));
                exit();
            }
            break;
        case 'delete_user':
            if (isset($_GET['user_id'])) {
                // Check if the user to be deleted is an admin
                $stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
                $stmt->execute([$_GET['user_id']]);
                $user = $stmt->fetch();
                
                if ($user && $user['is_admin']) {
                    $message = "Cannot delete an admin user";
                } else {
                    $message = deleteUser($pdo, $_GET['user_id']);
                }
                header("Location: admin.php?message=" . urlencode($message));
                exit();
            }
            break;
        case 'delete_post':
            if (isset($_GET['post_id'])) {
                $message = adminDeletePost($pdo, $_GET['post_id']);
                header("Location: admin.php?message=" . urlencode($message));
                exit();
            }
            break;
    }
}

// Get message from URL if it exists
if (isset($_GET['message'])) {
    $message = $_GET['message'];
}

// Get all users and posts
$users = getAllUsers($pdo);
$posts = getAllPosts($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Make user admin
            $('.make-admin').click(function() {
                const userId = $(this).data('user-id');
                const username = $(this).data('username');
                
                if (confirm(`Are you sure you want to make "${username}" an admin?`)) {
                    window.location.href = `admin.php?action=toggle_admin&user_id=${userId}`;
                }
            });

            // Delete user
            $('.delete-user').click(function() {
                const userId = $(this).data('user-id');
                const username = $(this).data('username');
                const isAdmin = $(this).data('is-admin');
                
                if (isAdmin) {
                    alert("Cannot delete an admin user");
                    return;
                }
                
                if (confirm(`Are you sure you want to delete user "${username}"? This will also delete all their posts.`)) {
                    window.location.href = `admin.php?action=delete_user&user_id=${userId}`;
                }
            });

            // Delete post
            $('.delete-post').click(function() {
                const postId = $(this).data('post-id');
                const title = $(this).data('title');
                
                if (confirm(`Are you sure you want to delete the post "${title}"?`)) {
                    window.location.href = `admin.php?action=delete_post&post_id=${postId}`;
                }
            });
        });
    </script>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Admin Dashboard</h1>
            <div class="welcome-message">
                <span>Welcome, <?php echo sanitizeInput($_SESSION['username']); ?></span>
                <a href="index.php" class="btn btn-secondary">View Posts</a>
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>

        <!-- Users Management Section -->
        <div class="card">
            <h2>User Management</h2>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo sanitizeInput($user['username']); ?></td>
                                <td><?php echo sanitizeInput($user['email']); ?></td>
                                <td>
                                    <span class="role-badge <?php echo $user['is_admin'] ? 'admin' : 'user'; ?>">
                                        <?php echo $user['is_admin'] ? 'Admin' : 'User'; ?>
                                    </span>
                                </td>
                                <td><?php echo formatDate($user['created_at']); ?></td>
                                <td>
                                    <?php if (!$user['is_admin']): ?>
                                        <button class="btn btn-primary make-admin" 
                                                data-user-id="<?php echo $user['id']; ?>"
                                                data-username="<?php echo sanitizeInput($user['username']); ?>">
                                            Make Admin
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-danger delete-user" 
                                            data-user-id="<?php echo $user['id']; ?>"
                                            data-username="<?php echo sanitizeInput($user['username']); ?>"
                                            data-is-admin="<?php echo $user['is_admin']; ?>">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Posts Management Section -->
        <div class="card">
            <h2>Post Management</h2>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Content</th>
                            <th>Author</th>
                            <th>Created At</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $post): ?>
                            <tr>
                                <td><?php echo sanitizeInput($post['title']); ?></td>
                                <td><?php echo sanitizeInput(substr($post['content'], 0, 100)) . '...'; ?></td>
                                <td><?php echo sanitizeInput($post['creator_name']); ?></td>
                                <td><?php echo formatDate($post['created_at']); ?></td>
                                <td><?php echo formatDate($post['updated_at']); ?></td>
                                <td>
                                    <button class="btn btn-danger delete-post" 
                                            data-post-id="<?php echo $post['id']; ?>"
                                            data-title="<?php echo sanitizeInput($post['title']); ?>">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html> 