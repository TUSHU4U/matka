<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' - ' : '' ?>Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .sidebar {
            min-height: 100vh;
            background-color: #212529;
            color: #fff;
        }
        .sidebar a {
            color: #adb5bd;
            text-decoration: none;
            padding: 12px 20px;
            display: block;
            border-left: 3px solid transparent;
            transition: all 0.3s ease;
        }
        .sidebar a:hover, .sidebar a.active {
            color: #fff;
            background-color: #343a40;
            border-left-color: #0d6efd;
        }
        .sidebar .brand {
            padding: 20px;
            font-size: 1.25rem;
            font-weight: bold;
            background-color: #1a1e21;
            text-align: center;
            border-bottom: 1px solid #343a40;
        }
        .main-content {
            flex-grow: 1;
            padding: 20px;
        }
        .top-navbar {
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,.04);
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .stat-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,.05);
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php require_once 'sidebar.php'; ?>
        
        <!-- Main Content Area -->
        <div class="main-content w-100">
            <!-- Top Navbar -->
            <div class="top-navbar">
                <h4 class="mb-0"><?= isset($page_title) ? htmlspecialchars($page_title) : 'Dashboard' ?></h4>
                <div>
                    <span class="me-3 fw-bold"><i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></span>
                    <a href="logout.php" class="btn btn-sm btn-outline-danger"><i class="bi bi-box-arrow-right"></i> Logout</a>
                </div>
            </div>
