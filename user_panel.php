<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login'); // Redirect if not logged in
    exit;
}

// Database connection
include 'config.php';

// Get user data
$user_id = $_SESSION['user_id'];
$query = "SELECT u.*, p.payment_status as payment_status, p.amount, p.transaction_id, p.order_id, p.created_at as payment_date
          FROM users u
          LEFT JOIN payments p ON u.id = p.user_id
          WHERE u.id = ?
          ORDER BY p.created_at DESC LIMIT 1";

$stmt = $conn->prepare($query);
if ($stmt === false) {
    die("Error preparing query: " . $conn->error);
}

$stmt->bind_param('i', $user_id);
if (!$stmt->execute()) {
    die("Error executing query: " . $stmt->error);
}

$result = $stmt->get_result();
$user = $result->fetch_assoc();
$job_data = json_decode($user['job_data'], true);
$personal_info = json_decode($user['personal_info'], true);

// Get all payment history
$payment_history = [];
$history_query = "SELECT * FROM payments WHERE user_id = ? ORDER BY created_at DESC";
$history_stmt = $conn->prepare($history_query);
$history_stmt->bind_param('i', $user_id);
$history_stmt->execute();
$history_result = $history_stmt->get_result();
$payment = $history_result->fetch_assoc();

while ($row = $history_result->fetch_assoc()) {
    $payment_history[] = $row;
}

// Check how many steps are completed
$steps = ['personal_info', 'address', 'education', 'experience', 'photo_signature'];
$completed_steps = 0;
foreach ($steps as $step) {
    if (!empty($user[$step])) {
        $completed_steps++;
    }
}

