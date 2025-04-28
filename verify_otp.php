<?php
session_start();
include 'config.php';
$title_text = "Verify Otp";
include 'header.php';

if (!isset($_GET['email']) || !isset($_GET['advertisement_number'])) {
    die("<div class='alert alert-danger'>Invalid Request! Try again.</div>");
}

$email = trim($_GET['email']);
$advertisement_number = trim($_GET['advertisement_number']);
?>
<div class="container mt-5">
    <!-- Full-Screen Loader -->
    <div id="loadingOverlay">
        <div id="loadingSpinner"></div>
    </div>
    <div id="otpMessage"></div>
    <div class="card border-success mb-3" style="max-width: 24rem; margin:auto;">
        <div class="card-header bg-success text-white">Verify OTP</div>
        <div class="card-body">
            <div class="alert alert-success">OTP Sent to your Email. Please Check your Email</div>
            <form id="otpForm">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                <input type="hidden" name="advertisement_number" value="<?php echo htmlspecialchars($advertisement_number); ?>">
                <input type="text" name="otp" class="form-control mb-2" placeholder="Enter OTP" required>
                <button type="submit" class="btn btn-success">Verify</button>
                <p id="timer"></p>
                <script>
                    function startTimer(duration) {
                        let timer = duration,
                            minutes, seconds;
                        let interval = setInterval(function() {
                            minutes = parseInt(timer / 60, 10);
                            seconds = parseInt(timer % 60, 10);
                            document.getElementById("timer").textContent = `OTP expires in ${minutes}m ${seconds}s`;
                            if (--timer < 0) {
                                clearInterval(interval);
                                document.getElementById("timer").textContent = "OTP expired. Resend OTP.";
                            }
                        }, 1000);
                    }
                    startTimer(300); // 5 minutes
                </script>

            </form>
            <button type="button" id="resendOtp" class="btn btn-warning">Resend OTP</button>
        </div>
    </div>
</div>
<script src="sweet_alert.js"></script>
<script>
    $(document).ready(function() {
        $("#otpForm").submit(function(e) {
            e.preventDefault();

            $("#loadingOverlay").fadeIn(); // Show full-screen loader

            $.ajax({
                type: "POST",
                url: "verify_otp_process.php",
                data: $(this).serialize(),
                dataType: "json",
                success: function(response) {
                    $("#loadingOverlay").fadeOut(); // Hide loader
                    if (response.status === "success") {
                        Swal.fire({
                            icon: "success",
                            title: "Successfully Registered",
                            showConfirmButton: false,
                            timer: 1500
                        });
                        $("#otpMessage").html("<div class='alert alert-success'>" + response.message + "</div>");
                        setTimeout(function() {
                            window.location.href = "login"; // Redirect to user dashboard
                        }, 2000);
                    } else {
                        $("#otpMessage").html("<div class='alert alert-danger'>" + response.message + "</div>");
                    }
                },
                error: function() {
                    $("#loadingOverlay").fadeOut(); // Hide loader
                    $("#otpMessage").html("<div class='alert alert-danger'>Something went wrong! Try again.</div>");
                }
            });
        });
        $("#resendOtp").click(function() {
            let email = "<?php echo $email; ?>";
            let advertisement_number = "<?php echo $advertisement_number; ?>";
            $("#loadingOverlay").fadeIn();

            $.ajax({
                type: "POST",
                url: "resendOtp.php",
                data: {
                    email: email,
                    advertisement_number : advertisement_number
                },
                dataType: "json",
                success: function(response) {
                    $("#loadingOverlay").fadeOut();
                    $("#otpMessage").html("<div class='alert alert-info'>" + response.message + "</div>");
                },
                error: function() {
                    $("#loadingOverlay").fadeOut();
                    $("#otpMessage").html("<div class='alert alert-danger'>Failed to resend OTP. Try again.</div>");
                }
            });
        });
    });
</script>
<?php include 'footer.php'; ?>