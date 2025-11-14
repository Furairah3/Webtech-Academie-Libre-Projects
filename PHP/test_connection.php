<?php
// Include the config file
include 'config.php';

// If we reach here without errors, connection is successful
echo "✅ Database connection successful!";

// Optional: Show database info
echo "<br>Database: " . $database;
echo "<br>Connected to: " . mysqli_get_host_info($conn);

// Close connection (good practice)
mysqli_close($conn);
?>