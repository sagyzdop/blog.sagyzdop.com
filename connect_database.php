<?php
$servername = "localhost";
$username = "test";
$password = "test";
$dbname = "test";

$connection = new PDO("mysql:host=$servername; dbname=$dbname", $username, $password);
$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
