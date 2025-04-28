<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login");
    exit();
}

// Handle notification submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_notification'])) {
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $link = filter_input(INPUT_POST, 'link', FILTER_SANITIZE_STRING);
    $type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING);
    
    if ($title && $link && in_array($type, ['new', 'reminder', 'result', 'alert'])) {
        $stmt = $pdo->prepare("INSERT INTO notifications (title, link, type) VALUES (?, ?, ?)");
        $stmt->execute([$title, $link, $type]);
        $success = "Notification added successfully!";
    } else {
        $error = "Please provide valid title, URL and notification type";
    }
}

// Get all notifications
$notifications = $pdo->query("SELECT * FROM notifications ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
include 'header.php';
?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Admin Dashboard</h1>
            <a href="admin_logout" class="btn btn-outline-danger">Logout</a>
        </div>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h2 class="h5 mb-0"><i class="fa-regular fa-bell"></i> Add New Notification</h2>
            </div>
            <div class="card-body">
                <form method="post">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="link" class="form-label">Link URL</label>
                        <input type="text" class="form-control" id="link" name="link" required>
                    </div>
                    <div class="mb-3">
                        <label for="type" class="form-label">Notification Type</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="new">New</option>
                            <option value="reminder">Reminder</option>
                            <option value="result">Result</option>
                            <option value="alert">Alert</option>
                        </select>
                    </div>
                    <button type="submit" name="add_notification" class="btn btn-primary">Add Notification</button>
                </form>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h2 class="h5 mb-0"><i class="fa-regular fa-bell"></i> Current Notifications</h2>
            </div>
            <div class="card-body">
                <?php if (empty($notifications)): ?>
                    <p class="text-muted">No notifications found.</p>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($notifications as $notification): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center type-<?= $notification['type'] ?>">
                                <div>
                                    <h5 class="mb-1"><?= htmlspecialchars($notification['title']) ?></h5>
                                    <small class="text-muted">Type: <?= ucfirst($notification['type']) ?></small><br>
                                    <small class="text-muted">Posted: <?= $notification['created_at'] ?></small>
                                </div>
                                <div>
                                    <a href="<?= htmlspecialchars($notification['link']) ?>" target="_blank" class="btn btn-sm btn-outline-primary me-2">View</a>
                                    <a href="?delete=<?= $notification['id'] ?>" onclick="return confirm('Are you sure?')" class="btn btn-sm btn-outline-danger">Delete</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>