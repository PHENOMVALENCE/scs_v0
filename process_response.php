<?php
// Start session
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Include database connection
require_once 'db_connect.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $complaint_id = $_POST['complaint_id'];
    $response = $conn->real_escape_string($_POST['response']);
    $status = $conn->real_escape_string($_POST['status']);
    
    // Validate input
    if (empty($complaint_id) || empty($response) || empty($status)) {
        header("Location: admin_dashboard.php?error=Please fill in all fields");
        exit;
    }
    
    // Update complaint
    $sql = "UPDATE complaints SET response = ?, status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $response, $status, $complaint_id);
    
    if ($stmt->execute()) {
        header("Location: admin_dashboard.php?success=Response submitted successfully");
        exit;
    } else {
        header("Location: admin_dashboard.php?error=Failed to submit response. Please try again.");
        exit;
    }
    
    $stmt->close();
} else {
    // If not submitted via POST, redirect to dashboard
    header("Location: admin_dashboard.php");
    exit;
}

$conn->close();
?>
