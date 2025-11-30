<?php
/**
 * Database Configuration File for Academie Libre
 * This file handles the connection to MySQL database
 * All other PHP files will include this to access the database
 */

// Database configuration settings
$host = "localhost";        // XAMPP usually runs MySQL on localhost
$username = "root";         // Default XAMPP MySQL username
$password = "";             // Default XAMPP MySQL password (usually empty)
$database = "academie_libre"; // Your database name

/**
 * Create database connection using mysqli
 * mysqli = MySQL Improved extension
 */
$conn = mysqli_connect($host, $username, $password, $database);

/**
 * Check if connection was successful
 * If connection fails, display error message and stop execution
 */
if (!$conn) {
    // die() function stops script execution and displays message
    die("Database connection failed: " . mysqli_connect_error());
}

/**
 * Set character set to UTF-8
 * This ensures proper handling of special characters and emojis
 * Important for international users and various languages
 */
if (!mysqli_set_charset($conn, "utf8mb4")) {
    die("Error setting character set: " . mysqli_error($conn));
}

echo "Database connected successfully!";

/**
 * How to use this file in other PHP scripts:
 * include 'php/config.php';
 * Then you can use $conn variable for database operations
 */

?>