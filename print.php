<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$id = $_GET['id'] ?? 0;
if (!$id) {
    header('Location: chemicals.php');
    exit();
}

$db = new SQLite3('database/chemicals.db');
$stmt = $db->prepare("SELECT * FROM chemicals WHERE id = :id");
$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
$chemical = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

if (!$chemical) {
    header('Location: chemicals.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Print - <?php echo $chemical['name']; ?></title>
    <style>
        body { font-family: Arial; padding: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 10px; text-align: left; }
        th { background: #f0f0f0; }
        @media print {
            button { display: none; }
        }
    </style>
</head>
<body>
    <h1 style="text-align: center;">Chemical Information</h1>
    <table>
        <tr><th>Name:</th><td><?php echo $chemical['name']; ?></td></tr>
        <tr><th>CAS:</th><td><?php echo $chemical['cas_number']; ?></td></tr>
        <tr><th>Formula:</th><td><?php echo $chemical['formula']; ?></td></tr>
        <tr><th>Quantity:</th><td><?php echo $chemical['quantity'] . ' ' . $chemical['unit']; ?></td></tr>
        <tr><th>Location:</th><td><?php echo $chemical['location']; ?></td></tr>
    </table>
    <button onclick="window.print()">Print</button>
</body>
</html>
