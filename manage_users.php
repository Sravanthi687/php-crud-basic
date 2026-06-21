<?php
// manage_users.php
require_once __DIR__ . '/db.php';
session_start();

// Enforce role-based access control
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    echo "<h1>403 Forbidden</h1><p>Access Denied: Admin permissions required to view this page.</p>";
    echo '<p><a href="dashboard.php">Back to Dashboard</a></p>';
    exit;
}

try {
    // Retrieve all users
    $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error retrieving users: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users (Admin Only) - Advanced CRUD</title>
</head>
<body>
    <h1>User Management Console</h1>
    <p><a href="dashboard.php">Back to Dashboard</a> | <a href="logout.php">Logout</a></p>

    <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
        <p style="color: green; font-weight: bold;">Success: User deleted successfully.</p>
    <?php endif; ?>

    <h2>Users Table</h2>
    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Profile Pic</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Gender</th>
                <th>Role</th>
                <th>Bio</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                    <td>
                        <?php if (!empty($user['profile_picture']) && file_exists(__DIR__ . '/' . $user['profile_picture'])): ?>
                            <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Pic" style="max-width: 40px; max-height: 40px;">
                        <?php else: ?>
                            <em>None</em>
                        <?php endif; ?>
                    </td>
                    <td><strong><?php echo htmlspecialchars($user['name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['phone'] ?: '-'); ?></td>
                    <td><?php echo htmlspecialchars($user['gender'] ?: '-'); ?></td>
                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                    <td><?php echo htmlspecialchars(mb_strimwidth($user['bio'] ?? '', 0, 40, '...')); ?></td>
                    <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                    <td>
                        <a href="edit_user.php?id=<?php echo $user['id']; ?>">Edit</a> | 
                        <a href="delete_user.php?id=<?php echo $user['id']; ?>" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
