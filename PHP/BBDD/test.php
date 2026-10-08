<?php
$server_name = "localhost";
$username = "root";
$db_name = "test";

try {
  $conn = new PDO("mysql:host=$server_name;dbname=$db_name", $username);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  echo "Connected successfully";
} catch (PDOException $e) {
  echo "Connection failed: " . $e->getMessage();
}


try {
  $sql = "INSERT INTO user (name, surname, email)
  VALUES ('John', 'Doe', 'john@example.com')";
  $conn->exec($sql);
  echo "New record created successfully";
} catch (PDOException $e) {
  echo $sql . "<br>" . $e->getMessage();
}

$conn = null;
