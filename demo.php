<?php
session_start();
// Database connection
$db = new mysqli('localhost', 'your_username', 'your_password', 'your_database');
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Get current advertisements (active and within application period)
$current_date = date('Y-m-d');
$query = "SELECT a.*, 
          (SELECT COUNT(*) FROM posts p WHERE p.advertisement_id = a.id) as post_count
          FROM advertisements a
          WHERE a.is_active = 1 
          AND a.application_start_date <= '$current_date'
          AND a.application_end_date >= '$current_date'
          ORDER BY a.application_start_date DESC";
$advertisements = $db->query($query);

// Function to get posts for an advertisement
function getPosts($db, $advertisement_id) {
    $query = "SELECT * FROM posts WHERE advertisement_id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param('i', $advertisement_id);
    $stmt->execute();
    return $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Portal - Current Opportunities</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --accent-color: #e74c3c;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        
        .navbar {
            background-color: var(--secondary-color);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-weight: 700;
            color: white !important;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.8) !important;
            font-weight: 500;
        }
        
        .nav-link:hover, .nav-link.active {
            color: white !important;
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 10px 10px;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
            margin-bottom: 20px;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            font-weight: 600;
        }
        
        .badge-primary {
            background-color: var(--primary-color);
        }
        
        .btn-apply {
            background-color: var(--accent-color);
            border: none;
            font-weight: 500;
        }
        
        .btn-apply:hover {
            background-color: #c0392b;
        }
        
        .important-links {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        
        .link-item {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .link-item:last-child {
            border-bottom: none;
        }
        
        .link-item a {
            color: var(--secondary-color);
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .link-item a:hover {
            color: var(--primary-color);
        }
        
        .vacancy-badge {
            font-size: 0.8rem;
            margin-right: 5px;
            margin-bottom: 5px;
        }
        
        .modal-header {
            background-color: var(--primary-color);
            color: white;
        }
        
        footer {
            background-color: var(--secondary-color);
            color: white;
            padding: 2rem 0;
            margin-top: 3rem;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-briefcase me-2"></i>Job Portal
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">My Applications</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Notifications</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Help</a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 mb-3">Current Job Opportunities</h1>
            <p class="lead">Browse through the latest job advertisements and apply for positions that match your qualifications</p>
        </div>
    </div>

    <div class="container mb-5">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-9">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-list-alt me-2"></i>Current Advertisements</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="advertisementsTable" class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>S.No.</th>
                                        <th>Advt No</th>
                                        <th>Posts (Vacancies)</th>
                                        <th>Important Dates</th>
                                        <th>Download Advt</th>
                                        <th>Apply Link</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $sno = 1; ?>
                                    <?php while ($ad = $advertisements->fetch_assoc()): 
                                        $posts = getPosts($db, $ad['id']);
                                    ?>
                                        <tr>
                                            <td><?= $sno++ ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($ad['advertisement_number']) ?></strong>
                                            </td>
                                            <td>
                                                <?php while ($post = $posts->fetch_assoc()): ?>
                                                    <div class="mb-2">
                                                        <strong><?= htmlspecialchars($post['post_name']) ?></strong>
                                                        <div>
                                                            <span class="badge bg-primary vacancy-badge">Total: <?= $post['total_vacancies'] ?></span>
                                                            <span class="badge bg-secondary vacancy-badge">Gen: <?= $post['vacancies_general'] ?></span>
                                                            <span class="badge bg-success vacancy-badge">OBC: <?= $post['vacancies_obc'] ?></span>
                                                            <span class="badge bg-info vacancy-badge">SC: <?= $post['vacancies_sc'] ?></span>
                                                            <span class="badge bg-warning vacancy-badge">ST: <?= $post['vacancies_st'] ?></span>
                                                            <span class="badge bg-danger vacancy-badge">EWS: <?= $post['vacancies_ews'] ?></span>
                                                        </div>
                                                    </div>
                                                <?php endwhile; ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" 
                                                        data-bs-target="#datesModal<?= $ad['id'] ?>">
                                                    <i class="far fa-calendar-alt me-1"></i>View Dates
                                                </button>
                                                
                                                <!-- Dates Modal -->
                                                <div class="modal fade" id="datesModal<?= $ad['id'] ?>" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Important Dates - <?= $ad['advertisement_number'] ?></h5>
                                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <ul class="list-group list-group-flush">
                                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                                        <strong>Application Start Date</strong>
                                                                        <span><?= date('d M, Y', strtotime($ad['application_start_date'])) ?></span>
                                                                    </li>
                                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                                        <strong>Application End Date</strong>
                                                                        <span><?= date('d M, Y', strtotime($ad['application_end_date'])) ?></span>
                                                                    </li>
                                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                                        <strong>Last Date for Payment</strong>
                                                                        <span><?= date('d M, Y', strtotime($ad['last_date_payment'])) ?></span>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if (!empty($ad['detail_link'])): ?>
                                                    <a href="<?= htmlspecialchars($ad['detail_link']) ?>" class="btn btn-sm btn-outline-secondary" target="_blank">
                                                        <i class="fas fa-download me-1"></i>Download
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">Not Available</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($ad['apply_link'])): ?>
                                                    <a href="<?= htmlspecialchars($ad['apply_link']) ?>" class="btn btn-sm btn-apply" target="_blank">
                                                        <i class="fas fa-external-link-alt me-1"></i>Apply Now
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">Not Available</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar with Important Links -->
            <div class="col-lg-3">
                <div class="important-links mb-4">
                    <h5 class="mb-3"><i class="fas fa-link me-2"></i>Important Links</h5>
                    <div class="link-item">
                        <a href="#"><i class="fas fa-file-alt me-2"></i>How to Apply</a>
                    </div>
                    <div class="link-item">
                        <a href="#"><i class="fas fa-question-circle me-2"></i>FAQs</a>
                    </div>
                    <div class="link-item">
                        <a href="#"><i class="fas fa-book me-2"></i>Recruitment Rules</a>
                    </div>
                    <div class="link-item">
                        <a href="#"><i class="fas fa-university me-2"></i>Organization Website</a>
                    </div>
                    <div class="link-item">
                        <a href="#"><i class="fas fa-phone-alt me-2"></i>Contact Helpdesk</a>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-bell me-2"></i>Latest Notifications</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info p-2 mb-2">
                            <small><strong>New:</strong> Updated application guidelines</small>
                        </div>
                        <div class="alert alert-warning p-2 mb-2">
                            <small><strong>Reminder:</strong> Check your application status</small>
                        </div>
                        <div class="alert alert-success p-2">
                            <small><strong>Result:</strong> Previous recruitment results declared</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>About Job Portal</h5>
                    <p>A centralized platform for all recruitment activities, providing transparent and efficient application processes.</p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white">Home</a></li>
                        <li><a href="#" class="text-white">Current Openings</a></li>
                        <li><a href="#" class="text-white">Results</a></li>
                        <li><a href="#" class="text-white">Contact Us</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact Information</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-envelope me-2"></i> jobs@example.com</li>
                        <li><i class="fas fa-phone me-2"></i> +1 234 567 890</li>
                        <li><i class="fas fa-map-marker-alt me-2"></i> 123 Recruitment St, City</li>
                    </ul>
                </div>
            </div>
            <hr class="bg-light">
            <div class="text-center">
                <p class="mb-0">&copy; <?= date('Y') ?> Job Portal. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#advertisementsTable').DataTable({
                responsive: true,
                order: [[0, 'asc']],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search advertisements...",
                },
                dom: '<"top"f>rt<"bottom"lip><"clear">'
            });
        });
    </script>
</body>
</html>
<?php
$db->close();
?>