<?php
session_start();
include 'config.php';
$title_text = "Personal Info";
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);
    $fathername = trim($_POST['fathername']);
    $mothername = trim($_POST['mothername']);
    $gender = trim($_POST['gender']);
    $dob = trim($_POST['dob']);
    $aadhar = trim($_POST['aadhar']);
    $category = trim($_POST['category']);
    $marital = trim($_POST['marital']);
    $nationality = trim($_POST['nationality']);
    $exman = trim($_POST['exman']);
    $disability = trim($_POST['disability']);

    $stmt = $conn->prepare("UPDATE users SET personal_info = ? WHERE id = ?");
    $data = json_encode([
        'fullname' => $fullname,
        'email' => $dob,
        'mobile' => $gender,
        'fathername' => $fathername,
        'mothername' => $mothername,
        'gender' => $gender,
        'dob' => $dob,
        'aadhar' => $aadhar,
        'category' => $category,
        'marital' => $marital,
        'nationality' => $nationality,
        'exman' => $exman,
        'disability' => $disability
    ]);
    $stmt->bind_param("si", $data, $user_id);

    if ($stmt->execute()) {
        header("Location: dashboard");
        exit();
    } else {
        $error = "Something went wrong!";
    }
}

$user = $conn->query("SELECT name, email, mobile, personal_info, is_final_submitted FROM users WHERE id = $user_id")->fetch_assoc();
$personal_info = !empty($user['personal_info']) ? json_decode($user['personal_info'], true) : [];

if ($user['is_final_submitted']) {
    echo "<div class='container mt-4'><div class='alert alert-danger'>You have already submitted your profile. No further changes allowed.</div></div>";
    include 'footer.php';
    exit();
}
?>

<div class="container mt-4">
    <h3>Step 1: Personal Information</h3>

    <?php if (isset($error)) : ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form id="personalSubmit" method="post">
        <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" class="form-control" name="fullname" value="<?php echo $user['name'] ?? ''; ?>" readonly>
        </div>
        <div class="row mb-3">
            <div class="col">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" value="<?php echo $user['email'] ?? ''; ?>" readonly>
            </div>
            <div class="col">
                <label class="form-label">Mobile Number</label>
                <input type="number" class="form-control" name="mobile" value="<?php echo $user['mobile'] ?? ''; ?>" readonly>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col">
                <label for="fathername" class="form-label">Father's Name</label>
                <input type="text" class="form-control" name="fathername" value="<?php echo $personal_info['fathername'] ?? ''; ?>">
            </div>
            <div class="col">
                <label for="mothername" class="form-label">Mother's Name</label>
                <input type="text" class="form-control" name="mothername" value="<?php echo $personal_info['mothername'] ?? ''; ?>">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col">
                <label class="form-label">Date of Birth</label>
                <input type="date" class="form-control" name="dob" value="<?php echo $personal_info['dob'] ?? ''; ?>" required>
            </div>
            <div class="col">
                <label class="form-label">Gender</label>
                <select class="form-control" name="gender" required>
                    <option value="Male" <?php echo ($personal_info['gender'] ?? '') == "Male" ? 'selected' : ''; ?>>Male</option>
                    <option value="Female" <?php echo ($personal_info['gender'] ?? '') == "Female" ? 'selected' : ''; ?>>Female</option>
                    <option value="Other" <?php echo ($personal_info['gender'] ?? '') == "Other" ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col">
                <label class="form-label">Aadhaar Number:</label>
                <input type="number" class="form-control" name="aadhar" value="<?php echo $personal_info['aadhar'] ?? ''; ?>" required>
            </div>
            <div class="col">
                <label class="form-label">Category/Caste:</label>
                <select class="form-control" name="category" required>
                    <option value="General" <?php echo ($personal_info['category'] ?? '') == "General" ? 'selected' : ''; ?>>General</option>
                    <option value="OBC" <?php echo ($personal_info['category'] ?? '') == "OBC" ? 'selected' : ''; ?>>OBC</option>
                    <option value="EWS" <?php echo ($personal_info['category'] ?? '') == "EWS" ? 'selected' : ''; ?>>EWS</option>
                    <option value="SC" <?php echo ($personal_info['category'] ?? '') == "SC" ? 'selected' : ''; ?>>SC</option>
                    <option value="ST" <?php echo ($personal_info['category'] ?? '') == "ST" ? 'selected' : ''; ?>>ST</option>
                </select>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col">
                <label class="form-label">Marital Status:</label>
                <select class="form-control" name="marital" required>
                    <option value="Unmarried" <?php echo ($personal_info['marital'] ?? '') == "Unmarried" ? 'selected' : ''; ?>>Unmarried</option>
                    <option value="Married" <?php echo ($personal_info['marital'] ?? '') == "Married" ? 'selected' : ''; ?>>Married</option>
                    <option value="Divorce" <?php echo ($personal_info['marital'] ?? '') == "Divorce" ? 'selected' : ''; ?>>Divorce</option>
                </select>
            </div>
            <div class="col">
                <label class="form-label">Nationality:</label>
                <select class="form-control" name="nationality" required>
                    <option value="Indian" <?php echo ($personal_info['nationality'] ?? '') == "Indian" ? 'selected' : ''; ?>>Indian</option>
                    <option value="Other" <?php echo ($personal_info['nationality'] ?? '') == "Other" ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col">
                <label class="form-label">Are you Ex-Serviceman:</label>
                <select class="form-control" name="exman" required>
                    <option value="No" <?php echo ($personal_info['exman'] ?? '') == "No" ? 'selected' : ''; ?>>No</option>
                    <option value="Yes" <?php echo ($personal_info['exman'] ?? '') == "Yes" ? 'selected' : ''; ?>>Yes</option>
                </select>
            </div>
            <div class="col">
                <label class="form-label">Are you Disabled:</label>
                <select class="form-control" name="disability" required>
                    <option value="No" <?php echo ($personal_info['disability'] ?? '') == "No" ? 'selected' : ''; ?>>No</option>
                    <option value="Yes" <?php echo ($personal_info['disability'] ?? '') == "Yes" ? 'selected' : ''; ?>>Yes</option>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-success w-100">Save & Continue</button>
    </form>
</div>
<script src="sweet_alert.js"></script>
<script>
    $(document).ready(function() {
        $('#personalSubmit').submit(function(event) {
            event.preventDefault(); // Prevent default form submission

            Swal.fire({
                title: 'Are you Sure?',
                text: "Save Personal Information!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, submit it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // If user clicks "Yes, submit it!", submit the form
                    $(this).unbind('submit').submit(); // Unbind the current submit event, then resubmit.
                }
            });
        });
    });
</script>
<?php include 'footer.php'; ?>