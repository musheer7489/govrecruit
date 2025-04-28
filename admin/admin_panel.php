<?php
session_start();
// Authentication check
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index');
    exit;
}

// Database connection
include '../config.php';

// Search and filter parameters
$search = $_GET['search'] ?? '';
$payment_filter = $_GET['payment'] ?? 'all';

// Base query with JOIN between users and payments
$query = "SELECT u.id, u.name, u.email, u.is_verified, u.job_data, u.is_final_submitted, u.created_at, 
          p.payment_status as payment_status, p.amount, p.transaction_id, p.order_id
          FROM users u
          LEFT JOIN payments p ON u.id = p.user_id";

// Add conditions
$conditions = [];
$params = [];
$types = '';

if (!empty($search)) {
    $conditions[] = "(u.name LIKE ? OR u.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'ss';
}

if ($payment_filter !== 'all') {
    if ($payment_filter === 'success') {
        $conditions[] = "p.payment_status = 'success'";
    } elseif ($payment_filter === 'failed') {
        $conditions[] = "p.payment_status = 'failed'";
    } elseif ($payment_filter === 'unpaid') {
        $conditions[] = "(p.payment_status IS NULL OR p.payment_status = 'failed')";
    }
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(' AND ', $conditions);
}

// Ordering
$query .= " GROUP BY u.id ORDER BY u.created_at DESC";

// Debugging: Output the final query
// error_log("Final Query: " . $query);

// Prepare with error handling
$stmt = $conn->prepare($query);
if ($stmt === false) {
    die("Error preparing query: " . $conn->error);
}

if (!empty($params)) {
    if (!$stmt->bind_param($types, ...$params)) {
        die("Error binding parameters: " . $stmt->error);
    }
}

if (!$stmt->execute()) {
    die("Error executing query: " . $stmt->error);
}

$result = $stmt->get_result();
if ($result === false) {
    die("Error getting result: " . $stmt->error);
}

