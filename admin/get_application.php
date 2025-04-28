<?php
require_once '../config.php';

$userId = $_GET['id'] ?? 0;
$stmt = $conn->prepare("SELECT u.*, p.payment_status as payment_status, p.amount, p.transaction_id, p.order_id, p.created_at as payment_date 
                     FROM users u LEFT JOIN payments p ON u.id = p.user_id WHERE u.id = ? ORDER BY p.created_at DESC LIMIT 1");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$application = $result->fetch_assoc();
$job_data = json_decode($application['job_data'], true);
$personal_info = json_decode($application['personal_info'], true);
?>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5><i class="fas fa-user"></i> Applicant Information</h5>
            </div>
            <div class="card-body">
                <p><strong>Name:</strong> <?= htmlspecialchars($application['name']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($application['email']) ?></p>
                <p><strong>is_verified:</strong>
                    <?php if ($application['is_verified'] == '1') : ?>
                        <span class="status-success"><i class="fas fa-check-circle"></i> Verified</span>
                    <?php else : ?>
                        <span class="status-failed"><i class="fas fa-times-circle"></i> Not Verified</span>
                    <?php endif; ?>
                </p>
                <p><strong>is_final_submitted:</strong>
                    <?php if ($application['is_final_submitted'] == '1') : ?>
                        <span class="status-success"><i class="fas fa-check-circle"></i> Submitted</span>
                    <?php else : ?>
                        <span class="status-failed"><i class="fas fa-times-circle"></i> Not Submitted</span>
                    <?php endif; ?>
                </p>
                <p><strong>Applied On:</strong> <?= date('M d, Y h:i A', strtotime($application['created_at'])) ?></p>
                <p><strong>Advertisement No:</strong> <?= htmlspecialchars($job_data['advertisement_number'] ?? 'N/A') ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5><i class="fas fa-credit-card"></i> Payment Information</h5>
            </div>
            <div class="card-body">
                <p><strong>Status:</strong>
                    <?php if ($application['payment_status'] === 'success') : ?>
                        <span class="badge bg-success">Success</span>
                    <?php elseif ($application['payment_status'] === 'failed') : ?>
                        <span class="badge bg-danger">Failed</span>
                    <?php else : ?>
                        <span class="badge bg-warning text-dark">Pending</span>
                    <?php endif; ?>
                </p>
                <p><strong>Amount:</strong> ₹<?= number_format($application['amount'] ?? 0, 2) ?></p>
                <?php if (!empty($application['transaction_id'])) : ?>
                    <p><strong>Transaction ID:</strong> <?= htmlspecialchars($application['transaction_id']) ?></p>
                <?php endif; ?>
                <?php if (!empty($application['order_id'])) : ?>
                    <p><strong>Order ID:</strong> <?= htmlspecialchars($application['order_id']) ?></p>
                <?php endif; ?>
                <?php if (!empty($application['payment_date'])) : ?>
                    <p><strong>Payment Date:</strong> <?= date('M d, Y h:i A', strtotime($application['payment_date'])) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-info text-white">
        <h5><i class="fas fa-briefcase"></i> Job Applications</h5>
    </div>
    <div class="card-body">
        <?php foreach ($job_data['applications'] as $app) : ?>
            <div class="card mb-3">
                <div class="card-header">
                    <h5><?= htmlspecialchars($app['post_title']) ?></h5>
                </div>
                <div class="card-body">
                    <h6>Qualifications:</h6>
                    <p><?= nl2br(htmlspecialchars($app['qualifications'])) ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>