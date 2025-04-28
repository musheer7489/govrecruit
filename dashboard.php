<?php
session_start();
include 'config.php';
$title_text = "Dashboard";
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's profile completion status
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$job_data = json_decode($user['job_data'], true);
$personal_info = json_decode($user['personal_info'], true);
// Fetch Payment completion status
$stmt = $conn->prepare("SELECT * FROM payments WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();


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

<div class="container mt-4">
    <h2>Welcome, <?php echo htmlspecialchars($user['name']); ?></h2>
    <div class="row">
        <div class="col-md-6">
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
                <form id="finalSubmitForm" class="mt-3">
                    <a href="form_preview" class="btn btn-secondary w-100 mb-2">Preview Form</a>
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
            <?php endif; ?>
        </div>
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <h5><i class="fas fa-briefcase"></i> Applied for Posts</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between">
                            <b>Advt Number: </b>
                            <p><?php echo $job_data['advertisement_number']; ?></p>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <b>Applied Posts : </b>
                            <ol>
                                <?php foreach ($job_data['applications'] as $job) { ?>
                                    <li><?php echo htmlspecialchars($job['post_title']); ?></li>
                                <?php } ?>
                            </ol>
                        </li>
                    </ul>
                </div>
            </div>
            <!-- Personal Info -->
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h5><i class="fas fa-info-circle"></i> Personal Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Name:</strong> <?= htmlspecialchars($user['name']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                    <p><strong>Mobile Number:</strong> <?= htmlspecialchars($user['mobile']) ?></p>
                    <?php if (!empty($personal_info['dob']) || !empty($personal_info['category'])) : ?>
                        <p><strong>Date of Birth:</strong> <?= htmlspecialchars(date("d-m-Y", strtotime($personal_info['dob']))) ?></p>
                        <p><strong>Category:</strong> <?= htmlspecialchars($personal_info['category']) ?></p>

                    <?php endif ?>
                    <p><strong>Applied On:</strong> <?= date('M d, Y h:i A', strtotime($user['created_at'])) ?></p>
                    <p><strong>Application Status:</strong>
                        <?php if (!$is_final_submitted) : ?>
                            <span class="badge bg-warning text-dark">Pending</span> - Waiting for Complete Application
                        <?php else : ?>
                            <?php if (!empty($payment)) : ?>
                                <?php if ($payment['payment_status'] === 'success') : ?>
                                    <span class="badge bg-success">Complete</span> - Your application is under review
                                <?php elseif ($payment['payment_status'] === 'failed') : ?>
                                    <span class="badge bg-danger">Payment Required</span> - Please complete your payment
                                <?php else : ?>
                                    <span class="badge bg-warning text-dark">Pending</span> - Waiting for payment confirmation
                                <?php endif; ?>
                            <?php else : ?>
                                <span class="badge bg-warning text-dark">Pending</span> - Waiting for payment confirmation
                            <?php endif; ?>
                        <?php endif ?>
                    </p>
                </div>
            </div>
            <!-- Payment History -->
            <?php if (!empty($payment)) : ?>
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-secondary text-white">
                                <h5><i class="fas fa-history"></i> Payment History</h5>
                            </div>
                            <div class="card-body">
                                <div class="timeline">
                                    <div class="timeline-item <?= $payment['payment_status'] === 'success' ? 'timeline-success' : 'timeline-failed' ?>">
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
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
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

<?php include 'footer.php'; ?>