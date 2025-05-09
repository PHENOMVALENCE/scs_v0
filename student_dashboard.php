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

// Get user information
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Get all complaints by this student
$sql = "SELECT * FROM complaints WHERE student_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$complaints = $stmt->get_result();

// Check for success or error messages
$success = isset($_GET['success']) ? $_GET['success'] : '';
$error = isset($_GET['error']) ? $_GET['error'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Complaint System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1>Student Complaint System</h1>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($username); ?></span>
                <a href="logout.php" class="btn btn-logout">Logout</a>
            </div>
        </header>
        
        <main class="dashboard-content">
            <section class="complaint-form-section">
                <h2>Submit a New Complaint</h2>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form action="submit_complaint.php" method="post" class="complaint-form">
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" rows="5" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Submit Complaint</button>
                    </div>
                </form>
            </section>
            
            <section class="complaints-list-section">
                <h2>My Complaints</h2>
                
                <?php if ($complaints->num_rows > 0): ?>
                    <div class="complaints-list">
                        <?php while ($complaint = $complaints->fetch_assoc()): ?>
                            <div class="complaint-card">
                                <div class="complaint-header">
                                    <h3><?php echo htmlspecialchars($complaint['subject']); ?></h3>
                                    <span class="complaint-status status-<?php echo $complaint['status']; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?>
                                    </span>
                                </div>
                                
                                <div class="complaint-body">
                                    <p><?php echo nl2br(htmlspecialchars($complaint['message'])); ?></p>
                                </div>
                                
                                <?php if ($complaint['response']): ?>
                                    <div class="complaint-response">
                                        <h4>Admin Response:</h4>
                                        <p><?php echo nl2br(htmlspecialchars($complaint['response'])); ?></p>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="complaint-footer">
                                    <span class="complaint-date">Submitted: <?php echo date('M d, Y h:i A', strtotime($complaint['created_at'])); ?></span>
                                    
                                    <?php if ($complaint['status'] === 'pending'): ?>
                                        <a href="delete_complaint.php?id=<?php echo $complaint['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this complaint?')">Delete</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="no-complaints">
                        <p>You haven't submitted any complaints yet.</p>
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
