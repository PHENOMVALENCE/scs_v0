<?php
// Include database connection
require_once 'db_connect.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate input
    if (empty($username) || empty($password) || empty($confirm_password)) {
        header("Location: register.php?error=Please fill in all fields");
        exit;
    }
    
    // Check if passwords match
    if ($password !== $confirm_password) {
        header("Location: register.php?error=Passwords do not match");
        exit;
    }
    
    // Check if username already exists
    $sql = "SELECT id FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        header("Location: register.php?error=Username already exists");
        exit;
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user (as student)
    $sql = "INSERT INTO users (username, password, role) VALUES (?, ?, 'student')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $hashed_password);
    
    if ($stmt->execute()) {
        header("Location: index.php?success=Registration successful. Please login.");
        exit;
    } else {
        header("Location: register.php?error=Registration failed. Please try again.");
        exit;
    }
    
    $stmt->close();
} else {
    // If not submitted via POST, redirect to registration page
    header("Location: register.php");
    exit;
}

$conn->close();
?>
