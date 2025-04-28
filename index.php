<?php
session_start();
// Database connection
require_once 'config.php';
$title_text = 'Home';
include 'header.php';

// Get all advertisements (both active and inactive)
$current_date = date('Y-m-d');
$query = "SELECT a.*, 
          (SELECT COUNT(*) FROM posts p WHERE p.advertisement_id = a.id) as post_count
          FROM advertisements a
          ORDER BY a.is_active DESC, a.application_start_date DESC";
$advertisements = $conn->query($query);

// Function to get posts for an advertisement
function getPosts($conn, $advertisement_id)
{
    $query = "SELECT * FROM posts WHERE advertisement_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $advertisement_id);
    $stmt->execute();
    return $stmt->get_result();
}
// Fetch all Notifications
$sql = "SELECT * FROM notifications ORDER BY created_at DESC";
$notifications = $conn->query($sql);
function getBadgeColor($type) {
    switch ($type) {
        case 'new': return 'primary';
        case 'reminder': return 'warning';
        case 'result': return 'success';
        case 'alert': return 'danger';
        default: return 'secondary';
    }
}
function getAlertBackground($type) {
    switch ($type) {
        case 'new': return 'danger';
        case 'reminder': return 'success';
        case 'result': return 'warning';
        case 'alert': return 'primary';
        default: return 'secondary';
    }
}
?>

