    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>Recruitment Portal</h5>
                    <p>A centralized platform for all recruitment activities, providing transparent and efficient application processes.</p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="./" class="text-white">Home</a></li>
                        <li><a href="current_openings" class="text-white">Current Openings</a></li>
                        <li><a href="results" class="text-white">Results</a></li>
                        <li><a href="important_links/help" class="text-white">Contact Us</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact Information</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-envelope me-2"></i> <?=COMPANY_EMAIL?></li>
                        <li><i class="fas fa-phone me-2"></i> <?=COMPANY_MOBILE?></li>
                        <li><i class="fas fa-map-marker-alt me-2"></i> <?=COMPANY_ADDRESS?></li>
                    </ul>
                </div>
            </div>
            <hr class="bg-light">
            <div class="text-center">
                <p class="mb-0">&copy; <?= date('Y') ?> <?=COMPANY_NAME?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="assets/script.js"></script>
    </body>

    </html>