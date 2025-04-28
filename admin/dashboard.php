<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login");
    exit();
}

// Get basic user statistics
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

// Payment statistics
$paymentStats = $pdo->query("
    SELECT 
        COUNT(DISTINCT user_id) as paying_users,
        SUM(amount) as total_amount,
        SUM(CASE WHEN payment_status = 'success' THEN amount ELSE 0 END) as completed_amount
    FROM payments
")->fetch(PDO::FETCH_ASSOC);

// Extract category distribution from JSON
$categoryStats = $pdo->query("
    SELECT 
        JSON_UNQUOTE(JSON_EXTRACT(personal_info, '$.category')) as category,
        COUNT(*) as count
    FROM users
    WHERE personal_info IS NOT NULL AND JSON_EXTRACT(personal_info, '$.category') IS NOT NULL
    GROUP BY JSON_UNQUOTE(JSON_EXTRACT(personal_info, '$.category'))
")->fetchAll(PDO::FETCH_ASSOC);

// Extract gender distribution from JSON
$genderStats = $pdo->query("
    SELECT 
        JSON_UNQUOTE(JSON_EXTRACT(personal_info, '$.gender')) as gender,
        COUNT(*) as count
    FROM users
    WHERE personal_info IS NOT NULL AND JSON_EXTRACT(personal_info, '$.gender') IS NOT NULL
    GROUP BY JSON_UNQUOTE(JSON_EXTRACT(personal_info, '$.gender'))
")->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
?>
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Admin Dashboard</h1>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <!-- Total Users Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card users-card h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Registered Users</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $totalUsers ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Paying Users Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card payment-card h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Paying Users</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= $paymentStats['paying_users'] ?? 0 ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Payments Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card payment-card h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Total Payments</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    ₹<?= number_format($paymentStats['total_amount'] ?? 0, 2) ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-wallet fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Completed Payments Card -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card stat-card payment-card h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Completed Payments</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    ₹<?= number_format($paymentStats['completed_amount'] ?? 0, 2) ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts and Detailed Statistics -->
        <div class="row">
            <!-- Category Distribution -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow mb-4 category-card">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">Category Distribution</h6>
                        <span>Total: <?= array_sum(array_column($categoryStats, 'count')) ?></span>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="categoryChart"></canvas>
                        </div>
                        <div class="table-responsive mt-4">
                            <table class="table table-bordered data-table">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Count</th>
                                        <th>Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $totalWithCategory = array_sum(array_column($categoryStats, 'count'));
                                    foreach ($categoryStats as $category): 
                                        $percentage = $totalWithCategory > 0 ? ($category['count'] / $totalWithCategory) * 100 : 0;
                                    ?>
                                        <tr>
                                            <td><?= htmlspecialchars($category['category'] ?? 'Not specified') ?></td>
                                            <td><?= $category['count'] ?></td>
                                            <td>
                                                <div class="progress">
                                                    <div class="progress-bar" role="progressbar" 
                                                         style="width: <?= $percentage ?>%; background-color: <?= getCategoryColor($category['category']) ?>;" 
                                                         aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100">
                                                        <?= round($percentage, 1) ?>%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gender Distribution -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow mb-4 gender-card">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">Gender Distribution</h6>
                        <span>Total: <?= array_sum(array_column($genderStats, 'count')) ?></span>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="genderChart"></canvas>
                        </div>
                        <div class="table-responsive mt-4">
                            <table class="table table-bordered data-table">
                                <thead>
                                    <tr>
                                        <th>Gender</th>
                                        <th>Count</th>
                                        <th>Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $totalWithGender = array_sum(array_column($genderStats, 'count'));
                                    foreach ($genderStats as $gender): 
                                        $percentage = $totalWithGender > 0 ? ($gender['count'] / $totalWithGender) * 100 : 0;
                                    ?>
                                        <tr>
                                            <td><?= htmlspecialchars($gender['gender'] ?? 'Not specified') ?></td>
                                            <td><?= $gender['count'] ?></td>
                                            <td>
                                                <div class="progress">
                                                    <div class="progress-bar" role="progressbar" 
                                                         style="width: <?= $percentage ?>%; background-color: <?= getGenderColor($gender['gender']) ?>;" 
                                                         aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100">
                                                        <?= round($percentage, 1) ?>%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Existing Notification Management Section -->
        <!-- ... -->
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>

    <script>
        // Category Chart
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        const categoryChart = new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_column($categoryStats, 'category')) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($categoryStats, 'count')) ?>,
                    backgroundColor: [
                        '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796'
                    ],
                    hoverBackgroundColor: [
                        '#2e59d9', '#17a673', '#2c9faf', '#dda20a', '#be2617', '#6c757d'
                    ],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }],
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });

        // Gender Chart
        const genderCtx = document.getElementById('genderChart').getContext('2d');
        const genderChart = new Chart(genderCtx, {
            type: 'pie',
            data: {
                labels: <?= json_encode(array_column($genderStats, 'gender')) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($genderStats, 'count')) ?>,
                    backgroundColor: [
                        '#4e73df', '#e83e8c', '#858796'
                    ],
                    hoverBackgroundColor: [
                        '#2e59d9', '#d71e7c', '#6c757d'
                    ],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }],
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>

<?php
function getCategoryColor($category) {
    $colors = [
        'General' => '#4e73df',
        'OBC' => '#1cc88a',
        'SC' => '#36b9cc',
        'ST' => '#f6c23e',
        'EWS' => '#e74a3b'
    ];
    return $colors[$category] ?? '#858796';
}

function getGenderColor($gender) {
    $colors = [
        'Male' => '#4e73df',
        'Female' => '#e83e8c',
        'Other' => '#858796'
    ];
    return $colors[$gender] ?? '#858796';
}
?>