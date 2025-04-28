<?php
include '../config.php';
$title = 'Contact Helpdesk';
include 'header.php'; ?>

    <main>
        <div class="page-header">
            <h1>Contact Helpdesk</h1>
            <p>Get in touch with our support team for assistance with your application or any queries</p>
        </div>

        <div class="faq-prompt">
            <h3>Have a question? Check our FAQs first!</h3>
            <p>Many common questions are already answered in our Frequently Asked Questions section.</p>
            <a href="FAQs" class="faq-btn">Visit FAQ Page</a>
        </div>

        <div class="contact-container">
            <div class="contact-info">
                <div class="contact-section">
                    <h2>Contact Information</h2>
                    <p>Our helpdesk is ready to assist you with any questions regarding the recruitment process.</p>

                    <div class="contact-details">
                        <div class="contact-item">
                            <div class="contact-icon">📞</div>
                            <div class="contact-text">
                                <h3>Phone Support</h3>
                                <p>Primary: <a href="tel:<?=COMPANY_MOBILE?>"><?=COMPANY_MOBILE?></a></p>
                                <p>Alternate: <a href="tel:+<?=COMPANY_MOBILE_ALTERNATE?>"><?=COMPANY_MOBILE_ALTERNATE?></a></p>
                                <p>Toll-free within the country</p>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-icon">✉️</div>
                            <div class="contact-text">
                                <h3>Email Support</h3>
                                <p>General Inquiries: <a href="mailto:<?=COMPANY_EMAIL?>"><?=COMPANY_EMAIL?></a></p>
                                <p>Technical Support: <a href="mailto:<?=COMPANY_RECRUITMENT_EMAIL?>"><?=COMPANY_RECRUITMENT_EMAIL?></a></p>
                                <p>Response time: 1-2 business days</p>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="contact-icon">🏢</div>
                            <div class="contact-text">
                                <h3>Physical Address</h3>
                                <p>Government Recruitment Office</p>
                                <p><?=COMPANY_ADDRESS?></p>
                                <p><a href="#" target="_blank">View on map</a></p>
                            </div>
                        </div>
                    </div>

                    <div class="working-hours">
                        <h3>Working Hours</h3>
                        <table>
                            <tr>
                                <th>Day</th>
                                <th>Hours</th>
                            </tr>
                            <tr>
                                <td>Monday - Friday</td>
                                <td>8:00 AM - 6:00 PM</td>
                            </tr>
                            <tr>
                                <td>Saturday</td>
                                <td>9:00 AM - 1:00 PM</td>
                            </tr>
                            <tr>
                                <td>Sunday</td>
                                <td>Closed</td>
                            </tr>
                            <tr>
                                <td>Public Holidays</td>
                                <td>Closed</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="contact-form">
                <div class="contact-section">
                    <h2>Send Us a Message</h2>
                    <p>Complete the form below and our team will get back to you as soon as possible.</p>

                    <form id="helpdeskForm">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="firstName">First Name *</label>
                                <input type="text" id="firstName" name="firstName" required>
                            </div>
                            <div class="form-group">
                                <label for="lastName">Last Name *</label>
                                <input type="text" id="lastName" name="lastName" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" required>
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone">
                        </div>

                        <div class="form-group">
                            <label for="subject">Subject *</label>
                            <select id="subject" name="subject" required>
                                <option value="" disabled selected>Select a topic</option>
                                <option value="application">Application Process</option>
                                <option value="technical">Technical Support</option>
                                <option value="eligibility">Eligibility Questions</option>
                                <option value="status">Application Status</option>
                                <option value="other">Other Inquiry</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="message">Your Message *</label>
                            <textarea id="message" name="message" required></textarea>
                        </div>

                        <div class="form-group">
                            <label for="applicationId">Application ID (if applicable)</label>
                            <input type="text" id="applicationId" name="applicationId">
                        </div>
                        <div id="formMessage" class="form-message" style="display:none; margin-bottom:1rem; padding:10px; border-radius:4px;"></div>
                        <button type="submit" class="submit-btn">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Simple form submission handling
        document.getElementById('helpdeskForm').addEventListener('submit', function(e) {
            e.preventDefault();

            // Get form data
            const formData = new FormData(this);
            const messageDiv = document.getElementById('formMessage');

            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Sending...';

            // AJAX request
            fetch('submit_contact.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        messageDiv.style.display = 'block';
                        messageDiv.style.backgroundColor = '#d4edda';
                        messageDiv.style.color = '#155724';
                        messageDiv.innerHTML = '<strong>Success!</strong> ' + data.message;

                        // Reset form
                        this.reset();
                    } else {
                        // Show error message
                        messageDiv.style.display = 'block';
                        messageDiv.style.backgroundColor = '#f8d7da';
                        messageDiv.style.color = '#721c24';
                        messageDiv.innerHTML = '<strong>Error:</strong> ' + data.message;
                    }
                })
                .catch(error => {
                    messageDiv.style.display = 'block';
                    messageDiv.style.backgroundColor = '#f8d7da';
                    messageDiv.style.color = '#721c24';
                    messageDiv.innerHTML = '<strong>Network Error:</strong> Please try again later.';
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Send Message';

                    // Hide message after 5 seconds
                    setTimeout(() => {
                        messageDiv.style.display = 'none';
                    }, 10000);
                });
        });
    </script>
<?php include 'footer.php'; ?>