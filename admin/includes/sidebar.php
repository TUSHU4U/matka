<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar d-flex flex-column" style="width: 250px;">
    <div class="brand">
        <i class="bi bi-speedometer2 text-primary"></i> Fatafat Admin
    </div>
    <div class="mt-3">
        <a href="index.php" class="<?= $current_page === 'index.php' ? 'active' : '' ?>">
            <i class="bi bi-grid-1x2-fill me-2"></i> Dashboard
        </a>
        <a href="results.php" class="<?= $current_page === 'results.php' ? 'active' : '' ?>">
            <i class="bi bi-calendar-check-fill me-2"></i> Manage Results
        </a>
        <a href="bulk_upload.php" class="<?= $current_page === 'bulk_upload.php' ? 'active' : '' ?>">
            <i class="bi bi-file-earmark-spreadsheet-fill me-2"></i> Bulk Upload
        </a>
        <a href="settings.php" class="<?= $current_page === 'settings.php' ? 'active' : '' ?>">
            <i class="bi bi-gear-fill me-2"></i> Settings
        </a>
    </div>
</div>
