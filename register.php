<?php
// register.php
require_once __DIR__ . '/db.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and trim inputs
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $role = trim($_POST['role'] ?? 'user');

    // Server-side validation
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Name, Email, and Password are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif (!in_array($role, ['user', 'admin'])) {
        $error = 'Invalid user role selected.';
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                $error = 'Email is already registered.';
            } else {
                // Hash the password securely
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert user
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone, gender, bio, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $email, $hashed_password, $phone, $gender, $bio, $role]);
                $message = 'User registered successfully! You can now <a href="login.php">Login</a>.';
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
    <title>Register - Advanced CRUD</title>
</head>
<body>
    <h1>Register New Account</h1>
    <p><a href="login.php">Back to Login</a></p>

    <?php if (!empty($message)): ?>
        <div style="color: green; font-weight: bold; margin-bottom: 15px;">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div style="color: red; font-weight: bold; margin-bottom: 15px;">
            Error: <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form id="regForm" action="register.php" method="POST" onsubmit="return validateForm()">
        <div>
            <label for="name">Full Name *</label><br>
            <input type="text" id="name" name="name" required><br><br>
        </div>

        <div>
            <label for="email">Email Address *</label><br>
            <input type="email" id="email" name="email" required><br><br>
        </div>

        <div>
            <label for="password">Password (min 6 chars) *</label><br>
            <input type="password" id="password" name="password" required><br><br>
        </div>

        <div>
            <label for="phone">Phone Number</label><br>
            <input type="text" id="phone" name="phone"><br><br>
        </div>

        <div>
            <label>Gender</label><br>
            <input type="radio" name="gender" value="male" checked> Male
            <input type="radio" name="gender" value="female"> Female
            <input type="radio" name="gender" value="other"> Other<br><br>
        </div>

        <div>
            <label for="role">Role *</label><br>
            <select id="role" name="role" required>
                <option value="user">Regular User</option>
                <option value="admin">Administrator</option>
            </select><br><br>
        </div>

        <div>
            <label for="bio">Short Bio</label><br>
            <textarea id="bio" name="bio" rows="4" cols="50"></textarea><br><br>
        </div>

        <button type="submit">Register</button>
        <button type="reset">Reset</button>
    </form>

    <script>
    function validateForm() {
        var name = document.getElementById('name').value.trim();
        var email = document.getElementById('email').value.trim();
        var password = document.getElementById('password').value;
        var role = document.getElementById('role').value;

        if (name === "" || email === "" || password === "") {
            alert("Please fill in all required fields marked with *");
            return false;
        }

        // Email regex
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            alert("Please enter a valid email address");
            return false;
        }

        if (password.length < 6) {
            alert("Password must be at least 6 characters long");
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
