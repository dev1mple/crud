// AJAX functions for admin actions
const AdminAjax = {
    // Toggle admin role
    toggleAdmin: function(userId, isAdmin) {
        const newRole = isAdmin ? 0 : 1;
        const action = isAdmin ? 'remove admin privileges from' : 'make';
        
        if (confirm(`Are you sure you want to ${action} this user?`)) {
            $.ajax({
                url: 'admin.php',
                method: 'POST',
                data: {
                    action: 'toggle_admin',
                    user_id: userId,
                    is_admin: newRole
                },
                success: function(response) {
                    if (response.success) {
                        // Update the button state
                        const button = $(`[data-user-id="${userId}"]`);
                        button.data('is-admin', newRole);
                        button.text(isAdmin ? 'Make Admin' : 'Remove Admin');
                        alert('User role updated successfully!');
                    } else {
                        alert('Error updating user role: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error occurred while updating user role');
                }
            });
        }
    },

    // Delete user
    deleteUser: function(userId, username) {
        if (confirm(`Are you sure you want to delete user "${username}"? This will also delete all their posts.`)) {
            $.ajax({
                url: 'admin.php',
                method: 'POST',
                data: {
                    action: 'delete_user',
                    user_id: userId
                },
                success: function(response) {
                    if (response.success) {
                        // Remove the user row from the table
                        $(`[data-user-id="${userId}"]`).closest('tr').fadeOut();
                        alert('User deleted successfully!');
                    } else {
                        alert('Error deleting user: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error occurred while deleting user');
                }
            });
        }
    },

    // Delete post
    deletePost: function(postId, title) {
        if (confirm(`Are you sure you want to delete the post "${title}"?`)) {
            $.ajax({
                url: 'admin.php',
                method: 'POST',
                data: {
                    action: 'delete_post',
                    post_id: postId
                },
                success: function(response) {
                    if (response.success) {
                        // Remove the post row from the table
                        $(`[data-post-id="${postId}"]`).closest('tr').fadeOut();
                        alert('Post deleted successfully!');
                    } else {
                        alert('Error deleting post: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error occurred while deleting post');
                }
            });
        }
    }
};

// Event handlers
$(document).ready(function() {
    // Toggle admin role
    $('.toggle-admin').click(function() {
        const userId = $(this).data('user-id');
        const isAdmin = $(this).data('is-admin');
        AdminAjax.toggleAdmin(userId, isAdmin);
    });

    // Delete user
    $('.delete-user').click(function() {
        const userId = $(this).data('user-id');
        const username = $(this).data('username');
        AdminAjax.deleteUser(userId, username);
    });

    // Delete post
    $('.delete-post').click(function() {
        const postId = $(this).data('post-id');
        const title = $(this).data('title');
        AdminAjax.deletePost(postId, title);
    });
}); 