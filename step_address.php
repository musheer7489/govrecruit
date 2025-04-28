<?php
session_start();
include 'config.php';
$title_text = "Address";
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$user = $conn->query("SELECT address, is_final_submitted FROM users WHERE id = $user_id")->fetch_assoc();
$address = !empty($user['address']) ? json_decode($user['address'], true) : [];

if ($user['is_final_submitted']) {
    echo "<div class='container mt-4'><div class='alert alert-danger'>You have already submitted your profile. No further changes allowed.</div></div>";
    include 'footer.php';
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $full_address = trim($_POST['full_address']);
    $state = trim($_POST['state']);
    $city = trim($_POST['city']);
    $pincode = trim($_POST['pincode']);

    $address_data = json_encode([
        'full_address' => $full_address,
        'state' => $state,
        'city' => $city,
        'pincode' => $pincode
    ]);

    $stmt = $conn->prepare("UPDATE users SET address = ? WHERE id = ?");
    $stmt->bind_param("si", $address_data, $user_id);

    if ($stmt->execute()) {
        header("Location: dashboard");
        exit();
    } else {
        $error = "Something went wrong!";
    }
}
?>

<div class="container mt-4">
    <h3>Step 2: Complete Address</h3>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form id="address" method="post">
        <div class="mb-3">
            <label class="form-label">Full Address</label>
            <input type="text" class="form-control" name="full_address" value="<?php echo $address['full_address'] ?? ''; ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">State</label>
            <select class="form-control" id="state" name="state" required>
                <option value="<?php echo $address['state'] ?? ''; ?>"><?php echo $address['state'] ?? 'Select State'; ?></option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">City</label>
            <select class="form-control" id="city" name="city" required>
                <option value="<?php echo $address['city'] ?? ''; ?>"><?php echo $address['city'] ?? 'Select City'; ?></option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">PIN Code</label>
            <input type="text" class="form-control" name="pincode" value="<?php echo $address['pincode'] ?? ''; ?>" required pattern="\d{6}" title="Enter a valid 6-digit PIN code">
        </div>
        <button type="submit" class="btn btn-success w-100">Save & Continue</button>
    </form>
</div>

<script>
$(document).ready(function() {
    function loadStates() {
        $.ajax({
            url: "fetch_states.php",
            type: "GET",
            success: function(data) {
                $("#state").html('<option value="">Select State</option>' + data);
            }
        });
    }

    $("#state").change(function() {
        let state = $(this).val();
        if (state) {
            $.ajax({
                url: "fetch_cities.php",
                type: "POST",
                data: { state: state },
                success: function(data) {
                    $("#city").html('<option value="">Select City</option>' + data);
                }
            });
        } else {
            $("#city").html('<option value="">Select City</option>');
        }
    });

    loadStates();
});
</script>
<script src="sweet_alert.js"></script>
<script>
    $(document).ready(function() {
        $('#address').submit(function(event) {
            event.preventDefault(); // Prevent default form submission

            Swal.fire({
                title: 'Are you Sure?',
                text: "Save your Address!",
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