<div class="container mb-5">
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0"><i class="fas fa-list-alt me-2"></i>All Advertisements</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="advertisementsTable" class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>S.No.</th>
                                    <th>Status</th>
                                    <th>Advt No</th>
                                    <th>Posts (Vacancies)</th>
                                    <th>Important Dates</th>
                                    <th>Download Advt</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $sno = 1; ?>
                                <?php while ($ad = $advertisements->fetch_assoc()) :
                                    $is_active = $ad['is_active'] == 1;
                                    $is_current = $is_active &&
                                        $ad['application_start_date'] <= $current_date &&
                                        $ad['application_end_date'] >= $current_date;
                                    $posts = getPosts($conn, $ad['id']);
                                ?>
                                    <tr>
                                        <td><?= $sno++ ?></td>
                                        <td>
                                            <?php if ($is_current) : ?>
                                                <span class="status-badge status-active">
                                                    <i class="fas fa-check-circle me-1"></i>Active
                                                </span>
                                            <?php elseif ($ad['application_end_date'] < $current_date) : ?>
                                                <span class="status-badge status-expired">
                                                    <i class="fas fa-times-circle me-1"></i>Expired
                                                </span>
                                            <?php else : ?>
                                                <span class="status-badge status-closed">
                                                    <i class="fas fa-lock me-1"></i>Closed
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($ad['advertisement_number']) ?></strong>
                                        </td>
                                        <td>
                                            <?php while ($post = $posts->fetch_assoc()) : ?>
                                                <div class="mb-2">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <strong><?= htmlspecialchars($post['post_name']) ?></strong>

                                                        </div>
                                                        <button class="btn btn-sm btn-outline-primary view-vacancy-btn" data-post-id="<?= $post['id'] ?>" data-post-name="<?= htmlspecialchars($post['post_name']) ?>" data-vacancies='<?= json_encode([
                                                                                                                                                                                                                                            'total' => $post['total_vacancies'],
                                                                                                                                                                                                                                            'general' => $post['vacancies_general'],
                                                                                                                                                                                                                                            'obc' => $post['vacancies_obc'],
                                                                                                                                                                                                                                            'sc' => $post['vacancies_sc'],
                                                                                                                                                                                                                                            'st' => $post['vacancies_st'],
                                                                                                                                                                                                                                            'ews' => $post['vacancies_ews']
                                                                                                                                                                                                                                        ]) ?>' data-eligibility="<?= htmlspecialchars($post['eligibility']) ?>" data-advertisement-number="<?= htmlspecialchars($ad['advertisement_number']) ?>" data-start-date="<?= date('d M, Y', strtotime($ad['application_start_date'])) ?>" data-end-date="<?= date('d M, Y', strtotime($ad['application_end_date'])) ?>" data-apply-link="<?= $is_current ? htmlspecialchars($ad['apply_link']) . "?advertisement_number=" . htmlspecialchars($ad['advertisement_number']) : '' ?>">
                                                            <i class="fas fa-eye me-1"></i>View
                                                        </button>
                                                    </div>
                                                </div>
                                                <hr>
                                            <?php endwhile; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-secondary view-dates-btn" data-start-date="<?= date('d M, Y', strtotime($ad['application_start_date'])) ?>" data-end-date="<?= date('d M, Y', strtotime($ad['application_end_date'])) ?>" data-payment-date="<?= date('d M, Y', strtotime($ad['last_date_payment'])) ?>">
                                                <i class="far fa-calendar-alt me-1"></i>View Dates
                                            </button>
                                        </td>
                                        <td>
                                            <?php if (!empty($ad['detail_link'])) : ?>
                                                <a href="<?= htmlspecialchars($ad['detail_link']) ?>" class="btn btn-sm btn-outline-secondary" target="_blank">
                                                    <i class="fas fa-download me-1"></i>Download
                                                </a>
                                            <?php else : ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($is_current && !empty($ad['apply_link'])) : ?>
                                                <a href="<?= htmlspecialchars($ad['apply_link']) ?>?advertisement_number=<?= htmlspecialchars($ad['advertisement_number']) ?>" class="btn btn-sm btn-danger" target="_blank">
                                                    <i class="fas fa-external-link-alt me-1"></i>Apply Now
                                                </a>
                                            <?php else : ?>
                                                <span class="status-badge status-closed">
                                                    Closed
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar with Important Links -->
        <div class="col-lg-3">
            <div class="important-links mb-4">
                <h5 class="mb-3"><i class="fas fa-link me-2"></i>Important Links</h5>
                <div class="link-item">
                    <a href="important_links/how_to_apply"><i class="fas fa-file-alt me-2"></i>How to Apply</a>
                </div>
                <div class="link-item">
                    <a href="important_links/FAQs"><i class="fas fa-question-circle me-2"></i>FAQs</a>
                </div>
                <div class="link-item">
                    <a href="important_links/recruitment_rules"><i class="fas fa-book me-2"></i>Recruitment Rules</a>
                </div>
                <div class="link-item">
                    <a href="<?= COMPANY_WEBSITE ?>"><i class="fas fa-university me-2"></i>Organization Website</a>
                </div>
                <div class="link-item">
                    <a href="important_links/help"><i class="fas fa-phone-alt me-2"></i>Contact Helpdesk</a>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-bell me-2"></i>Latest Notifications</h5>
                </div>
                <div class="card-body">
                    <?php while ($notification = $notifications->fetch_assoc()) : ?>
                        <div class="alert alert-<?= getAlertBackground($notification['type']) ?> p-2 mb-2">
                            <small><strong class="badge bg-<?= getBadgeColor($notification['type']) ?>"><?= ucfirst($notification['type'])?>:</strong> <a class="text-dark stretched-link" href="<?= $notification['link']?>"><?= $notification['title']?></a></small>
                        </div>
                        
                    <?php endwhile ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Vacancy Details Modal (Single Modal for all) -->
