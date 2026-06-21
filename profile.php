<?php
// profile.php
require_once __DIR__ . '/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Fetch current user details
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        // Session user doesn't exist anymore
        header("Location: logout.php");
        exit;
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $bio = trim($_POST['bio'] ?? '');

    // Server-side validation
    if (empty($name)) {
        $error = 'Full Name is a required field.';
    } else {
        try {
            // Profile image upload handling
            $profile_pic_path = $user['profile_picture']; // Default to old path

            if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] !== UPLOAD_ERR_NO_FILE) {
                $file = $_FILES['profile_pic'];
                
                // 1. Check for upload errors
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception("File upload failed with error code: " . $file['error']);
                }

                // 2. Validate file size (Limit to 2MB)
                $max_size = 2 * 1024 * 1024; // 2MB
                if ($file['size'] > $max_size) {
                    throw new Exception("File size exceeds 2MB limit.");
                }

                // 3. Validate file MIME type/extension
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                if (!in_array($mime_type, $allowed_types)) {
                    throw new Exception("Invalid file type. Only JPG, PNG, and GIF are allowed.");
                }

                // 4. Ensure upload folder exists
                $upload_dir = __DIR__ . '/uploads';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                // 5. Generate a unique safe file name
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $safe_filename = uniqid('profile_', true) . '.' . $extension;
                $destination = $upload_dir . '/' . $safe_filename;

                // 6. Move the uploaded file
                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    // Delete old profile picture file if it exists
                    if (!empty($user['profile_picture']) && file_exists(__DIR__ . '/' . $user['profile_picture'])) {
                        @unlink(__DIR__ . '/' . $user['profile_picture']);
                    }
                    $profile_pic_path = 'uploads/' . $safe_filename;
                } else {
                    throw new Exception("Error moving uploaded file to destination folder.");
                }
            }

            // Update user details in database
            $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, gender = ?, bio = ?, profile_picture = ? WHERE id = ?");
            $stmt->execute([$name, $phone, $gender, $bio, $profile_pic_path, $user_id]);
            
            // Refresh session name if it changed
            $_SESSION['name'] = $name;

            // Refresh user details variable
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();

            $message = 'Profile updated successfully!';
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Advanced CRUD</title>
</head>
<body>
    <h1>My Profile</h1>
    <p><a href="dashboard.php">Back to Dashboard</a> | <a href="logout.php">Logout</a></p>

    <?php if (!empty($message)): ?>
        <div style="color: green; font-weight: bold; margin-bottom: 15px;">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div style="color: red; font-weight: bold; margin-bottom: 15px;">
            Error: <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <h2>Profile Information</h2>
    <div>
        <strong>Profile Picture:</strong><br>
        <?php if (!empty($user['profile_picture']) && file_exists(__DIR__ . '/' . $user['profile_picture'])): ?>
            <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" style="max-width: 150px; display: block; margin-top: 5px;"><br>
        <?php else: ?>
            <p>No profile picture uploaded yet.</p>
        <?php endif; ?>
    </div>

    <form action="profile.php" method="POST" enctype="multipart/form-data">
        <div>
            <label for="name">Full Name *</label><br>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required><br><br>
        </div>

        <div>
            <label>Email (Cannot be changed)</label><br>
            <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled><br><br>
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
            <label for="bio">Short Bio</label><br>
            <textarea id="bio" name="bio" rows="4" cols="50"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea><br><br>
        </div>

        <div>
            <label for="profile_pic">Upload Profile Picture (JPG, PNG, GIF - Max 2MB)</label><br>
            <input type="file" id="profile_pic" name="profile_pic" accept="image/*"><br><br>
        </div>

        <button type="submit">Save Changes</button>
    </form>
</body>
</html>
