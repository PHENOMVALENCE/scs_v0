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

// Check if complaint ID and status are provided
if (isset($_GET['id']) && is_numeric($_GET['id']) && isset($_GET['status'])) {
    $complaint_id = $_GET['id'];
    $status = $_GET['status'];
    
    // Validate status
    $valid_statuses = ['pending', 'in_progress', 'resolved'];
    if (!in_array($status, $valid_statuses)) {
        header("Location: admin_dashboard.php?error=Invalid status");
        exit;
    }
    
    // Update complaint status
    $sql = "UPDATE complaints SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $complaint_id);
    
    if ($stmt->execute()) {
        header("Location: admin_dashboard.php?success=Status updated successfully");
        exit;
    } else {
        header("Location: admin_dashboard.php?error=Failed to update status");
        exit;
    }
    
    $stmt->close();
} else {
    header("Location: admin_dashboard.php?error=Invalid request");
    exit;
}

$conn->close();
?>
