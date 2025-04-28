<?php
session_start();
include 'config.php';
$title_text = "Login";
include 'header.php';

$job_data = "SELECT advertisement_number FROM advertisements";
$result = $conn->query($job_data);
?>

<div class="container mt-5 login-container">
    <h2>Login</h2>
    <div id="loginMessage"></div>
    <form id="loginForm">
        <div class="mb-3">
            <label for="advertisement_number" class="form-label">Select ADVT No</label>
            <select name="advertisement_number" id="advertisement_number" class="form-control mb-2">
                <?php if ($result->num_rows > 0) {
                    // output data of each row
                    while ($row = $result->fetch_assoc()) { ?>
                        <option value="<?php echo $row['advertisement_number']; ?>"><?php echo $row['advertisement_number']; ?></option>
                <?php }
                } else {
                    echo "0 results";
                }
                $conn->close(); ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" id="email" class="form-control mb-2" placeholder="Enter Your Email" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" name="password" id="password" class="form-control mb-2" placeholder="Enter Your Password" required>
        </div>
        <div class="d-grid">
            <button type="submit" class="btn btn-primary">Login</button>
        </div>
        <div class="mt-3 text-center">
            <a href="reset_password/forgot_password">Forgot Password?</a>
        </div>
    </form>
</div>
<script>
    $(document).ready(function() {
        $("#loginForm").submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: "login_process.php",
                type: "POST",
                data: $(this).serialize(),
                success: function(response) {
                    $("#loginMessage").html(response);
                }
            });
        });
    });
</script>
<?php include 'footer.php'; ?>