<div class="modal fade" id="vacancyModal" tabindex="-1" aria-labelledby="vacancyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="vacancyModalLabel">Vacancy Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="vacancy-details mb-4">
                    <h5 class="post-name mb-3"></h5>
                    <h6>Vacancy Distribution</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <p class="badge bg-primary vacancy-badge"><strong>Total Vacancies:</strong> <span class="vacancy-total">0</span></p>
                        </div>
                        <div class="col-md-4">
                            <p class="badge bg-secondary vacancy-badge"><strong>General:</strong> <span class="vacancy-general">0</span></p>
                        </div>
                        <div class="col-md-4">
                            <p class="badge bg-success vacancy-badge"><strong>OBC:</strong> <span class="vacancy-obc">0</span></p>
                        </div>
                        <div class="col-md-4">
                            <p class="badge bg-info vacancy-badge"><strong>SC:</strong> <span class="vacancy-sc">0</span></p>
                        </div>
                        <div class="col-md-4">
                            <p class="badge bg-warning vacancy-badge"><strong>ST:</strong> <span class="vacancy-st">0</span></p>
                        </div>
                        <div class="col-md-4">
                            <p class="badge bg-danger vacancy-badge"><strong>EWS:</strong> <span class="vacancy-ews">0</span></p>
                        </div>
                    </div>
                </div>

                <div class="eligibility-section mb-4">
                    <h6>Eligibility Criteria</h6>
                    <div class="qualification-list bg-light p-3 rounded"></div>
                </div>

                <div class="advertisement-info">
                    <h6>Advertisement Information</h6>
                    <p><strong>Advertisement Number:</strong> <span class="advertisement-number">N/A</span></p>
                    <p><strong>Application Period:</strong> <span class="application-period">N/A</span></p>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" class="btn btn-primary apply-now-btn" target="_blank" style="display: none;">
                    <i class="fas fa-external-link-alt me-1"></i>Apply Now
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Important Dates Modal (Single Modal for all) -->
<div class="modal fade" id="datesModal" tabindex="-1" aria-labelledby="datesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="datesModalLabel">Important Dates</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <strong>Application Start Date</strong>
                        <span class="start-date">N/A</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <strong>Application End Date</strong>
                        <span class="end-date">N/A</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <strong>Last Date for Payment</strong>
                        <span class="payment-date">N/A</span>
                    </li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#advertisementsTable').DataTable({
            responsive: true,
            order: [
                [1, 'desc']
            ], // Sort by status
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search advertisements...",
            },
            dom: '<"top"f>rt<"bottom"lip><"clear">',
            columnDefs: [{
                    responsivePriority: 1,
                    targets: 0
                },
                {
                    responsivePriority: 2,
                    targets: 1
                },
                {
                    responsivePriority: 3,
                    targets: 2
                },
                {
                    responsivePriority: 4,
                    targets: -1
                }
            ]
        });

        // Vacancy Modal Handler
        $(document).on('click', '.view-vacancy-btn', function() {
            const postName = $(this).data('post-name');
            const vacancies = $(this).data('vacancies');
            const eligibility = $(this).data('eligibility');
            const advNumber = $(this).data('advertisement-number');
            const startDate = $(this).data('start-date');
            const endDate = $(this).data('end-date');
            const applyLink = $(this).data('apply-link');

            // Update modal content
            $('#vacancyModal .post-name').text(postName);
            $('#vacancyModal .vacancy-total').text(vacancies.total);
            $('#vacancyModal .vacancy-general').text(vacancies.general);
            $('#vacancyModal .vacancy-obc').text(vacancies.obc);
            $('#vacancyModal .vacancy-sc').text(vacancies.sc);
            $('#vacancyModal .vacancy-st').text(vacancies.st);
            $('#vacancyModal .vacancy-ews').text(vacancies.ews);
            $('#vacancyModal .qualification-list').html(eligibility ? eligibility.replace(/\n/g, '<br>') : 'No eligibility criteria specified.');
            $('#vacancyModal .advertisement-number').text(advNumber);
            $('#vacancyModal .application-period').text(startDate + ' to ' + endDate);

            // Update apply button
            const applyBtn = $('#vacancyModal .apply-now-btn');
            if (applyLink) {
                applyBtn.attr('href', applyLink).show();
            } else {
                applyBtn.hide();
            }

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('vacancyModal'));
            modal.show();
        });

        // Dates Modal Handler
        $(document).on('click', '.view-dates-btn', function() {
            const startDate = $(this).data('start-date');
            const endDate = $(this).data('end-date');
            const paymentDate = $(this).data('payment-date');

            // Update modal content
            $('#datesModal .start-date').text(startDate);
            $('#datesModal .end-date').text(endDate);
            $('#datesModal .payment-date').text(paymentDate);

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('datesModal'));
            modal.show();
        });

        // Modal stability fixes
        $('.modal').on('show.bs.modal', function() {
            // Disable body scrolling
            $('body').addClass('modal-open');
        });

        $('.modal').on('hidden.bs.modal', function() {
            // Re-enable body scrolling
            $('body').removeClass('modal-open');
        });
    });
</script>
<?php
$conn->close();
include 'footer.php';
?>