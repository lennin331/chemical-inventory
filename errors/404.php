<?php
http_response_code(404);
$pageTitle = "Page Not Found";
require_once '../config/config.php';
?>

<?php include '../includes/header.php'; ?>

<div class="text-center py-5">
    <i class="bi bi-question-circle display-1 text-warning"></i>
    <h1 class="display-4 mt-4">404 - Page Not Found</h1>
    <p class="lead">The page you're looking for doesn't exist.</p>
    <div class="mt-4">
        <a href="../dashboard.php" class="btn btn-primary">
            <i class="bi bi-house"></i> Go to Dashboard
        </a>
        <a href="javascript:history.back()" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Go Back
        </a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>