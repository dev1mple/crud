$(document).ready(function() {
    // Toggle admin role
    $('.toggle-admin').click(function() {
        const userId = $(this).data('user-id');
        const isAdmin = $(this).data('is-admin');
        const newRole = isAdmin ? 0 : 1;
        const action = isAdmin ? 'remove admin privileges from' : 'make';
        
        if (confirm(`Are you sure you want to ${action} this user?`)) {
            window.location.href = `admin.php?action=toggle_admin&user_id=${userId}&is_admin=${newRole}`;
        }
    });

    // Delete user
    $('.delete-user').click(function() {
        const userId = $(this).data('user-id');
        const username = $(this).data('username');
        
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