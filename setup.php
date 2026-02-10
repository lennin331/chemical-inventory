<?php
// setup.php - One-click setup
echo "<h2>Chemical Inventory System Setup</h2>";

// Create directories
if (!is_dir('database')) {
    mkdir('database', 0755, true);
    echo "<div class='alert alert-success'>✅ Created database directory</div>";
}

if (!is_dir('uploads')) {
    mkdir('uploads', 0755, true);
    echo "<div class='alert alert-success'>✅ Created uploads directory</div>";
}

// Database file path
$db_file = 'database/chemicals.db';

// Check if database already exists
if (file_exists($db_file)) {
    echo "<div class='alert alert-warning'>⚠️ Database already exists. Delete database/chemicals.db to reset.</div>";
    echo "<a href='index.php' class='btn btn-primary'>Go to Home</a>";
    exit();
}

try {
    // Create database connection
    $db = new SQLite3($db_file);
    
    echo "<div class='alert alert-success'>✅ Database created successfully</div>";
    
    // Create users table
    $db->exec("CREATE TABLE users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password_hash TEXT NOT NULL,
        full_name TEXT NOT NULL,
        role TEXT DEFAULT 'admin',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "<div class='alert alert-success'>✅ Created users table</div>";
    
    // Create categories table
    $db->exec("CREATE TABLE categories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "<div class='alert alert-success'>✅ Created categories table</div>";
    
    // Create chemicals table
    $db->exec("CREATE TABLE chemicals (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        cas_number TEXT NOT NULL,
        name TEXT NOT NULL,
        formula TEXT,
        category_id INTEGER,
        quantity REAL NOT NULL,
        unit TEXT DEFAULT 'g',
        location TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "<div class='alert alert-success'>✅ Created chemicals table</div>";
    
    // Create admin user (password: admin123)
    $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
    $db->exec("INSERT INTO users (username, password_hash, full_name, role) 
               VALUES ('admin', '$password_hash', 'System Administrator', 'admin')");
    echo "<div class='alert alert-success'>✅ Created admin user</div>";
    
    // Add sample categories
    $db->exec("INSERT INTO categories (name, description) VALUES 
               ('Solvents', 'Organic and inorganic solvents'),
               ('Acids', 'Various laboratory acids'),
               ('Bases', 'Alkaline chemicals'),
               ('Salts', 'Inorganic salts'),
               ('Organics', 'Organic compounds')");
    echo "<div class='alert alert-success'>✅ Added sample categories</div>";
    
    // Add sample chemicals
    $db->exec("INSERT INTO chemicals (cas_number, name, formula, category_id, quantity, unit, location) VALUES 
               ('64-17-5', 'Ethanol', 'C2H5OH', 1, 5000, 'mL', 'Storage Room A'),
               ('7664-93-9', 'Sulfuric Acid', 'H2SO4', 2, 1000, 'mL', 'Acid Cabinet'),
               ('1310-73-2', 'Sodium Hydroxide', 'NaOH', 3, 500, 'g', 'Base Cabinet'),
               ('7647-14-5', 'Sodium Chloride', 'NaCl', 4, 1000, 'g', 'Salt Shelf')");
    echo "<div class='alert alert-success'>✅ Added sample chemicals</div>";
    
    echo "<div class='alert alert-success mt-4'><h4>🎉 Setup Complete!</h4></div>";
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>❌ Setup failed: " . $e->getMessage() . "</div>";
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Setup Complete</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; background: #f8f9fa; }
        .container { max-width: 800px; margin: 0 auto; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card mt-4">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0">Setup Successful!</h4>
            </div>
            <div class="card-body">
                <h5>Login Credentials:</h5>
                <div class="alert alert-info">
                    <p><strong>Username:</strong> admin</p>
                    <p><strong>Password:</strong> admin123</p>
                </div>
                
                <div class="alert alert-danger">
                    <strong>⚠️ Security Notice:</strong> Change this password after first login!
                </div>
                
                <h5>Next Steps:</h5>
                <ol>
                    <li>Login with the credentials above</li>
                    <li>Change the admin password immediately</li>
                    <li>Start adding your own chemicals</li>
                </ol>
                
                <div class="mt-4">
                    <a href="login.php" class="btn btn-primary btn-lg">
                        <i class="bi bi-box-arrow-in-right"></i> Go to Login
                    </a>
                    <a href="index.php" class="btn btn-secondary btn-lg">
                        <i class="bi bi-house"></i> Home Page
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>