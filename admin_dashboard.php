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

// Get user information
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Build query based on filter
$where_clause = "";
if ($status_filter !== 'all') {
    $where_clause = "WHERE c.status = '" . $conn->real_escape_string($status_filter) . "'";
}

// Get all complaints with student information
$sql = "SELECT c.*, u.username as student_username 
        FROM complaints c 
        JOIN users u ON c.student_id = u.id 
        $where_clause
        ORDER BY 
            CASE 
                WHEN c.status = 'pending' THEN 1
                WHEN c.status = 'in_progress' THEN 2
                WHEN c.status = 'resolved' THEN 3
            END, 
            c.created_at DESC";
$result = $conn->query($sql);

// Get complaint counts
$sql_counts = "SELECT 
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_count,
                SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved_count,
                COUNT(*) as total_count
               FROM complaints";
$counts_result = $conn->query($sql_counts);
$counts = $counts_result->fetch_assoc();

// Check for success or error messages
$success = isset($_GET['success']) ? $_GET['success'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Complaint System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1>Student Complaint System</h1>
            <div class="user-info">
                <span>Welcome, Admin <?php echo htmlspecialchars($username); ?></span>
                <a href="logout.php" class="btn btn-logout">Logout</a>
            </div>
        </header>
        
        <main class="dashboard-content">
            <section class="dashboard-stats">
                <div class="stat-card">
                    <h3>Total</h3>
                    <p><?php echo $counts['total_count']; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Pending</h3>
                    <p><?php echo $counts['pending_count']; ?></p>
                </div>
                <div class="stat-card">
                    <h3>In Progress</h3>
                    <p><?php echo $counts['in_progress_count']; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Resolved</h3>
                    <p><?php echo $counts['resolved_count']; ?></p>
                </div>
            </section>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <section class="complaints-filter">
                <h2>All Complaints</h2>
                <div class="filter-controls">
                    <label for="status-filter">Filter by Status:</label>
                    <select id="status-filter" onchange="window.location.href='admin_dashboard.php?status='+this.value">
                        <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="in_progress" <?php echo $status_filter === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="resolved" <?php echo $status_filter === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                    </select>
                </div>
            </section>
            
            <section class="complaints-list-section">
                <?php if ($result->num_rows > 0): ?>
                    <div class="complaints-list admin-complaints-list">
                        <?php while ($complaint = $result->fetch_assoc()): ?>
                            <div class="complaint-card">
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
                                
                                <?php if ($complaint['response']): ?>
                                    <div class="complaint-response">
                                        <h4>Admin Response:</h4>
                                        <p><?php echo nl2br(htmlspecialchars($complaint['response'])); ?></p>
                                        <span class="response-date">Responded: <?php echo date('M d, Y h:i A', strtotime($complaint['updated_at'])); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="complaint-actions">
                                    <?php if ($complaint['status'] !== 'resolved'): ?>
                                        <a href="respond_complaint.php?id=<?php echo $complaint['id']; ?>" class="btn btn-primary">
                                            <?php echo $complaint['response'] ? 'Update Response' : 'Respond'; ?>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($complaint['status'] === 'pending'): ?>
                                        <a href="update_status.php?id=<?php echo $complaint['id']; ?>&status=in_progress" class="btn btn-secondary">
                                            Mark as In Progress
                                        </a>
                                    <?php elseif ($complaint['status'] === 'in_progress'): ?>
                                        <a href="update_status.php?id=<?php echo $complaint['id']; ?>&status=resolved" class="btn btn-success">
                                            Mark as Resolved
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="no-complaints">
                        <p>No complaints found.</p>
                    </div>
                <?php endif; ?>
            </section>
        </main>
        
        <footer class="dashboard-footer">
            <p>&copy; <?php echo date('Y'); ?> Student Complaint System</p>
        </footer>
    </div>
    
    <script src="js/script.js"></script>
</body>
</html>
