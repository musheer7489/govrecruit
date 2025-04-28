<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "otp_register";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Razorpay API Credentials
define("RAZORPAY_KEY_ID", "rzp_test_64ZQLjjTKXqotI");
define("RAZORPAY_KEY_SECRET", "3zqbJJaTOWsP4nkhBSqjD4uH");
define("COMPANY_NAME", "Cochin Shipyard Ltd");
define("COMPANY_LOGO_URL", "m-logo.png");
define("COMPANY_FAVICON", "m-logo.png");
define("COMPANY_HEADER_IMG", "assets/web-logo.png");
define("BASE_URL", "localhost/otp_register");
define("COMPANY_RECRUITMENT_EMAIL", "helpdesk@gmail.com");
define("COMPANY_EMAIL", "helpdesk@gmail.com");
define("COMPANY_WEBSITE", "http://company.com");
define("COMPANY_MOBILE", "+91 526 538 980");
define("COMPANY_MOBILE_ALTERNATE", "+91 526 538 980");
define("COMPANY_ADDRESS", "Mohan Estate, NEW DELHI");
?>
