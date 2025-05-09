<?php
// Start session
session_start();

// Include database connection
require_once 'db_connect.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    
    // Validate input
    if (empty($username) || empty($password)) {
        header("Location: index.php?error=Please fill in all fields");
        exit;
    }
    
    // Check if user exists
    $sql = "SELECT id, username, password, role FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: student_dashboard.php");
            }
            exit;
        } else {
            header("Location: index.php?error=Invalid username or password");
            exit;
        }
    } else {
        header("Location: index.php?error=Invalid username or password");
        exit;
    }
    
    $stmt->close();
} else {
    // If not submitted via POST, redirect to login page
    header("Location: index.php");
    exit;
}

$conn->close();
?>
