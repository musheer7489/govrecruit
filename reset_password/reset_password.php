<?php
include '../config.php';
$title_text = "Reset Password";
include 'header.php'; ?>

<!-- Full-Screen Loader -->
<div id="loadingOverlay">
    <div id="loadingSpinner"></div>
</div>
<div class="container login-container mt-5">
    <h2 class="text-center">Reset Password</h2>
    <form id="resetPasswordForm">
        <div class="mb-3">
            <label for="password" class="form-label">New Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Reset Password</button>
    </form>
    <div id="message" class="mt-3"></div>
</div>
<script src="../sweet_alert.js"></script>
<script>
    document.getElementById("resetPasswordForm").addEventListener("submit", function(event) {
        event.preventDefault();
        $("#loadingOverlay").fadeIn();

        let formData = new FormData(this);
        fetch("reset_password_process.php", {
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
                        title: "Password Reset!",
                        showConfirmButton: false,
                        timer: 1500
                    });
                    setTimeout(() => {
                        window.location.href = "../login";
                    }, 2000);
                }
            });
    });
</script>

<?php include '../footer.php'; ?>