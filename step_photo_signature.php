<?php
session_start();
include 'config.php';
$title_text = "Photo and Signature";
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$user = $conn->query("SELECT photo_signature, is_final_submitted FROM users WHERE id = $user_id")->fetch_assoc();
$photo_signature = json_decode($user['photo_signature'], true);

// Default placeholders
$photo = isset($photo_signature['photo']) ? $photo_signature['photo'] : 'default_photo.jpg';
$signature = isset($photo_signature['signature']) ? $photo_signature['signature'] : 'default_signature.png';

if ($user['is_final_submitted']) {
    echo "<div class='container mt-4'><div class='alert alert-danger'>You have already submitted your profile. No further changes allowed.</div></div>";
    include 'footer.php';
    exit();
}
?>

<div class="container mt-4">
    <h3>Step 5: Upload Photo & Signature</h3>
    <form id="uploadForm" enctype="multipart/form-data">
        <div class="row">
            <!-- Photo Upload -->
            <div class="col-md-6 text-center">
                <label class="form-label"><b>Upload Your Photo</b></label>
                <div class="border p-3">
                    <img id="photoPreview" src="uploads/<?php echo $photo; ?>" class="img-thumbnail" width="150">
                    <input type="file" id="photo" name="photo" class="form-control mt-2" accept=".jpg, .jpeg, .png" required>
                </div>
            </div>

            <!-- Signature Upload -->
            <div class="col-md-6 text-center">
                <label class="form-label"><b>Upload Your Signature</b></label>
                <div class="border p-3">
                    <img id="signaturePreview" src="uploads/<?php echo $signature; ?>" class="img-thumbnail" width="200">
                    <input type="file" id="signature" name="signature" class="form-control mt-2" accept=".jpg, .jpeg, .png" required>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-success w-100 mt-3">Upload & Continue</button>
    </form>
</div>
<script src="sweet_alert.js"></script>
<script>
    // Preview Image Before Upload
    document.getElementById("photo").addEventListener("change", function(event) {
        var file = event.target.files[0];
        if (file) {
            var fileSize = file.size; // in bytes
            var maxSize = 50 * 1024; // 50 KB in bytes

            if (fileSize > maxSize) {
                Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: "Photo size exceeds 50KB. Please Select Smaller Size!",
                });
                // Optionally clear the file input:
                $('#photo').val('');
            }
        }
        let reader = new FileReader();
        reader.onload = function() {
            document.getElementById("photoPreview").src = reader.result;
        }
        reader.readAsDataURL(event.target.files[0]);
    });

    document.getElementById("signature").addEventListener("change", function(event) {
        var file = event.target.files[0];
        if (file) {
            var fileSize = file.size; // in bytes
            var maxSize = 20 * 1024; // 50 KB in bytes

            if (fileSize > maxSize) {
                Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: "Signature size exceeds 20KB. Please Select Smaller Size!",
                });
                // Optionally clear the file input:
                $('#signature').val('');
            }
        }
        let reader = new FileReader();
        reader.onload = function() {
            document.getElementById("signaturePreview").src = reader.result;
        }
        reader.readAsDataURL(event.target.files[0]);
    });

    // AJAX File Upload
    document.getElementById("uploadForm").addEventListener("submit", function(event) {
        event.preventDefault();
        Swal.fire({
            title: 'Are you Sure?',
            text: "Save your Address!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, submit it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // If user clicks "Yes, submit it!", submit the form

                let formData = new FormData(this);

                fetch('upload_photo_signature.php', {
                    method: "POST",
                    body: formData
                }).then(response => response.json()).then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: "Alert!",
                            text: "Upload Succcessful",
                            icon: "success",
                            confirmButtonColor: "#3085d6",
                            confirmButtonText: "ok!"
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = "dashboard";
                            }
                        });
                    } else {
                        Swal.fire({
                            title: "Oops!",
                            text: data.error,
                            icon: "error",
                            confirmButtonColor: "#3085d6",
                            confirmButtonText: "ok!"
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = "dashboard";
                            }
                        });
                        alert(data.error);
                    }
                }).catch(error => console.log(error));
                //$(this).unbind('submit').submit(); // Unbind the current submit event, then resubmit.
            }
        });

    });
</script>

<?php include 'footer.php'; ?>