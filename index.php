<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chemical Inventory System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .hero {
            background: white;
            border-radius: 15px;
            padding: 50px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="hero">
            <h1 class="display-4 mb-4"><b>Chemical Inventory System</b></h1>
            <p class="lead mb-4">A simple system to manage your chemical inventory</p>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <p>You are logged in as <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
                <a href="dashboard.php" class="btn btn-primary btn-lg">Go to Dashboard</a>
                <a href="logout.php" class="btn btn-secondary btn-lg">Logout</a>
            <?php else: ?>
                <?php if (file_exists('database/chemicals.db')): ?>
                    <a href="login.php" class="btn btn-primary btn-lg">Login</a>
                <?php else: ?>
                    <a href="setup.php" class="btn btn-success btn-lg">Setup Database</a>
                <?php endif; ?>
                <p class="mt-3 text-muted">Default login: admin / admin123</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>