$total_steps = count($steps);
$progress_percentage = ($completed_steps / $total_steps) * 100;
$is_final_submitted = $user['is_final_submitted'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Applications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .card {
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            border-radius: 10px 10px 0 0 !important;
        }

        .status-success {
            color: #28a745;
            font-weight: bold;
        }

        .status-failed {
            color: #dc3545;
            font-weight: bold;
        }

        .status-pending {
            color: #ffc107;
            font-weight: bold;
        }

        .badge-light {
            background-color: #e9ecef;
            color: #495057;
        }

        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline-item {
            position: relative;
            padding-bottom: 20px;
        }

        .timeline-item:before {
            content: '';
            position: absolute;
            left: -15px;
            top: 0;
            width: 2px;
            height: 100%;
            background: #dee2e6;
        }

        .timeline-dot {
            position: absolute;
            left: -21px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #6c757d;
        }

        .timeline-success .timeline-dot {
            background: #28a745;
        }

        .timeline-failed .timeline-dot {
            background: #dc3545;
        }
    </style>
</head>

<body>
    <div class="container py-4">
        <div class="row">
            <!-- Steps Completed -->
            <div class="col-12">
                <div class="row mb-4">
                    <div class="col-12">
                        <h4><i class="fas fa-user-circle"></i> <?= htmlspecialchars($user['name']) ?></h4>
                        <p class="text-muted">Welcome back! Here's your application status.</p>
                    </div>
                </div>
                <p>Complete your profile step by step.</p>

                <div class="progress mb-3">
                    <div class="progress-bar" role="progressbar" style="width: <?php echo $progress_percentage; ?>%;" aria-valuenow="<?php echo $progress_percentage; ?>" aria-valuemin="0" aria-valuemax="100">
                        <?php echo round($progress_percentage); ?>%
                    </div>
                </div>

                <ul class="list-group">
                    <?php foreach ($steps as $index => $step) : ?>
                        <li class="list-group-item d-flex justify-content-between">
                            Step <?php echo $index + 1; ?>: <?php echo ucfirst(str_replace("_", " ", $step)); ?>
                            <?php if (!empty($user[$step])) : ?>
                                <a href="step_<?php echo $step; ?>" class="btn btn-sm btn-success">Completed</a>
                            <?php else : ?>
                                <a href="step_<?php echo $step; ?>" class="btn btn-sm btn-primary">Complete</a>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <?php if ($completed_steps == $total_steps && !$is_final_submitted) : ?>
                    <div class="card mt-4">
                        <div class="card-header bg-dark text-white">
                            <h5><i class="fas fa-download"></i> Application Receipt</h5>
                        </div>
                        <div class="card-body text-center">
                            <form id="finalSubmitForm" class="mt-3">
                                <button type="submit" class="btn btn-danger w-100">Final Submit</button>
                            </form>
                        <?php elseif ($is_final_submitted) : ?>
                            <p class="text-center text-danger mt-3"><strong>You have already submitted your profile. No changes allowed.</strong></p>
                            <?php if (!empty($payment)) : ?>
                                <?php if ($payment['payment_status'] !== 'success') : ?>
                                    <a href="payment" class="btn btn-primary w-100 mb-2">Proceed to Payment</a>
                                    <a href="form_preview" class="btn btn-secondary w-100">Preview Form</a>
                                <?php else : ?>
                                    <a href="print_form" class="btn btn-success w-100">Print Form</a>
                                <?php endif; ?>
                            <?php else : ?>
                                <a href="payment" class="btn btn-primary w-100 mb-2">Proceed to Payment</a>
                                <a href="form_preview" class="btn btn-secondary w-100">Preview Form</a>
                            <?php endif; ?>
                        </div>
                    </div>

                <?php endif; ?>
            </div>
        </div>
        <div class="row">
            <!-- User Summary -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5><i class="fas fa-info-circle"></i> My Information</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Name:</strong> <?= htmlspecialchars($user['name']) ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                        <p><strong>Mobile Number:</strong> <?= htmlspecialchars($user['mobile']) ?></p>
                        <?php if ($personal_info['dob'] != '' && $personal_info['category'] != '') : ?>
                            <p><strong>Date of Birth:</strong> <?= htmlspecialchars($personal_info['dob']) ?></p>
                            <p><strong>Category:</strong> <?= htmlspecialchars($personal_info['category']) ?></p>

                        <?php endif ?>
                        <p><strong>Applied On:</strong> <?= date('M d, Y h:i A', strtotime($user['created_at'])) ?></p>
                        <p><strong>Advertisement No:</strong>
                            <span class="badge badge-light"><?= htmlspecialchars($job_data['advertisement_number'] ?? 'N/A') ?></span>
                        </p>
                    </div>
                </div>

                <?php if (!empty($payment)) : ?>
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5><i class="fas fa-credit-card"></i> Payment Status</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($user['payment_status'] === 'success') : ?>
                                <p class="status-success"><i class="fas fa-check-circle"></i> Payment Successful</p>
                            <?php elseif ($user['payment_status'] === 'failed') : ?>
                                <p class="status-failed"><i class="fas fa-times-circle"></i> Payment Failed</p>
                            <?php else : ?>
                                <p class="status-pending"><i class="fas fa-clock"></i> Payment Pending</p>
                            <?php endif; ?>

                            <?php if (!empty($user['amount'])) : ?>
                                <p><strong>Amount:</strong> ₹<?= number_format($user['amount'], 2) ?></p>
                            <?php endif; ?>

                            <?php if (!empty($user['transaction_id'])) : ?>
                                <p><strong>Transaction ID:</strong> <?= htmlspecialchars($user['transaction_id']) ?></p>
                            <?php endif; ?>

                            <?php if (!empty($user['order_id'])) : ?>
                                <p><strong>Order ID:</strong> <?= htmlspecialchars($user['order_id']) ?></p>
                            <?php endif; ?>

                            <?php if (!empty($user['payment_date'])) : ?>
                                <p><strong>Processed On:</strong> <?= date('M d, Y h:i A', strtotime($user['payment_date'])) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif ?>

                <!-- Job Applications -->
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5><i class="fas fa-briefcase"></i> Applied for Posts</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($job_data['applications'])) : ?>
                            <?php foreach ($job_data['applications'] as $application) : ?>
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5><?= htmlspecialchars($application['post_title']) ?></h5>
                                    </div>
                                    <div class="card-body">
                                        <h6>Advertisement Number:</h6>
                                        <p><?= htmlspecialchars($job_data['advertisement_number'] ?? 'N/A') ?></p>

                                        <h6>Required Qualifications:</h6>
                                        <p><?= nl2br(htmlspecialchars($application['qualifications'])) ?></p>

                                        <h6>Application Status:</h6>
                                        <p>
                                            <?php if ($user['payment_status'] === 'success') : ?>
                                                <span class="badge bg-success">Complete</span> - Your application is under review
                                            <?php elseif ($user['payment_status'] === 'failed') : ?>
                                                <span class="badge bg-danger">Payment Required</span> - Please complete your payment
                                            <?php else : ?>
                                                <span class="badge bg-warning text-dark">Pending</span> - Waiting for payment confirmation
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <p>No applications found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
    </div>
        </div>

        <!-- Payment History -->
        <?php if (!empty($payment_history)) : ?>
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <h5><i class="fas fa-history"></i> Payment History</h5>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                <?php foreach ($payment_history as $payment) : ?>
                                    <div class="timeline-item <?= $payment['status'] === 'success' ? 'timeline-success' : 'timeline-failed' ?>">
                                        <div class="timeline-dot"></div>
                                        <h5>
                                            <?= ucfirst($payment['payment_status']) ?> Payment - ₹<?= number_format($payment['amount'], 2) ?>
                                            <small class="text-muted"><?= date('M d, Y h:i A', strtotime($payment['created_at'])) ?></small>
                                        </h5>
                                        <div class="pl-3">
                                            <?php if (!empty($payment['transaction_id'])) : ?>
                                                <p><strong>Transaction ID:</strong> <?= htmlspecialchars($payment['transaction_id']) ?></p>
                                            <?php endif; ?>
                                            <?php if (!empty($payment['order_id'])) : ?>
                                                <p><strong>Order ID:</strong> <?= htmlspecialchars($payment['order_id']) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>


    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="sweet_alert.js"></script>
    <script>
        $(document).ready(function() {
            $("#finalSubmitForm").submit(function(e) {
                e.preventDefault();
                Swal.fire({
                    title: "Are you sure you want to submit your profile? You won't be able to edit it later.?",
                    showCancelButton: true,
                    confirmButtonText: "Yes",
                }).then((result) => {
                    /* Read more about isConfirmed, isDenied below */
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "POST",
                            url: "final_submit.php",
                            data: {
                                submit: true
                            },
                            success: function(response) {
                                Swal.fire({
                                    title: "Alert!",
                                    text: response,
                                    icon: "success",
                                    confirmButtonColor: "#3085d6",
                                    confirmButtonText: "ok!"
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        location.reload();
                                    }
                                });
                            }
                        });

                    }
                });
            });
        });
    </script>
    <script>
        // PDF download functionality
        document.getElementById('downloadPdf').addEventListener('click', function() {
            function downloadExternalPageAsPdf(url, filename) {
                if (typeof jsPDF === 'undefined') {
                    console.error('jsPDF is not loaded. Please include it in your HTML.');
                    return;
                }

                const pdf = new jsPDF('p', 'pt', 'a4');
                const pageWidth = pdf.internal.pageSize.getWidth();
                const pageHeight = pdf.internal.pageSize.getHeight();
                const margins = {
                    top: 40,
                    bottom: 40,
                    left: 40,
                    width: pageWidth - 80,
                };

                const iframe = document.createElement('iframe');
                iframe.style.display = 'none';
                document.body.appendChild(iframe);

                iframe.onload = function() {
                    const iframeDocument = iframe.contentDocument || iframe.contentWindow.document;
                    const iframeBody = iframeDocument.body;

                    if (!iframeBody) {
                        console.error('Could not access iframe body content.');
                        document.body.removeChild(iframe);
                        return;
                    }

                    pdf.html(iframeBody, {
                        x: margins.left,
                        y: margins.top,
                        width: margins.width,
                        windowWidth: iframeBody.scrollWidth,
                        callback: function(pdf) {
                            pdf.save(filename || 'preview_page.pdf');
                            document.body.removeChild(iframe);
                        },
                    });
                };

                iframe.src = url;
            }

            downloadExternalPageAsPdf('form_preview', 'preview_page.pdf');
        });
    </script>
</body>

</html>
<?php
$conn->close();
?>