// Prepare and execute
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
include 'header.php'
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <h2 class="mb-4"><i class="fas fa-tachometer-alt"></i> Job Applications Admin Panel</h2>
            </div>

            <!-- Filter Card -->
            <div class="filter-card mb-4">
                <form method="get" class="row g-3">
                    <div class="col-md-5">
                        <input type="text" name="search" class="form-control" placeholder="Search name or email" value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-5">
                        <select name="payment" class="form-select">
                            <option value="all" <?= $payment_filter === 'all' ? 'selected' : '' ?>>All Payment Statuses</option>
                            <option value="success" <?= $payment_filter === 'success' ? 'selected' : '' ?>>Payment Success</option>
                            <option value="failed" <?= $payment_filter === 'failed' ? 'selected' : '' ?>>Payment Failed</option>
                            <option value="unpaid" <?= $payment_filter === 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter"></i> Filter</button>
                    </div>
                </form>
            </div>

            <!-- Applications Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="applicationsTable" class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Advertisement</th>

                                    <th>is_Submitted</th>
                                    <th>Payment Status</th>
                                    <th>Amount</th>
                                    <th>Transaction</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()) :
                                    $job_data = json_decode($row['job_data'], true);
                                    $advertisement_number = $job_data['advertisement_number'] ?? 'N/A';
                                ?>
                                    <tr>
                                        <td><?= $row['id'] ?></td>
                                        <td><?= htmlspecialchars($row['name']) ?></td>
                                        <td><?= htmlspecialchars($row['email']) ?></td>
                                        <td>
                                            <span class="badge badge-light"><?= htmlspecialchars($advertisement_number) ?></span>
                                        </td>

                                        <td>
                                            <?php if ($row['is_final_submitted'] == '1') : ?>
                                                <span class="status-success"><i class="fas fa-check-circle"></i> Submitted</span>
                                            <?php else : ?>
                                                <span class="status-failed"><i class="fas fa-times-circle"></i> Not Submitted</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($row['payment_status'] === 'success') : ?>
                                                <span class="status-success"><i class="fas fa-check-circle"></i> Success</span>
                                            <?php elseif ($row['payment_status'] === 'failed') : ?>
                                                <span class="status-failed"><i class="fas fa-times-circle"></i> Failed</span>
                                            <?php else : ?>
                                                <span class="status-pending"><i class="fas fa-clock"></i> Pending</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= $row['amount'] ? '₹' . number_format($row['amount'], 2) : '₹0.00' ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($row['transaction_id'])) : ?>
                                                <span class="badge badge-light"><?= substr($row['transaction_id'], 0, 8) ?>...</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary view-btn" data-id="<?= $row['id'] ?>" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-success payment-btn" data-id="<?= $row['id'] ?>" title="Update Payment">
                                                <i class="fas fa-rupee-sign"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger delete-btn" data-id="<?= $row['id'] ?>">
                                                <i class="fas fa-trash-alt"></i></button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Application Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-file-alt"></i> Application Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="applicationDetails">
                <!-- Content will be loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-rupee-sign"></i> Update Payment Status</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="paymentForm">
                <div class="modal-body">
                    <input type="hidden" name="user_id" id="paymentUserId">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="success">Success</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount (₹)</label>
                        <input type="number" name="amount" class="form-control" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Transaction ID</label>
                        <input type="text" name="transaction_id" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Order ID</label>
                        <input type="text" name="order_id" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Save Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        // Initialize DataTable with export buttons
        $('#applicationsTable').DataTable({
            dom: '<"top"Bf>rt<"bottom"lip><"clear">',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ],
            responsive: true,
            columnDefs: [{
                    responsivePriority: 1,
                    targets: 0
                },
                {
                    responsivePriority: 2,
                    targets: 1
                },
                {
                    responsivePriority: 3,
                    targets: -1
                }
            ]
        });

        // View Application
        $(document).on('click', '.view-btn', function() {
            const userId = $(this).data('id');
            $.get('get_application.php?id=' + userId, function(data) {
                $('#applicationDetails').html(data);
                $('#viewModal').modal('show');
            }).fail(function() {
                alert('Error loading application details');
            });
        });
        // Delete User
        $(document).on('click', '.delete-btn', function() {
            const userId = $(this).data('id');
            if (confirm("Are you sure you want to delete this item?")) {
                $.ajax({
                    url: 'delete_user.php', // Path to your PHP script
                    type: 'POST',
                    data: {
                        id: userId
                    },
                    dataType: 'json', // Expect JSON response from PHP
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: "success",
                                title: "Deleted!",
                                text: response.message,
                                confirmButtonText: "OK",
                            }).then((result) => {
                                /* Read more about isConfirmed, isDenied below */
                                if (result.isConfirmed) {
                                    window.location.reload();
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Oops!",
                                text: response.message,
                                confirmButtonText: "OK",
                            }).then((result) => {
                                /* Read more about isConfirmed, isDenied below */
                                if (result.isConfirmed) {
                                    window.location.reload();
                                }
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", error);
                        Swal.fire({
                                icon: "error",
                                title: "Oops!",
                                text: error,
                                confirmButtonText: "OK",
                            }).then((result) => {
                                /* Read more about isConfirmed, isDenied below */
                                if (result.isConfirmed) {
                                    window.location.reload();
                                }
                            });
                    }
                });
            }

        });

        // Payment Modal
        $(document).on('click', '.payment-btn', function() {
            const userId = $(this).data('id');
            $('#paymentUserId').val(userId);

            // Clear form
            $('#paymentForm')[0].reset();
            $('#paymentModal').modal('show');
        });

        // Save Payment
        $('#paymentForm').submit(function(e) {
            e.preventDefault();
            const formData = $(this).serialize();
            console.log(formData);
            $.post('update_payment.php', formData, function(response) {
                if (response.success) {
                    alert('Payment updated successfully!');
                    $('#paymentModal').modal('hide');
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            }, 'json').fail(function(response) {
                alert('Error processing request');
            });
        });
    });
</script>
</body>

</html>