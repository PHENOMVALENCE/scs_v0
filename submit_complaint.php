<?php
// Start session
session_start();

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: index.php");
    exit;
}

// Include database connection
require_once 'db_connect.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $student_id = $_SESSION['user_id'];
    $subject = $conn->real_escape_string($_POST['subject']);
    $message = $conn->real_escape_string($_POST['message']);
    
    // Validate input
    if (empty($subject) || empty($message)) {
        header("Location: student_dashboard.php?error=Please fill in all fields");
        exit;
    }
    
    // Insert complaint
    $sql = "INSERT INTO complaints (student_id, subject, message) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $student_id, $subject, $message);
    
    if ($stmt->execute()) {
        header("Location: student_dashboard.php?success=Complaint submitted successfully");
        exit;
    } else {
        header("Location: student_dashboard.php?error=Failed to submit complaint. Please try again.");
        exit;
    }
    
    $stmt->close();
} else {
    // If not submitted via POST, redirect to dashboard
    header("Location: student_dashboard.php");
    exit;
}

$conn->close();
?>
