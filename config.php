<?php
session_start();
$message = "";

$db_username = 'root';
$db_password = '';
$conn = new PDO( 'mysql:host=localhost;dbname=bluebank', $db_username, $db_password );
if(!$conn){
    die("Fatal Error: Connection Failed!");
}
?>