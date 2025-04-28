<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login');
    exit;
}

// Database configuration
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'otp_register';

// Connect to database
try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Get all queries with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

$stmt = $pdo->prepare("SELECT SQL_CALC_FOUND_ROWS * FROM contact_submissions ORDER BY submission_date DESC LIMIT :offset, :perPage");
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);
$stmt->execute();
$queries = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = $pdo->query("SELECT FOUND_ROWS()")->fetchColumn();
$totalPages = ceil($total / $perPage);

// Update status if requested
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = $_POST['query_id'];
    $status = $_POST['new_status'];
    
    $updateStmt = $pdo->prepare("UPDATE contact_submissions SET status = ? WHERE id = ?");
    $updateStmt->execute([$status, $id]);
    
    // Refresh the page to show updated status
    header("Location: admin_queries?page=$page");
    exit;
}
include 'header.php'
?>


    
    <!-- Main Content -->
    <div class="container">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0">User Contact Queries</h2>
                <div>
                    <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Search queries...">
                </div>
            </div>
            
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Subject</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($queries as $query): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($query['id']); ?></td>
                                <td><?php echo htmlspecialchars($query['first_name'] . ' ' . $query['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($query['email']); ?></td>
                                <td><?php echo ucfirst(htmlspecialchars($query['subject'])); ?></td>
                                <td><?php echo date('M j, Y g:i a', strtotime($query['submission_date'])); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo str_replace('_', '-', $query['status']); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $query['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <button onclick="showQueryDetails(<?php echo $query['id']; ?>)" 
                                            class="btn btn-sm btn-primary action-btn me-1">
                                        View
                                    </button>
                                    
                                    <form class="d-inline" method="post" action="admin_queries?page=<?php echo $page; ?>">
                                        <input type="hidden" name="query_id" value="<?php echo $query['id']; ?>">
                                        <select name="new_status" onchange="this.form.submit()" 
                                                class="form-select form-select-sm d-inline-block" style="width: auto;">
                                            <option value="new" <?php echo $query['status'] === 'new' ? 'selected' : ''; ?>>New</option>
                                            <option value="in_progress" <?php echo $query['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                            <option value="resolved" <?php echo $query['status'] === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                        </select>
                                        <input type="hidden" name="update_status" value="1">
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
    
    <!-- Query Details Modal -->
    <div class="modal fade" id="queryDetailsModal" tabindex="-1" aria-labelledby="queryDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="queryDetailsModalLabel">Query Details #<span id="modalQueryId"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="query-details">
                                <dt>Name:</dt>
                                <dd id="detailName"></dd>
                                
                                <dt>Email:</dt>
                                <dd id="detailEmail"></dd>
                                
                                <dt>Phone:</dt>
                                <dd id="detailPhone"></dd>
                                
                                <dt>Application ID:</dt>
                                <dd id="detailAppId"></dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="query-details">
                                <dt>Subject:</dt>
                                <dd id="detailSubject"></dd>
                                
                                <dt>Status:</dt>
                                <dd id="detailStatus"></dd>
                                
                                <dt>Submission Date:</dt>
                                <dd id="detailDate"></dd>
                            </dl>
                        </div>
                    </div>
                    <hr>
                    <h6>Message:</h6>
                    <div class="p-3 bg-light rounded" id="detailMessage"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show query details in modal
        function showQueryDetails(id) {
            fetch('get_query_details.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Populate modal with data
                        document.getElementById('modalQueryId').textContent = data.query.id;
                        document.getElementById('detailName').textContent = data.query.first_name + ' ' + data.query.last_name;
                        document.getElementById('detailEmail').textContent = data.query.email;
                        document.getElementById('detailPhone').textContent = data.query.phone || 'Not provided';
                        document.getElementById('detailAppId').textContent = data.query.application_id || 'N/A';
                        document.getElementById('detailSubject').textContent = data.query.subject.charAt(0).toUpperCase() + data.query.subject.slice(1);
                        document.getElementById('detailStatus').innerHTML = `<span class="status-badge status-${data.query.status.replace('_', '-')}">${data.query.status.replace('_', ' ')}</span>`;
                        document.getElementById('detailDate').textContent = new Date(data.query.submission_date).toLocaleString();
                        document.getElementById('detailMessage').textContent = data.query.message;
                        
                        // Show modal
                        const modal = new bootstrap.Modal(document.getElementById('queryDetailsModal'));
                        modal.show();
                    } else {
                        alert('Error loading query details');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading query details');
                });
        }
        
        // Simple search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    </script>
</body>
</html>