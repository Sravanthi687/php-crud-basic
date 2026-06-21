<?php
// dashboard.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_name = $_SESSION['name'];
$user_role = $_SESSION['role'];

// API Integration - Fetching weather info (Hyderabad, India - Latitude: 17.3850, Longitude: 78.4867)
$weather_info = null;
$weather_error = '';

try {
    $api_url = "https://api.open-meteo.com/v1/forecast?latitude=17.3850&longitude=78.4867&current_weather=true";
    
    // Set connection timeout to 3 seconds to avoid blocking page load
    $opts = [
        'http' => [
            'method' => 'GET',
            'timeout' => 3
        ]
    ];
    $context = stream_context_create($opts);
    $response = @file_get_contents($api_url, false, $context);
    
    if ($response === false) {
        throw new Exception("Unable to fetch weather data from API.");
    }
    
    $data = json_decode($response, true);
    if (isset($data['current_weather'])) {
        $weather_info = $data['current_weather'];
    } else {
        throw new Exception("Invalid response structure from Weather API.");
    }
} catch (Exception $e) {
    $weather_error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Advanced PHP CRUD</title>
</head>
<body>
    <h1>Dashboard</h1>
    <p>Welcome, <strong><?php echo htmlspecialchars($user_name); ?></strong>! (Role: <em><?php echo htmlspecialchars($user_role); ?></em>)</p>
    
    <hr>
    
    <h2>Navigation</h2>
    <ul>
        <li><a href="profile.php">My Profile</a></li>
        <?php if ($user_role === 'admin'): ?>
            <li><a href="manage_users.php">Manage Users (Admin Only)</a></li>
        <?php endif; ?>
        <li><a href="logout.php">Logout</a></li>
    </ul>
    
    <hr>
    
    <h2>Weather Integration (External API)</h2>
    <p>Real-time weather query for Hyderabad, India:</p>
    <?php if ($weather_info): ?>
        <ul>
            <li><strong>Temperature:</strong> <?php echo htmlspecialchars($weather_info['temperature']); ?> °C</li>
            <li><strong>Wind Speed:</strong> <?php echo htmlspecialchars($weather_info['windspeed']); ?> km/h</li>
            <li><strong>Weather Code:</strong> <?php echo htmlspecialchars($weather_info['weathercode']); ?></li>
            <li><strong>Time Fetched:</strong> <?php echo htmlspecialchars($weather_info['time']); ?></li>
        </ul>
        <p><small>Powered by Open-Meteo public API</small></p>
    <?php else: ?>
        <p style="color: orange;">Weather Information currently unavailable. Error: <?php echo htmlspecialchars($weather_error); ?></p>
    <?php endif; ?>
</body>
</html>
