<?php
// login.php
require_once __DIR__ . '/db.php';

session_start();

// Redirect logged-in users directly to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email and Password are required fields.';
    } else {
        try {
            // Retrieve user by email securely
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Password is correct, start session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['name'] = $user['name'];

                header("Location: dashboard.php");
                exit;
            } else {
                $error = 'Invalid email or password.';
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
    <title>Login - Advanced CRUD</title>
</head>
<body>
    <h1>Login</h1>
    
    <?php if (!empty($error)): ?>
        <div style="color: red; font-weight: bold; margin-bottom: 15px;">
            Error: <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form id="loginForm" action="login.php" method="POST" onsubmit="return validateLogin()">
        <div>
            <label for="email">Email Address *</label><br>
            <input type="email" id="email" name="email" required><br><br>
        </div>

        <div>
            <label for="password">Password *</label><br>
            <input type="password" id="password" name="password" required><br><br>
        </div>

        <button type="submit">Login</button>
    </form>

    <p>Don't have an account? <a href="register.php">Register Here</a></p>

    <script>
    function validateLogin() {
        var email = document.getElementById('email').value.trim();
        var password = document.getElementById('password').value;

        if (email === "" || password === "") {
            alert("Both email and password are required.");
            return false;
        }

        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            alert("Please enter a valid email address");
            return false;
        }

        return true;
    }
    </script>
</body>
</html>
