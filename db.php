<?php
// db.php
// Database connection file using PDO SQLite

$db_file = __DIR__ . '/advanced_tasks.db';

try {
    // Connect to SQLite
    $pdo = new PDO("sqlite:" . $db_file);
    
    // Configure PDO to throw exceptions on error
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Create users table if it does not exist
    $createTableQuery = "
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            phone TEXT,
            gender TEXT,
            bio TEXT,
            role TEXT DEFAULT 'user',
            profile_picture TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ";
    $pdo->exec($createTableQuery);

} catch (PDOException $e) {
    // Display user-friendly database connection error
    echo "<h1>Database Error</h1>";
    echo "<p>Sorry, there was an issue connecting to the database server. Technical details: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}
?>
