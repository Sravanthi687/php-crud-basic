<?php
// edit_user.php
require_once __DIR__ . '/db.php';
session_start();

// Enforce role-based access control
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    echo "<h1>403 Forbidden</h1><p>Access Denied: Admin permissions required.</p>";
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: manage_users.php");
    exit;
}

$message = '';
$error = '';
$user = null;

// Fetch user data to edit
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        header("Location: manage_users.php");
        exit;
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $role = trim($_POST['role'] ?? 'user');
    $bio = trim($_POST['bio'] ?? '');

    // Server-side validation
    if (empty($name) || empty($email)) {
        $error = 'Name and Email are required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (!in_array($role, ['user', 'admin'])) {
        $error = 'Invalid role.';
    } else {
        try {
            // Check if email already registered for another user
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $id]);
            if ($stmt->fetchColumn() > 0) {
                $error = 'Email is already registered by another user.';
            } else {
                // Update user details
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ?, gender = ?, role = ?, bio = ? WHERE id = ?");
                $stmt->execute([$name, $email, $phone, $gender, $role, $bio, $id]);
                $message = 'User profile updated successfully!';
                
                // Refresh updated details
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$id]);
                $user = $stmt->fetch();
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User (Admin Only) - Advanced CRUD</title>
</head>
<body>
    <h1>Edit User Profile (Admin View)</h1>
    <p><a href="manage_users.php">Back to User Management</a></p>

    <?php if (!empty($message)): ?>
        <p style="color: green; font-weight: bold;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <p style="color: red; font-weight: bold;">Error: <?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form id="editForm" action="edit_user.php?id=<?php echo htmlspecialchars($id); ?>" method="POST" onsubmit="return validateEditForm()">
        <div>
            <label for="name">Full Name *</label><br>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required><br><br>
        </div>

        <div>
            <label for="email">Email Address *</label><br>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required><br><br>
        </div>

        <div>
            <label for="phone">Phone Number</label><br>
            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"><br><br>
        </div>

        <div>
            <label>Gender</label><br>
            <input type="radio" name="gender" value="male" <?php echo ($user['gender'] === 'male') ? 'checked' : ''; ?>> Male
            <input type="radio" name="gender" value="female" <?php echo ($user['gender'] === 'female') ? 'checked' : ''; ?>> Female
            <input type="radio" name="gender" value="other" <?php echo ($user['gender'] === 'other') ? 'checked' : ''; ?>> Other<br><br>
        </div>

        <div>
            <label for="role">Role *</label><br>
            <select id="role" name="role" required>
                <option value="user" <?php echo ($user['role'] === 'user') ? 'selected' : ''; ?>>Regular User</option>
                <option value="admin" <?php echo ($user['role'] === 'admin') ? 'selected' : ''; ?>>Administrator</option>
            </select><br><br>
        </div>

        <div>
            <label for="bio">Short Bio</label><br>
            <textarea id="bio" name="bio" rows="4" cols="50"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea><br><br>
        </div>

        <button type="submit">Save Changes</button>
    </form>

    <script>
    function validateEditForm() {
        var name = document.getElementById('name').value.trim();
        var email = document.getElementById('email').value.trim();
        var role = document.getElementById('role').value;

        if (name === "" || email === "") {
            alert("Name and Email are required fields.");
            return false;
        }

        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            alert("Please enter a valid email address");
            return false;
        }

        if (role !== "user" && role !== "admin") {
            alert("Invalid role selected");
            return false;
        }

        return true;
    }
    </script>
</body>
</html>
