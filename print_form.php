<?php
session_start();
require_once 'vendor/autoload.php';
include 'config.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Access Denied");
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$stmt = $conn->prepare("SELECT job_data, name, email, mobile, personal_info, address, education, experience, photo_signature FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
// Fetch Payment completion status
$stmt = $conn->prepare("SELECT * FROM payments WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();

// Decode JSON fields
$job_data = json_decode($user['job_data'], true);
$personal_info = json_decode($user['personal_info'], true);
$address = json_decode($user['address'], true);
$education = json_decode($user['education'], true);
$experience = json_decode($user['experience'], true);
$photo_signature = json_decode($user['photo_signature'], true);

// Create a new PDF document
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('Your Website Name');
$pdf->SetTitle('User Form');

// Set margins
$pdf->SetMargins(10, 15, 10);
$pdf->SetAutoPageBreak(TRUE, 15);
$pdf->AddPage();

// Border for entire page
$pdf->SetLineWidth(1);
$pdf->Rect(5, 5, 200, 287);

// Set font
$pdf->SetFont('helvetica', '', 12);

// HTML content with Bootstrap-like table styling
$html = '
<style>
    table {
        border-collapse: collapse;
        width: 100%;
        font-size: 14px;
    }
    th, td {
        border: 1px solid #000;
        padding: 10px;
        text-align: left;
    }
    th {
        background-color: #f8f9fa;
        font-weight: bold;
    }
    h2 {
        text-align: center;
        font-size: 18px;
        margin-bottom: 10px;
    }
    .photo-signature {
        text-align: center;
        margin-top: 20px;
    }
    .header-img {
        text-align: center;
        margin-top: 20px;
    }
</style>
<div align="center" class="header-img">
        <img src="assets/web-logo.png" alt="header" width="480">
    </div>
<h2>Application Form Print</h2>

<h4>Applied Posts</h4>
<table>
    <tr><th>Advt No</th><td>' . htmlspecialchars($job_data['advertisement_number'] ?? '') . '</td></tr>';
    foreach ($job_data['applications'] as $job) {
        $html .= '<tr><th>Post Name</th><td>' . htmlspecialchars($job['post_title'] ?? 'NA') . '</td></tr>';
    }
$html .= '</table>
<h4>Personal Information</h4>
<table>
   <tr><th>Full Name</th><td>'. htmlspecialchars($user['name']).'</td></tr>
    <tr><th>Email</th><td>' . htmlspecialchars($user['email']).'</td></tr>
    <tr><th>Mobile</th><td>' . htmlspecialchars($user['mobile']) . '</td></tr>
    <tr><th>Category</th><td>' . htmlspecialchars($personal_info['category'] ?? '') . '</td></tr>
    <tr><th>Gender</th><td> ' . htmlspecialchars($personal_info['gender'] ?? '') . '</td></tr>
    <tr><th>Date of Birth</th><td>' .htmlspecialchars($personal_info['dob'] ?? '') . '</td></tr>
    <tr><th>Disability</th><td>' .htmlspecialchars($personal_info['disability'] ?? 'No'). '</td></tr>
    <tr><th>Marrital Status</th><td>' .htmlspecialchars($personal_info['marital'] ?? 'No'). '</td></tr>
    <tr><th>Ex-Serviceman</th><td>' .htmlspecialchars($personal_info['exman'] ?? 'No'). '</td></tr>
    <tr><th>Nationality</th><td>' .htmlspecialchars($personal_info['nationality'] ?? 'No'). '</td></tr>
</table>

<h4>Complete Address</h4>
<table>
    <tr><th>Full Address</th><td>' . htmlspecialchars($address['full_address'] ?? '') . '</td></tr>
    <tr><th>State</th><td>' . htmlspecialchars($address['state'] ?? '') . '</td></tr>
    <tr><th>City</th><td>' . htmlspecialchars($address['city'] ?? '') . '</td></tr>
    <tr><th>PIN Code</th><td>' . htmlspecialchars($address['pincode'] ?? '') . '</td></tr>
</table>

<h4>Educational Information</h4>
<table>
    <tr>
        <th>Level</th>
        <th>Year</th>
        <th>Board/University</th>
        <th>Percentage</th>
    </tr>
    <tr>
        <td>Matric (10th)</td>
        <td>' . htmlspecialchars($education['matriculation']['passing_year'] ?? '') . '</td>
        <td>' . htmlspecialchars($education['matriculation']['board'] ?? '') . '</td>
        <td>' . htmlspecialchars($education['matriculation']['percentage'] ?? '') . '%</td>
    </tr>
    <tr>
        <td>Intermediate (12th)</td>
        <td>' . htmlspecialchars($education['intermediate']['passing_year'] ?? '') . '</td>
        <td>' . htmlspecialchars($education['intermediate']['board'] ?? '') . '</td>
        <td>' . htmlspecialchars($education['intermediate']['percentage'] ?? '') . '%</td>
    </tr>
    <tr>
        <td>Graduation</td>
        <td>' . htmlspecialchars($education['graduation']['passing_year'] ?? 'NA') . '</td>
        <td>' . htmlspecialchars($education['graduation']['college'] ?? 'NA') . '</td>
        <td>' . htmlspecialchars($education['graduation']['percentage'] ?? '0') . '%</td>
    </tr>
</table>

<h4>Work Experience</h4>
<table>
    <tr>
        <th>Company Name</th>
        <th>Role</th>
        <th>Years</th>
    </tr>';
    foreach ($experience as $exp) {
        $html .= '<tr>
            <td>' . htmlspecialchars($exp['company'] ?? 'NA') . '</td>
            <td>' . htmlspecialchars($exp['job_title'] ?? 'NA') . '</td>
            <td>' . htmlspecialchars($exp['experience_years'] ?? '0') . ' years</td>
        </tr>';
    }
$html .= '</table>
<h4 >Payment Status</h4>
        <table >
            <tr><th>Status</th><th>Amount</th><th>Payment ID</th></tr>';
            if(!empty($payment)) {
                $html .= '<tr>
                    <td>'. htmlspecialchars($payment['payment_status'] ?? '') . '</td>
                    <td>' . htmlspecialchars($payment['amount'] ?? '') . '</td>
                    <td>' . htmlspecialchars($payment['transaction_id'] ?? '') . '</td>
                </tr>';
            } else{ 
                $html .= '<td>Not Available</td>
                <td>NA</td>
                <td>NA</td>';
                 }
        $html .= '</table>

<h4 class="photo-signature">Photo & Signature</h4>
<table>
    <tr>
        <td align="center"><img src="uploads/' . htmlspecialchars($photo_signature['photo'] ?? 'default_photo.png') . '" width="120"></td>
        <td align="center"><img src="uploads/' . htmlspecialchars($photo_signature['signature'] ?? 'default_signature.png') . '" width="120"></td>
    </tr>
    <tr>
        <th align="center">Photo</th>
        <th align="center">Signature</th>
    </tr>
</table>';

$pdf->writeHTML($html, true, false, true, false, '');

// Output PDF
$pdf->Output('User_Form.pdf', 'I');
?>
