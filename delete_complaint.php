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

// Check if complaint ID is provided
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $complaint_id = $_GET['id'];
    $student_id = $_SESSION['user_id'];
    
    // Check if complaint belongs to this student and is still pending
    $sql = "SELECT id FROM complaints WHERE id = ? AND student_id = ? AND status = 'pending'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $complaint_id, $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        // Delete the complaint
        $sql = "DELETE FROM complaints WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $complaint_id);
        
        if ($stmt->execute()) {
            header("Location: student_dashboard.php?success=Complaint deleted successfully");
            exit;
        } else {
            header("Location: student_dashboard.php?error=Failed to delete complaint");
            exit;
        }
    } else {
        header("Location: student_dashboard.php?error=You can only delete your own pending complaints");
        exit;
    }
    
    $stmt->close();
} else {
    header("Location: student_dashboard.php");
    exit;
}

$conn->close();
?>
