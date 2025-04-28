<?php
session_start();
include 'config.php';
$title_text = "Experience";
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$user = $conn->query("SELECT experience, is_final_submitted FROM users WHERE id = $user_id")->fetch_assoc();
$experience = !empty($user['experience']) ? json_decode($user['experience'], true) : [];

if ($user['is_final_submitted']) {
    echo "<div class='container mt-4'><div class='alert alert-danger'>You have already submitted your profile. No further changes allowed.</div></div>";
    include 'footer.php';
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $experience_data = [];

    if (!empty($_POST['job_title'])) {
        for ($i = 0; $i < count($_POST['job_title']); $i++) {
            $experience_data[] = [
                'job_title' => trim($_POST['job_title'][$i]),
                'company' => trim($_POST['company'][$i]),
                'start_date' => trim($_POST['start_date'][$i]),
                'end_date' => trim($_POST['end_date'][$i]),
                'experience_years' => trim($_POST['experience_years'][$i])
            ];
        }
    }

    $experience_json = json_encode($experience_data);

    $stmt = $conn->prepare("UPDATE users SET experience = ? WHERE id = ?");
    $stmt->bind_param("si", $experience_json, $user_id);

    if ($stmt->execute()) {
        header("Location: dashboard");
        exit();
    } else {
        $error = "Something went wrong!";
    }
}
?>

<div class="container mt-4">
    <h3>Step 4: Work Experience</h3>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form id="experience" method="post">
        <div id="experience_container">
            <?php if (!empty($experience)): ?>
                <?php foreach ($experience as $exp): ?>
                    <div class="experience_block border p-3 mb-3">
                        <button type="button" class="btn btn-danger btn-sm float-end remove_exp">X</button>
                        <div class="mb-3">
                            <label class="form-label">Job Title</label>
                            <input type="text" class="form-control" name="job_title[]" value="<?php echo $exp['job_title']; ?>" >
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Company Name</label>
                            <input type="text" class="form-control" name="company[]" value="<?php echo $exp['company']; ?>" >
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" class="form-control" name="start_date[]" value="<?php echo $exp['start_date']; ?>" >
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">End Date</label>
                                <input type="date" class="form-control" name="end_date[]" value="<?php echo $exp['end_date']; ?>" >
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Experience (Years)</label>
                            <input type="number" class="form-control" name="experience_years[]" value="<?php echo $exp['experience_years']; ?>" >
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="experience_block border p-3 mb-3">
                    <button type="button" class="btn btn-danger btn-sm float-end remove_exp">X</button>
                    <div class="mb-3">
                        <label class="form-label">Job Title</label>
                        <input type="text" class="form-control" value="NA" name="job_title[]" >
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Company Name</label>
                        <input type="text" class="form-control" name="company[]" >
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" name="start_date[]" >
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" name="end_date[]" >
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Experience (Years)</label>
                        <input type="number" class="form-control" name="experience_years[]" >
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <button type="button" class="btn btn-primary mb-3" id="add_experience">+ Add Another Experience</button>

        <button type="submit" class="btn btn-success w-100">Save & Continue</button>
    </form>
</div>

<script>
    document.getElementById("add_experience").addEventListener("click", function() {
        let container = document.getElementById("experience_container");
        let newExp = document.querySelector(".experience_block").cloneNode(true);
        newExp.querySelectorAll("input").forEach(input => input.value = "");
        newExp.querySelector(".remove_exp").addEventListener("click", function() {
            newExp.remove();
        });
        container.appendChild(newExp);
    });

    document.querySelectorAll(".remove_exp").forEach(btn => {
        btn.addEventListener("click", function() {
            this.parentElement.remove();
        });
    });
</script>
<script src="sweet_alert.js"></script>
<script>
    $(document).ready(function() {
        $('#experience').submit(function(event) {
            event.preventDefault(); // Prevent default form submission

            Swal.fire({
                title: 'Are you Sure?',
                text: "Save your Experience!",
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
