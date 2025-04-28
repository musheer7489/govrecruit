<?php
session_start();
include 'config.php';
$title_text = "Payment";
include 'header.php';
?>

<div class="container mt-4">
    <h2 class="text-center">Complete Your Payment</h2>
    <div id="loadingOverlay">
        <div id="loadingSpinner"></div>
    </div>
    <div id="payment-info" class="text-center mt-3">
    </div>
    <button id="pay-btn" class="btn btn-primary mt-3" style="display:none;">Pay Now</button>
</div>

<!-- Razorpay -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script src="sweet_alert.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        $("#loadingOverlay").fadeIn();
        fetch("fetch_payment_details.php")
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    $("#loadingOverlay").fadeOut();
                    document.getElementById("payment-info").innerHTML = `<p>Amount to Pay: ₹${data.amount}</p>`;
                    document.getElementById("pay-btn").style.display = "block";

                    document.getElementById("pay-btn").addEventListener("click", function() {
                        $("#loadingOverlay").fadeIn();
                        document.getElementById("pay-btn").style.display = "none";
                        var options = {
                            "key": "<?= RAZORPAY_KEY_ID ?>",
                            "amount": data.amount * 100, // Convert to paisa
                            "currency": "INR",
                            "name": "<?= COMPANY_NAME ?>",
                            "description": "Application Fee",
                            "image": "<?= COMPANY_LOGO_URL ?>",
                            "order_id": data.order_id,
                            "prefill": {
                                "name": data.name,
                                "email": data.email,
                                "contact": data.mobile
                            },
                            "handler": function(response) {
                                fetch("verify_payment.php", {
                                        method: "POST",
                                        headers: {
                                            "Content-Type": "application/json"
                                        },
                                        body: JSON.stringify({
                                            payment_id: response.razorpay_payment_id,
                                            order_id: response.razorpay_order_id,
                                            status: response.status,
                                            amount: data.amount
                                        })
                                    })
                                    .then(res => res.json())
                                    .then(result => {
                                        if (result.status == "success") {
                                            $("#loadingOverlay").fadeOut();
                                            Swal.fire({
                                                title: "Alert!",
                                                text: result.message,
                                                icon: "success",
                                                confirmButtonColor: "#3085d6",
                                                confirmButtonText: "ok!"
                                            }).then((result) => {
                                                if (result.isConfirmed) {
                                                    window.location.href = "dashboard";
                                                }
                                            });
                                            /* alert(result.message);
                                            if (result.status === "success") {
                                                window.location.href = "dashboard";
                                            } */
                                        } else {
                                            window.location.href = "dashboard";
                                        }
                                    });
                            },
                            "theme": {
                                "color": "#528FF0"
                            }
                        };
                        var rzp1 = new Razorpay(options);
                        rzp1.open();
                    });
                } else {
                    document.getElementById("payment-info").innerHTML = `<p class="text-danger">${data.message}</p>`;
                    window.location.href = "dashboard";
                }
            });
    });
</script>

<?php include 'footer.php'; ?>