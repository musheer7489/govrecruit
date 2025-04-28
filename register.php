<?php
session_start();
include 'config.php';
$title_text = "Register Form";
include 'header.php';
// Include post data
$postData = include 'jobs.php';
$advertisement_number = $postData['advertisement_number'];
// Assuming you have a button that triggers this action
if (isset($_GET['advertisement_number'])) {
    $advertisement_number = $_GET['advertisement_number'];
} else {
    $advertisement_number = ''; // Default if no registration number is provided
}
// Sanitize the input - VERY IMPORTANT for security
$advertisement_number = mysqli_real_escape_string($conn, $advertisement_number);

// Fetch the advertisement_id based on the advertisement_number
$sql_advertisement = "SELECT id FROM advertisements WHERE advertisement_number = '$advertisement_number'";
$result_advertisement = mysqli_query($conn, $sql_advertisement);
if (mysqli_num_rows($result_advertisement) > 0) {
    $row_advertisement = mysqli_fetch_assoc($result_advertisement);
    $advertisement_id = $row_advertisement['id'];

    // Fetch posts based on the retrieved advertisement_id
    $sql_posts = "SELECT * FROM posts WHERE advertisement_id = " . intval($advertisement_id); // Use intval
    $result_posts = mysqli_query($conn, $sql_posts);
} else {
    echo "<p>Advertisement Number: " . htmlspecialchars($advertisement_number) . " not found.</p>";
}

mysqli_close($conn);
$posts = $postData['posts'];
?>

<!-- Full-Screen Loader -->
<div id="loadingOverlay">
    <div id="loadingSpinner"></div>
</div>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fa-solid fa-link"></i> Important Links</h5>
                </div>
                <div class="card-body">
                    <div class="link-item">
                        <a href="login"><i class="fa-solid fa-user-minus"></i> Already Registered User Login</a>
                    </div>
                    <div class="link-item">
                        <a href="important_links/how_to_apply"><i class="fas fa-file-alt me-2"></i>How to Apply</a>
                    </div>
                    <div class="link-item">
                        <a href="important_links/help"><i class="fas fa-phone-alt me-2"></i>Contact Helpdesk</a>
                    </div>
                    <div class="link-item">
                        <a href="important_links/FAQs"><i class="fas fa-question-circle me-2"></i>FAQs</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header text-center">
                    <h3><i class="fa-regular fa-user"></i> Register</h3>
                </div>
                <div class="card-body">
                    <p class="mt-3 text-center" id="message"></p>
                    <form id="registerForm">
                        <div class="form-group mb-3">
                            <label for="advertisement_number" class="form-label"><b>Advertisement Number:</b></label>
                            <input type="text" class="form-control" id="advertisement_number" name="advertisement_number" value="<?php echo $advertisement_number; ?>" disabled>
                        </div>

                        <div class="form-group job-item">
                            <label class="form-label"><b>Select Posts(s):</b></label>
                            <?php if (mysqli_num_rows($result_posts) > 0) :
                                while ($row_posts = mysqli_fetch_assoc($result_posts)) : ?>
                                    <div>
                                        <input type="checkbox" id="post_<?php echo $row_posts['id']; ?>" name="posts[]" value="<?php echo $row_posts['id']; ?>" onchange="toggleQualifications(<?php echo $row_posts['id']; ?>, '<?php echo $row_posts['post_name']; ?>')">
                                        <label for="post_<?php echo $row_posts['id']; ?>" style="display: inline;">
                                            <?php echo $row_posts['post_name']; ?>
                                        </label>
                                        <input type="hidden" id="post_title_<?php echo $row_posts['id']; ?>" name="post_title_<?php echo $row_posts['id']; ?>">
                                    </div>

                                    <div id="qualifications_<?php echo $row_posts['id']; ?>" style="display: none;" class="post-section qualifications">
                                        <p><strong>Required Qualifications: </strong> <?php echo $row_posts['eligibility']; ?></p>
                                        <input type="text" id="qualifications_<?php echo $row_posts['id']; ?>" class="form-control" name="qualifications_<?php echo $row_posts['id']; ?>" value="<?php echo $row_posts['eligibility']; ?>" required hidden></input>
                                    </div>
                            <?php
                                endwhile;
                            endif; ?>
                        </div>
                        <div class="form-group row mb-3">
                            <label class="col-sm-3 col-form-label"><b>Full Name</b></label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="name" required>
                            </div>
                        </div>
                        <div class="form-group row mb-3">
                            <label class="col-sm-3 col-form-label"><b>Email</b></label>
                            <div class="col-sm-9">
                                <input type="email" class="form-control" name="email" required>
                            </div>
                        </div>
                        <div class="form-group row mb-3">
                            <label class="col-sm-3 col-form-label"><b>Mobile Number</b></label>
                            <div class="col-sm-9">
                                <input type="number" class="form-control" name="mobile" required>
                            </div>
                        </div>
                        <div class="form-group row mb-3">
                            <label class="col-sm-3 col-form-label"><b>Password</b></label>
                            <div class="col-sm-9">
                                <input type="password" class="form-control" name="password" required>
                            </div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Register</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="sweet_alert.js"></script>
<script>
    $(document).ready(function() {
        $("#registerForm").submit(function(e) {
            e.preventDefault();
            $("#loadingOverlay").fadeIn();
            // Create a plain object instead of FormData
            let formData = {
                advertisement_number: $('[name="advertisement_number"]').val(),
                name: $('[name="name"]').val(),
                email: $('[name="email"]').val(),
                mobile: $('[name="mobile"]').val(),
                password: $('[name="password"]').val(),
                posts: []
            };

            // Collect selected posts and their qualifications
            $('[name="posts[]"]:checked').each(function() {
                let postId = $(this).val();
                formData.posts.push({
                    id: postId,
                    title: $('[name="post_title_' + postId + '"]').val(),
                    qualifications: $('[name="qualifications_' + postId + '"]').val()
                });
            });

            // AJAX request
            $.ajax({
                type: "POST",
                url: "register_process.php",
                contentType: 'application/json',
                data: JSON.stringify(formData),
                dataType: 'json',
                success: function(response) {
                    $("#loadingOverlay").fadeOut();
                    if (response.status === "success") {
                        Swal.fire({
                            title: "OTP Sent",
                            icon: "success",
                            confirmButtonColor: "#3085d6",
                            confirmButtonText: "ok!"
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = "verify_otp?email=" + response.email + "&advertisement_number=" + response.advertisement_number;
                            }
                        });
                    } else {
                        $("#message").html("<div class='alert alert-danger'>" + response.message + "</div>");
                    }
                },
                error: function(xhr, status, error) {
                    $("#loadingOverlay").fadeOut(); // Hide loader in case of error
                    $("#message").html("<div class='alert alert-danger'>Something went wrong! " + error + "</div>");
                }
            });
        });
    });
</script>
<script>
    function toggleQualifications(postId, postTitle) {
        const checkbox = document.getElementById('post_' + postId);
        const qualDiv = document.getElementById('qualifications_' + postId);

        if (checkbox.checked) {
            qualDiv.style.display = 'block';
            // Set the hidden post title field
            document.getElementById('post_title_' + postId).value = postTitle;
        } else {
            qualDiv.style.display = 'none';
        }
    }
</script>
<?php include 'footer.php'; ?>