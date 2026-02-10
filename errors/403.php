<?php
http_response_code(403);
$pageTitle = "Access Denied";
require_once '../config/config.php';
?>

<?php include '../includes/header.php'; ?>

<div class="text-center py-5">
    <i class="bi bi-shield-exclamation display-1 text-danger"></i>
    <h1 class="display-4 mt-4">403 - Access Denied</h1>
    <p class="lead">You don't have permission to access this page.</p>
    <div class="mt-4">
        <a href="../dashboard.php" class="btn btn-primary">
            <i class="bi bi-house"></i> Go to Dashboard
        </a>
        <a href="../index.php" class="btn btn-outline-secondary">
            <i class="bi bi-globe"></i> Home Page
        </a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>