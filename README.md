# PHP CRUD Basic Application

This is the **Basic** version of the Advanced PHP CRUD application. It demonstrates core functionality (CRUD, Authentication, Profile Management, External API Integration) using plain HTML without CSS styling, serving as a functional baseline.

## Features

- **Authentication**: User Registration and Login.
- **Role-Based Access Control (RBAC)**: Supports `user` and `admin` roles. Admin users can manage other users.
- **User Dashboard**: Personalized dashboard greeting the user and displaying real-time weather data.
- **Profile Management**: Users can update their profile information and upload profile pictures.
- **User Management (Admin Only)**: 
  - List all users
  - Edit user details (Name, Email, Role)
  - Delete users
- **External API**: Integrates Open-Meteo API for live weather data.
- **Database**: SQLite database utilizing PDO for secure prepared statements.

## Setup Instructions

1. Ensure you have a web server with PHP installed (e.g., XAMPP, WAMP, or standalone PHP).
2. Place the `php-crud-basic` folder inside your web server's document root (e.g., `C:\xampp\htdocs\php-crud-basic`).
3. Ensure the web server process has write access to the `php-crud-basic` directory so the SQLite database (`app.db`) and uploaded files (`uploads/` directory) can be created and modified.
4. Access the application via your web browser: `http://localhost/php-crud-basic/login.php`

## Usage Guide

1. **Initial Access**: Navigate to `register.php` to create a new user account. You can select either the `user` or `admin` role.
2. **Dashboard**: After logging in, you will be redirected to the Dashboard where you can see your basic info and current weather.
3. **Profile**: Click "My Profile" to update your name, email, or upload a profile picture.
4. **Admin Features**: If you registered as an admin, click "Manage Users" to view, edit, or delete existing user accounts.

## Project Structure

- `db.php`: Database connection and schema initialization script.
- `index.php`: Redirects to login.
- `register.php`: User registration.
- `login.php`: User login.
- `logout.php`: Destroys the session.
- `dashboard.php`: Main dashboard with Weather API integration.
- `profile.php`: Profile updates and picture upload.
- `manage_users.php`: Admin-only view to list users.
- `edit_user.php`: Admin-only view to edit user data.
- `delete_user.php`: Admin-only script to delete a user.
