<?php
include '../config.php';
$title_text = "Forgot Password";
include 'header.php'; 

session_start();
$job_data = "SELECT advertisement_number FROM advertisements";
$result = $conn->query($job_data);
?>

<!-- Full-Screen Loader -->
<div id="loadingOverlay">
    <div id="loadingSpinner"></div>
</div>
<div class="container mt-5 login-container">
    <h2 class="text-center">Forgot Password</h2>
    <form id="forgotPasswordForm">
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
            <label for="email" class="form-label">Enter Your Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Send OTP</button>
    </form>
    <div id="message" class="mt-3"></div>
</div>
<script src="../sweet_alert.js"></script>
<script>
    document.getElementById("forgotPasswordForm").addEventListener("submit", function(event) {
        event.preventDefault();
        $("#loadingOverlay").fadeIn();

        let formData = new FormData(this);
        fetch("send_otp.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById("message").innerHTML = `<div class="alert alert-${data.status}">${data.message}</div>`;
                $("#loadingOverlay").fadeOut();
                if (data.status === "success") {
                    Swal.fire({
                        icon: "success",
                        title: "Reset OTP Sent",
                        showConfirmButton: false,
                        timer: 1500
                    });
                    setTimeout(() => {
                        window.location.href = "verify_reset_otp";
                    }, 2000);
                }
            });
    });
</script>

<?php include '../footer.php'; ?>