<?php
include '../config.php';
$title_text = "Verify Password";
include 'header.php'; ?>

<!-- Full-Screen Loader -->
<div id="loadingOverlay">
    <div id="loadingSpinner"></div>
</div>
<div class="container mt-5 login-container">
    <h2 class="text-center">Verify OTP</h2>
    <form id="verifyOtpForm">
        <div class="mb-3">
            <label for="otp" class="form-label">Enter OTP</label>
            <input type="text" class="form-control" id="otp" name="otp" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Verify OTP</button>
    </form>
    <div id="message" class="mt-3"></div>
</div>
<script src="../sweet_alert.js"></script>
<script>
    document.getElementById("verifyOtpForm").addEventListener("submit", function(event) {
        event.preventDefault();
        $("#loadingOverlay").fadeIn();

        let formData = new FormData(this);
        fetch("verify_reset_otp_process", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                $("#loadingOverlay").fadeOut();
                document.getElementById("message").innerHTML = `<div class="alert alert-${data.status}">${data.message}</div>`;
                if (data.status === "success") {
                    Swal.fire({
                        icon: "success",
                        title: "OTP Verified!",
                        showConfirmButton: false,
                        timer: 1500
                    });
                    setTimeout(() => {
                        window.location.href = "reset_password";
                    }, 2000);
                }
            });
    });
</script>

<?php include '../footer.php'; ?>