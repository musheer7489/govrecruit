<?php
session_start();
include 'config.php';
// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$stmt = $conn->prepare("SELECT name, email, mobile, personal_info, address, education, experience, photo_signature FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
// Fetch Payment completion status
$stmt = $conn->prepare("SELECT * FROM payments WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();

// Decode JSON fields
$personal_info = json_decode($user['personal_info'], true);
$address = json_decode($user['address'], true);
$education = json_decode($user['education'], true);
$experience = json_decode($user['experience'], true);
$photo_signature = json_decode($user['photo_signature'], true);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Application</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="assets/styles.css">
    <link rel="shortcut icon" href="<?=COMPANY_FAVICON?>" type="image/x-icon">
</head>

<body>
    <h2 class="text-center">Form Preview</h2>
    <div class="container mt-5">
        <div class="header-img">
            <img src="<?=COMPANY_HEADER_IMG?>" alt="header" width="100%">
        </div>

        <div class="card p-4 shadow-lg">
            <h4 class="text-center">Personal Information</h4>
            <table class="table table-bordered">
                <tr>
                    <th>Full Name</th>
                    <td><?= htmlspecialchars($user['name']) ?></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                </tr>
                <tr>
                    <th>Mobile</th>
                    <td><?= htmlspecialchars($user['mobile']) ?></td>
                </tr>
                <tr>
                    <th>Category</th>
                    <td><?= htmlspecialchars($personal_info['category'] ?? '') ?></td>
                </tr>
                <tr>
                    <th>Gender</th>
                    <td><?= htmlspecialchars($personal_info['gender'] ?? '') ?></td>
                </tr>
                <tr>
                    <th>Date of Birth</th>
                    <td><?= htmlspecialchars(date("d-m-Y",strtotime($personal_info['dob'])) ?? '') ?></td>
                </tr>
                <tr>
                    <th>Disability</th>
                    <td><?= htmlspecialchars($personal_info['disability'] ?? 'No') ?></td>
                </tr>
                <tr>
                    <th>Marrital Status</th>
                    <td><?= htmlspecialchars($personal_info['marital'] ?? 'No') ?></td>
                </tr>
                <tr>
                    <th>Ex-Serviceman</th>
                    <td><?= htmlspecialchars($personal_info['exman'] ?? 'No') ?></td>
                </tr>
                <tr>
                    <th>Nationality</th>
                    <td><?= htmlspecialchars($personal_info['nationality'] ?? 'No') ?></td>
                </tr>
            </table>

            <h4 class="text-center">Complete Address</h4>
            <table class="table table-bordered">
                <tr>
                    <th>Full Address</th>
                    <td><?= htmlspecialchars($address['full_address'] ?? '') ?></td>
                </tr>
                <tr>
                    <th>State</th>
                    <td><?= htmlspecialchars($address['state'] ?? '') ?></td>
                </tr>
                <tr>
                    <th>City</th>
                    <td><?= htmlspecialchars($address['city'] ?? '') ?></td>
                </tr>
                <tr>
                    <th>PIN Code</th>
                    <td><?= htmlspecialchars($address['pincode'] ?? '') ?></td>
                </tr>
            </table>

            <h4 class="text-center">Educational Information</h4>
            <table class="table table-bordered">
                <tr>
                    <th>Matric (10th)</th>
                    <td>Year: <?= htmlspecialchars($education['matriculation']['passing_year'] ?? '') ?>, Board: <?= htmlspecialchars($education['matriculation']['board'] ?? '') ?>, School: <?= htmlspecialchars($education['matriculation']['college'] ?? '') ?>, Percentage: <?= htmlspecialchars($education['matriculation']['percentage'] ?? '') ?>%</td>
                </tr>
                <tr>
                    <th>Intermediate (12th)</th>
                    <td>Year: <?= htmlspecialchars($education['intermediate']['passing_year'] ?? '') ?>, Board: <?= htmlspecialchars($education['intermediate']['board'] ?? '') ?>, College: <?= htmlspecialchars($education['intermediate']['college'] ?? '') ?>, Percentage: <?= htmlspecialchars($education['intermediate']['percentage'] ?? '') ?>%</td>
                </tr>
                <tr>
                    <th>Graduation</th>
                    <td>Year: <?= htmlspecialchars($education['graduation']['passing_year'] ?? '') ?>, College: <?= htmlspecialchars($education['graduation']['college'] ?? '') ?>, College: <?= htmlspecialchars($education['graduation']['college'] ?? '') ?>, Percentage: <?= htmlspecialchars($education['graduation']['percentage'] ?? '') ?>%</td>
                </tr>
            </table>

            <h4 class="text-center">Work Experience</h4>
            <table class="table table-bordered">
                <tr>
                    <th>Company Name</th>
                    <th>Job Role</th>
                    <th>Total Years</th>
                </tr>
                <?php foreach ($experience as $exp) { ?>
                    <tr>
                        <td><?= htmlspecialchars($exp['company'] ?? '') ?></td>
                        <td><?= htmlspecialchars($exp['job_title'] ?? '') ?></td>
                        <td><?= htmlspecialchars($exp['experience_years'] ?? '') ?> years</td>
                    </tr>
                <?php } ?>
            </table>
            <h4 class="text-center">Payment Status</h4>
            <table class="table table-bordered">
                <tr>
                    <th>Status</th>
                    <th>Amount</th>
                    <th>Payment ID</th>
                </tr>
                <?php if (!empty($payment)) { ?>
                    <tr>
                        <td><?= htmlspecialchars($payment['payment_status'] ?? '') ?></td>
                        <td><?= htmlspecialchars($payment['amount'] ?? '') ?></td>
                        <td><?= htmlspecialchars($payment['transaction_id'] ?? '') ?> years</td>
                    </tr>
                <?php } else { ?>
                    <td>Not Available</td>
                    <td>NA</td>
                    <td>NA</td>
                <?php } ?>
            </table>

            <h4 class="text-center">Photo & Signature</h4>
            <div class="d-flex justify-content-around">
                <div>
                    <p class="text-center">Photo</p>
                    <img src="uploads/<?= htmlspecialchars($photo_signature['photo'] ?? 'default_photo.png') ?>" alt="User Photo" class="img-thumbnail" width="150">
                </div>
                <div>
                    <p class="text-center">Signature</p>
                    <img src="uploads/<?= htmlspecialchars($photo_signature['signature'] ?? 'default_signature.png') ?>" alt="User Signature" class="img-thumbnail" width="150">
                </div>
            </div>
        </div>

        <div class="mt-4 text-center mb-4">
            <a href="dashboard" class="btn btn-secondary mr-2">Back to Dashboard</a>
            <button class="btn btn-success" onclick="window.print()">Print</button>
        </div>
    </div>
    <!-- Footer 
    <footer class="footer bg-info text-dark mt-5">
        <div class="container py-3 text-center">
            <p class="mb-0">&copy; <?php echo date("Y"); echo ' '.COMPANY_NAME;?>. All Rights Reserved.</p>
            <p class="mb-0">
                <a href="terms" class="text-dark">Terms of Service</a> |
                <a href="privacy" class="text-dark">Privacy Policy</a>
            </p>
        </div>
    </footer> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/script.js"></script>
</body>

</html>