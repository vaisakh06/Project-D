<?php
// Database connection settings for the INITIAL-D project.
$host = "localhost";
$username = "root";
$password = "";
$database = "initial_d";

// Create a connection to the MySQL database using MySQLi.
$connection = mysqli_connect($host, $username, $password, $database);

// Stop the page and show an error message if the connection fails.
if (!$connection) {
    die("Database connection failed: " . mysqli_connect_error());
}
