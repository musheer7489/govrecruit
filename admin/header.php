<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <!-- Admin Header -->
    <header class="admin-header py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <h1 class="h4 mb-0">Government Recruitment Admin Dashboard</h1>
            <?php if (isset($_SESSION['admin_logged_in'])) : ?>
                <div>
                    <span class="me-3">Welcome, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></span>
                    <a href="logout" class="btn btn-sm btn-outline-light">Logout</a>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <!-- Admin Navigation -->
    <?php if (isset($_SESSION['admin_logged_in'])) : ?>
        <nav class="admin-nav navbar navbar-expand-lg mb-4">
            <div class="container">
                <div class="collapse navbar-collapse">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin_panel">User Management</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin_queries">User Queries</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin_job_posts">Job Post</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="notification">Post Notification</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    <?php endif; ?>