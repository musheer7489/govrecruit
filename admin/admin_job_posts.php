<?php
session_start();
// Authentication check
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index');
    exit;
}

// Database connection
$db = new mysqli('localhost', 'root', '', 'otp_register');
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_advertisement') {
        // Create new advertisement
        $advertisement_number = $db->real_escape_string($_POST['advertisement_number']);
        $application_start_date = $db->real_escape_string($_POST['application_start_date']);
        $application_end_date = $db->real_escape_string($_POST['application_end_date']);
        $last_date_payment = $db->real_escape_string($_POST['last_date_payment']);
        $detail_link = $db->real_escape_string($_POST['detail_link']);
        $apply_link = $db->real_escape_string($_POST['apply_link']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        $query = "INSERT INTO advertisements (advertisement_number, application_start_date, 
                  application_end_date, last_date_payment, detail_link, apply_link, is_active)
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->bind_param(
            'ssssssi',
            $advertisement_number,
            $application_start_date,
            $application_end_date,
            $last_date_payment,
            $detail_link,
            $apply_link,
            $is_active
        );

        if ($stmt->execute()) {
            $advertisement_id = $stmt->insert_id;
            $success_message = "Advertisement created successfully!";

            // Now handle posts
            if (isset($_POST['post_name']) && is_array($_POST['post_name'])) {
                foreach ($_POST['post_name'] as $index => $post_name) {
                    $post_name = $db->real_escape_string($post_name);
                    $eligibility = $db->real_escape_string($_POST['eligibility'][$index] ?? '');
                    $total_vacancies = intval($_POST['total_vacancies'][$index] ?? 0);
                    $vacancies_general = intval($_POST['vacancies_general'][$index] ?? 0);
                    $vacancies_obc = intval($_POST['vacancies_obc'][$index] ?? 0);
                    $vacancies_sc = intval($_POST['vacancies_sc'][$index] ?? 0);
                    $vacancies_st = intval($_POST['vacancies_st'][$index] ?? 0);
                    $vacancies_ews = intval($_POST['vacancies_ews'][$index] ?? 0);

                    $query = "INSERT INTO posts (advertisement_id, post_name, eligibility, 
                              total_vacancies, vacancies_general, vacancies_obc, 
                              vacancies_sc, vacancies_st, vacancies_ews)
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $db->prepare($query);
                    $stmt->bind_param(
                        'issiiiiii',
                        $advertisement_id,
                        $post_name,
                        $eligibility,
                        $total_vacancies,
                        $vacancies_general,
                        $vacancies_obc,
                        $vacancies_sc,
                        $vacancies_st,
                        $vacancies_ews
                    );
                    $stmt->execute();
                }
            }
        } else {
            $error_message = "Error: " . $stmt->error;
        }
    } elseif ($action === 'update_advertisement') {
        // Update advertisement
        $id = $_POST['id'] ?? 0;
        $advertisement_number = $db->real_escape_string($_POST['advertisement_number']);
        $application_start_date = $db->real_escape_string($_POST['application_start_date']);
        $application_end_date = $db->real_escape_string($_POST['application_end_date']);
        $last_date_payment = $db->real_escape_string($_POST['last_date_payment']);
        $detail_link = $db->real_escape_string($_POST['detail_link']);
        $apply_link = $db->real_escape_string($_POST['apply_link']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        $query = "UPDATE advertisements SET 
                  advertisement_number = ?,
                  application_start_date = ?,
                  application_end_date = ?,
                  last_date_payment = ?,
                  detail_link = ?,
                  apply_link = ?,
                  is_active = ?
                  WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->bind_param(
            'ssssssii',
            $advertisement_number,
            $application_start_date,
            $application_end_date,
            $last_date_payment,
            $detail_link,
            $apply_link,
            $is_active,
            $id
        );

        if ($stmt->execute()) {
            $success_message = "Advertisement updated successfully!";

            // Delete existing posts
            $delete_query = "DELETE FROM posts WHERE advertisement_id = ?";
            $delete_stmt = $db->prepare($delete_query);
            $delete_stmt->bind_param('i', $id);
            $delete_stmt->execute();

            // Add updated posts
            if (isset($_POST['post_name']) && is_array($_POST['post_name'])) {
                foreach ($_POST['post_name'] as $index => $post_name) {
                    $post_name = $db->real_escape_string($post_name);
                    $eligibility = $db->real_escape_string($_POST['eligibility'][$index] ?? '');
                    $total_vacancies = intval($_POST['total_vacancies'][$index] ?? 0);
                    $vacancies_general = intval($_POST['vacancies_general'][$index] ?? 0);
                    $vacancies_obc = intval($_POST['vacancies_obc'][$index] ?? 0);
                    $vacancies_sc = intval($_POST['vacancies_sc'][$index] ?? 0);
                    $vacancies_st = intval($_POST['vacancies_st'][$index] ?? 0);
                    $vacancies_ews = intval($_POST['vacancies_ews'][$index] ?? 0);

                    $query = "INSERT INTO posts (advertisement_id, post_name, eligibility, 
                              total_vacancies, vacancies_general, vacancies_obc, 
                              vacancies_sc, vacancies_st, vacancies_ews)
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $db->prepare($query);
                    $stmt->bind_param(
                        'issiiiiii',
                        $id,
                        $post_name,
                        $eligibility,
                        $total_vacancies,
                        $vacancies_general,
                        $vacancies_obc,
                        $vacancies_sc,
                        $vacancies_st,
                        $vacancies_ews
                    );
                    $stmt->execute();
                }
            }
        } else {
            $error_message = "Error: " . $stmt->error;
        }
    } elseif ($action === 'delete_advertisement') {
        // Delete advertisement (posts will be deleted automatically due to CASCADE)
        $id = $_POST['id'] ?? 0;
        $query = "DELETE FROM advertisements WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            $success_message = "Advertisement and all associated posts deleted successfully!";
        } else {
            $error_message = "Error: " . $stmt->error;
        }
    }
}

