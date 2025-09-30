<?php

$host = "localhost";   
$user = "root";        
$pass = "11";            
$dbname = "DBlearnIT"; 

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
