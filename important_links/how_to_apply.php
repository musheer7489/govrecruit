<?php
include '../config.php';
$title = 'How to Apply';
include 'header.php'; ?>

<main>
    <div class="page-header">
        <h1>How to Apply for Government Positions</h1>
        <p>Follow these simple steps to complete your application for government employment</p>
    </div>

    <div class="application-steps">
        <div class="step">
            <span class="step-number">1</span>
            <h3>Create an Account</h3>
            <p>Register on our portal with your valid email address and personal information to create your applicant profile.</p>
        </div>

        <div class="step">
            <span class="step-number">2</span>
            <h3>Search for Jobs</h3>
            <p>Browse available positions by department, location, or job category. Save jobs you're interested in.</p>
        </div>

        <div class="step">
            <span class="step-number">3</span>
            <h3>Complete Application</h3>
            <p>Fill out the online application form for your selected position, attaching all required documents.</p>
        </div>

        <div class="step">
            <span class="step-number">4</span>
            <h3>Review & Submit</h3>
            <p>Carefully review your application for accuracy before final submission. You'll receive a confirmation email.</p>
        </div>

        <div class="step">
            <span class="step-number">5</span>
            <h3>Application Tracking</h3>
            <p>Track your application status through your dashboard. You may be contacted for additional information.</p>
        </div>

        <div class="step">
            <span class="step-number">6</span>
            <h3>Selection Process</h3>
            <p>If selected, you'll be notified about next steps which may include tests, interviews, or background checks.</p>
        </div>
    </div>

    <div class="requirements">
        <h2>General Application Requirements</h2>
        <ul>
            <li>Valid government-issued identification</li>
            <li>Educational certificates and transcripts</li>
            <li>Professional certifications (if applicable)</li>
            <li>Updated resume/CV</li>
            <li>Cover letter (for some positions)</li>
            <li>References (typically 2-3 professional references)</li>
            <li>Any other position-specific documents</li>
        </ul>
    </div>

    <div class="important-notes">
        <h2>Important Notes for Applicants</h2>
        <ul>
            <li>Applications must be submitted before the posted deadline</li>
            <li>Incomplete applications will not be processed</li>
            <li>All information provided must be accurate and verifiable</li>
            <li>False information may lead to disqualification</li>
            <li>Keep your contact information updated in your profile</li>
            <li>Check your email regularly for communication about your application</li>
        </ul>
    </div>

    <div class="cta-button">
        <a href="../current_openings.php" class="btn btn-warning">Begin Your Application Now</a>
    </div>
</main>
<?php include 'footer.php'; ?>