<?php
require_once('./config.php'); // Include your database connection

if (isset($_POST['id'])) {
    $hallId = intval($_POST['id']); // Sanitize input
    $qry = $conn->query("SELECT price FROM hall_list WHERE id = $hallId AND delete_flag = 0 AND status = 1");

    if ($qry->num_rows > 0) {
        $hall = $qry->fetch_assoc();
        echo "<h3>Price/hour: INR " . number_format($hall['price']) . "</h3>";
    } else {
        echo "<p>Hall not found or inactive.</p>";
    }
} else {
    echo "<p>No hall ID received.</p>";
}
?>