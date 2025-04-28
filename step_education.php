<?php
session_start();
include 'config.php';
$title_text = "Education";
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$user = $conn->query("SELECT education, is_final_submitted FROM users WHERE id = $user_id")->fetch_assoc();
$education = !empty($user['education']) ? json_decode($user['education'], true) : [];

if ($user['is_final_submitted']) {
    echo "<div class='container mt-4'><div class='alert alert-danger'>You have already submitted your profile. No further changes allowed.</div></div>";
    include 'footer.php';
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $matric = [
        'passing_year' => trim($_POST['matric_passing_year']),
        'college' => trim($_POST['matric_college']),
        'board' => trim($_POST['matric_board']),
        'percentage' => trim($_POST['matric_percentage'])
    ];
    
    $intermediate = [
        'passing_year' => trim($_POST['inter_passing_year']),
        'college' => trim($_POST['inter_college']),
        'board' => trim($_POST['inter_board']),
        'percentage' => trim($_POST['inter_percentage'])
    ];
    
    $graduation = [
        'passing_year' => trim($_POST['grad_passing_year']),
        'college' => trim($_POST['grad_college']),
        'board' => trim($_POST['grad_board']),
        'percentage' => trim($_POST['grad_percentage'])
    ];

    $education_data = json_encode([
        'matriculation' => $matric,
        'intermediate' => $intermediate,
        'graduation' => $graduation
    ]);

    $stmt = $conn->prepare("UPDATE users SET education = ? WHERE id = ?");
    $stmt->bind_param("si", $education_data, $user_id);

    if ($stmt->execute()) {
        header("Location: dashboard");
        exit();
    } else {
        $error = "Something went wrong!";
    }
}
?>

<div class="container mt-4">
    <h3>Step 3: Educational Information</h3>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form id="education" method="post">
        <h5>Matriculation (10th)</h5>
        <div class="mb-3">
            <label class="form-label">Passing Year</label>
            <input type="number" class="form-control" name="matric_passing_year" value="<?php echo $education['matriculation']['passing_year'] ?? ''; ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">School Name</label>
            <input type="text" class="form-control" name="matric_college" value="<?php echo $education['matriculation']['college'] ?? ''; ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Board Name</label>
            <input type="text" class="form-control" name="matric_board" value="<?php echo $education['matriculation']['board'] ?? ''; ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Percentage (%)</label>
            <input type="text" class="form-control" name="matric_percentage" value="<?php echo $education['matriculation']['percentage'] ?? ''; ?>" required>
        </div>

        <h5>Intermediate (12th)</h5>
        <div class="mb-3">
            <label class="form-label">Passing Year</label>
            <input type="number" class="form-control" name="inter_passing_year" value="<?php echo $education['intermediate']['passing_year'] ?? ''; ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">College Name</label>
            <input type="text" class="form-control" name="inter_college" value="<?php echo $education['intermediate']['college'] ?? ''; ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Board Name</label>
            <input type="text" class="form-control" name="inter_board" value="<?php echo $education['intermediate']['board'] ?? ''; ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Percentage (%)</label>
            <input type="text" class="form-control" name="inter_percentage" value="<?php echo $education['intermediate']['percentage'] ?? ''; ?>" required>
        </div>

        <h5>Graduation</h5>
        <div class="mb-3">
            <label class="form-label">Passing Year</label>
            <input type="number" class="form-control" name="grad_passing_year" value="<?php echo $education['graduation']['passing_year'] ?? ''; ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">College Name</label>
            <input type="text" class="form-control" name="grad_college" value="<?php echo $education['graduation']['college'] ?? ''; ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">University Name</label>
            <input type="text" class="form-control" name="grad_board" value="<?php echo $education['graduation']['board'] ?? ''; ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Percentage (%)</label>
            <input type="text" class="form-control" name="grad_percentage" value="<?php echo $education['graduation']['percentage'] ?? ''; ?>">
        </div>

        <button type="submit" class="btn btn-success w-100">Save & Continue</button>
    </form>
</div>
<script src="sweet_alert.js"></script>
<script>
    $(document).ready(function() {
        $('#education').submit(function(event) {
            event.preventDefault(); // Prevent default form submission

            Swal.fire({
                title: 'Are you Sure?',
                text: "Save your Educational details!",
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
