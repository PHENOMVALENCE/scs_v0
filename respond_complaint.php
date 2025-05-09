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

// Check if complaint ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin_dashboard.php?error=Invalid complaint ID");
    exit;
}

$complaint_id = $_GET['id'];

// Get complaint details
$sql = "SELECT c.*, u.username as student_username 
        FROM complaints c 
        JOIN users u ON c.student_id = u.id 
        WHERE c.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $complaint_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: admin_dashboard.php?error=Complaint not found");
    exit;
}

$complaint = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Respond to Complaint - Admin Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1>Student Complaint System</h1>
            <div class="user-info">
                <a href="admin_dashboard.php" class="btn btn-back">Back to Dashboard</a>
                <a href="logout.php" class="btn btn-logout">Logout</a>
            </div>
        </header>
        
        <main class="dashboard-content">
            <section class="respond-section">
                <h2>Respond to Complaint</h2>
                
                <div class="complaint-details">
                    <div class="complaint-header">
                        <h3><?php echo htmlspecialchars($complaint['subject']); ?></h3>
                        <span class="complaint-status status-<?php echo $complaint['status']; ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?>
                        </span>
                    </div>
                    
                    <div class="complaint-meta">
                        <span>Student: <?php echo htmlspecialchars($complaint['student_username']); ?></span>
                        <span>Submitted: <?php echo date('M d, Y h:i A', strtotime($complaint['created_at'])); ?></span>
                    </div>
                    
                    <div class="complaint-body">
                        <p><?php echo nl2br(htmlspecialchars($complaint['message'])); ?></p>
                    </div>
                </div>
                
                <form action="process_response.php" method="post" class="response-form">
                    <input type="hidden" name="complaint_id" value="<?php echo $complaint['id']; ?>">
                    
                    <div class="form-group">
                        <label for="response">Your Response</label>
                        <textarea id="response" name="response" rows="5" required><?php echo htmlspecialchars($complaint['response'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Update Status</label>
                        <select id="status" name="status">
                            <option value="pending" <?php echo $complaint['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="in_progress" <?php echo $complaint['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="resolved" <?php echo $complaint['status'] === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Submit Response</button>
                    </div>
                </form>
            </section>
        </main>
        
        <footer class="dashboard-footer">
            <p>&copy; <?php echo date('Y'); ?> Student Complaint System</p>
        </footer>
    </div>
</body>
</html>
