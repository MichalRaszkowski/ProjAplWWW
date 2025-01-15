<?php
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'moja_strona';

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Błąd połączenia z bazą danych: " . $conn->connect_error);
}

$login = 'admin';
$pass = 'haslo123';