// Fetch all advertisements with their posts
$query = "SELECT a.*, 
          (SELECT COUNT(*) FROM posts p WHERE p.advertisement_id = a.id) as post_count
          FROM advertisements a
          ORDER BY a.application_start_date DESC";
$advertisements = $db->query($query);

// Function to get posts for an advertisement
function getPosts($db, $advertisement_id)
{
    $query = "SELECT * FROM posts WHERE advertisement_id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param('i', $advertisement_id);
    $stmt->execute();
    return $stmt->get_result();
}
include 'header.php'
?>
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-12">
                <h2 class="mb-4"><i class="fas fa-briefcase"></i> Job Advertisement Management</h2>

                <?php if (isset($success_message)) : ?>
                    <div class="alert alert-success"><?= $success_message ?></div>
                <?php endif; ?>

                <?php if (isset($error_message)) : ?>
                    <div class="alert alert-danger"><?= $error_message ?></div>
                <?php endif; ?>

                <!-- Create/Edit Form -->
                <div class="card form-section">
                    <div class="card-header">
                        <h5><i class="fas fa-edit"></i> <?= isset($_GET['edit']) ? 'Edit' : 'Create' ?> Advertisement</h5>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <?php if (isset($_GET['edit'])) :
                                $edit_id = $_GET['edit'];
                                $edit_query = "SELECT * FROM advertisements WHERE id = ?";
                                $edit_stmt = $db->prepare($edit_query);
                                $edit_stmt->bind_param('i', $edit_id);
                                $edit_stmt->execute();
                                $advertisement = $edit_stmt->get_result()->fetch_assoc();
                                $posts = getPosts($db, $edit_id);
                            ?>
                                <input type="hidden" name="action" value="update_advertisement">
                                <input type="hidden" name="id" value="<?= $advertisement['id'] ?>">
                            <?php else : ?>
                                <input type="hidden" name="action" value="create_advertisement">
                            <?php endif; ?>

                            <h4 class="mb-3">Advertisement Details</h4>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Advertisement Number*</label>
                                    <input type="text" class="form-control" name="advertisement_number" value="<?= $advertisement['advertisement_number'] ?? '' ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Application Start Date*</label>
                                    <input type="date" class="form-control" name="application_start_date" value="<?= $advertisement['application_start_date'] ?? '' ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Application End Date*</label>
                                    <input type="date" class="form-control" name="application_end_date" value="<?= $advertisement['application_end_date'] ?? '' ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Last Date for Payment*</label>
                                    <input type="date" class="form-control" name="last_date_payment" value="<?= $advertisement['last_date_payment'] ?? '' ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Detail Page Link</label>
                                    <input type="text" class="form-control" name="detail_link" value="<?= $advertisement['detail_link'] ?? '' ?>" require>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Apply Link*</label>
                                    <input type="text" class="form-control" name="apply_link" value="<?= $advertisement['apply_link'] ?? '' ?>" required>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" <?= (isset($advertisement['is_active']) && $advertisement['is_active']) ? 'checked' : 'checked' ?>>
                                        <label class="form-check-label" for="is_active">
                                            Active (Visible to applicants)
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <h4 class="mt-4 mb-3">Posts</h4>
                            <div id="posts-container">
                                <?php if (isset($posts) && $posts->num_rows > 0) : ?>
                                    <?php while ($post = $posts->fetch_assoc()) : ?>
                                        <div class="post-row">
                                            <div class="row g-3">
                                                <div class="col-md-5">
                                                    <label class="form-label">Post Name*</label>
                                                    <input type="text" class="form-control" name="post_name[]" value="<?= htmlspecialchars($post['post_name']) ?>" required>
                                                </div>
                                                <div class="col-md-7">
                                                    <label class="form-label">Eligibility Criteria</label>
                                                    <textarea class="form-control" name="eligibility[]" rows="2"><?= htmlspecialchars($post['eligibility']) ?></textarea>
                                                </div>

                                                <div class="col-12">
                                                    <div class="vacancy-box">
                                                        <h6>Vacancy Details</h6>
                                                        <div class="row g-3">
                                                            <div class="col-md-2">
                                                                <label class="form-label">Total*</label>
                                                                <input type="number" class="form-control" name="total_vacancies[]" value="<?= $post['total_vacancies'] ?>" min="0" required>
                                                            </div>
                                                            <div class="col-md-2">
                                                                <label class="form-label">General</label>
                                                                <input type="number" class="form-control" name="vacancies_general[]" value="<?= $post['vacancies_general'] ?>" min="0">
                                                            </div>
                                                            <div class="col-md-2">
                                                                <label class="form-label">OBC</label>
                                                                <input type="number" class="form-control" name="vacancies_obc[]" value="<?= $post['vacancies_obc'] ?>" min="0">
                                                            </div>
                                                            <div class="col-md-2">
                                                                <label class="form-label">SC</label>
                                                                <input type="number" class="form-control" name="vacancies_sc[]" value="<?= $post['vacancies_sc'] ?>" min="0">
                                                            </div>
                                                            <div class="col-md-2">
                                                                <label class="form-label">ST</label>
                                                                <input type="number" class="form-control" name="vacancies_st[]" value="<?= $post['vacancies_st'] ?>" min="0">
                                                            </div>
                                                            <div class="col-md-2">
                                                                <label class="form-label">EWS</label>
                                                                <input type="number" class="form-control" name="vacancies_ews[]" value="<?= $post['vacancies_ews'] ?>" min="0">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-2">
                                                    <button type="button" class="btn btn-danger remove-post" style="margin-top: 32px;">
                                                        <i class="fas fa-trash"></i> Remove Post
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else : ?>
                                    <div class="post-row">
                                        <div class="row g-3">
                                            <div class="col-md-5">
                                                <label class="form-label">Post Name*</label>
                                                <input type="text" class="form-control" name="post_name[]" required>
                                            </div>
                                            <div class="col-md-7">
                                                <label class="form-label">Eligibility Criteria</label>
                                                <textarea class="form-control" name="eligibility[]" rows="2"></textarea>
                                            </div>

                                            <div class="col-12">
                                                <div class="vacancy-box">
                                                    <h6>Vacancy Details</h6>
                                                    <div class="row g-3">
                                                        <div class="col-md-2">
                                                            <label class="form-label">Total*</label>
                                                            <input type="number" class="form-control" name="total_vacancies[]" min="0" required>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-label">General</label>
                                                            <input type="number" class="form-control" name="vacancies_general[]" min="0">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-label">OBC</label>
                                                            <input type="number" class="form-control" name="vacancies_obc[]" min="0">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-label">SC</label>
                                                            <input type="number" class="form-control" name="vacancies_sc[]" min="0">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-label">ST</label>
                                                            <input type="number" class="form-control" name="vacancies_st[]" min="0">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-label">EWS</label>
                                                            <input type="number" class="form-control" name="vacancies_ews[]" min="0">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-danger remove-post" style="margin-top: 32px;">
                                                    <i class="fas fa-trash"></i> Remove Post
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="add-post-btn">
                                <button type="button" class="btn btn-secondary" id="add-post">
                                    <i class="fas fa-plus"></i> Add Another Post
                                </button>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> <?= isset($_GET['edit']) ? 'Update' : 'Create' ?> Advertisement
                                </button>
                                <?php if (isset($_GET['edit'])) : ?>
                                    <a href="admin_job_posts" class="btn btn-secondary">Cancel</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Advertisements List -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-list"></i> Current Advertisements</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="advertisementsTable" class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Adv. No.</th>
                                        <th>Posts (Vacancies)</th>
                                        <th>Dates</th>
                                        <th>Payment Deadline</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($ad = $advertisements->fetch_assoc()) :
                                        $posts = getPosts($db, $ad['id']);
                                    ?>
                                        <tr>
                                            <td><?= htmlspecialchars($ad['advertisement_number']) ?></td>
                                            <td>
                                                <?php while ($post = $posts->fetch_assoc()) : ?>
                                                    <div>
                                                        <strong><?= htmlspecialchars($post['post_name']) ?></strong>
                                                        (Total: <?= $post['total_vacancies'] ?> |
                                                        Gen: <?= $post['vacancies_general'] ?> |
                                                        OBC: <?= $post['vacancies_obc'] ?> |
                                                        SC: <?= $post['vacancies_sc'] ?> |
                                                        ST: <?= $post['vacancies_st'] ?> |
                                                        EWS: <?= $post['vacancies_ews'] ?>)
                                                    </div>
                                                <?php endwhile; ?>
                                            </td>
                                            <td>
                                                <?= date('M d, Y', strtotime($ad['application_start_date'])) ?> -
                                                <?= date('M d, Y', strtotime($ad['application_end_date'])) ?>
                                            </td>
                                            <td><?= date('M d, Y', strtotime($ad['last_date_payment'])) ?></td>
                                            <td>
                                                <?php if ($ad['is_active']) : ?>
                                                    <span class="status-active"><i class="fas fa-check-circle"></i> Active</span>
                                                <?php else : ?>
                                                    <span class="status-inactive"><i class="fas fa-times-circle"></i> Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="admin_job_posts?edit=<?= $ad['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <form method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this advertisement and all its posts?');">
                                                    <input type="hidden" name="action" value="delete_advertisement">
                                                    <input type="hidden" name="id" value="<?= $ad['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="fas fa-trash-alt"></i> Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#advertisementsTable').DataTable({
                responsive: true,
                order: [
                    [2, 'desc']
                ] // Sort by application start date by default
            });

            // Add post row
            $('#add-post').click(function() {
                $('#posts-container').append(`
                    <div class="post-row">
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label">Post Name*</label>
                                <input type="text" class="form-control" name="post_name[]" required>
                            </div>
                            <div class="col-md-7">
                                <label class="form-label">Eligibility Criteria</label>
                                <textarea class="form-control" name="eligibility[]" rows="2"></textarea>
                            </div>
                            
                            <div class="col-12">
                                <div class="vacancy-box">
                                    <h6>Vacancy Details</h6>
                                    <div class="row g-3">
                                        <div class="col-md-2">
                                            <label class="form-label">Total*</label>
                                            <input type="number" class="form-control" name="total_vacancies[]" min="0" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">General</label>
                                            <input type="number" class="form-control" name="vacancies_general[]" min="0">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">OBC</label>
                                            <input type="number" class="form-control" name="vacancies_obc[]" min="0">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">SC</label>
                                            <input type="number" class="form-control" name="vacancies_sc[]" min="0">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">ST</label>
                                            <input type="number" class="form-control" name="vacancies_st[]" min="0">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">EWS</label>
                                            <input type="number" class="form-control" name="vacancies_ews[]" min="0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-2">
                                <button type="button" class="btn btn-danger remove-post" style="margin-top: 32px;">
                                    <i class="fas fa-trash"></i> Remove Post
                                </button>
                            </div>
                        </div>
                    </div>
                `);
            });

            // Remove post row
            $(document).on('click', '.remove-post', function() {
                if ($('.post-row').length > 1) {
                    $(this).closest('.post-row').remove();
                } else {
                    alert('You must have at least one post.');
                }
            });

            // Scroll to form when editing
            <?php if (isset($_GET['edit'])) : ?>
                $('html, body').animate({
                    scrollTop: $('.form-section').offset().top - 20
                }, 500);
            <?php endif; ?>
        });
    </script>
</body>

</html>
<?php
$db->close();
?>