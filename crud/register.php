<?php
session_start();
require_once 'functions.php';
require_once 'handleForms.php';

// Get database connection
$pdo = getDBConnection();

// Handle registration form
$error = handleRegisterForm($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="auth-container">
            <div class="auth-form">
                <h2 class="auth-title">Register</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>

                    <div class="form-group">
                        <label for="admin_code">Admin Registration Code (Optional)</label>
                        <input type="password" id="admin_code" name="admin_code">
                        <small class="form-text">Leave empty for regular user registration</small>
                    </div>
                    
                    <button type="submit" name="register" class="btn btn-primary">Register</button>
                </form>
                
                <div class="auth-link">
                    <p>Already have an account? <a href="login.php">Login here</a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>