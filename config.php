<?php
$host = 'sql102.iceiy.com';
$dbname = 'icei_40017832_mahirwe_ecommerce';
$username = 'icei_40017832';
$password = 'Rugeraa2007';

try {
    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Connection error: " . $e->getMessage());
}
?>