<?php
ini_set('display_errors', 1);

error_reporting(E_ALL);

session_start();

require_once "../config/db.php";

require_once "../config/db.php";

$name = trim($_POST["name"]);
$email = trim($_POST["email"]);
$password = trim($_POST["password"]);

$errors = [];

if (empty($name)) {
    $errors[] = "Name is required";
}

if (empty($email)) {
    $errors[] = "Email is required";
}

if (empty($password)) {
    $errors[] = "Password is required";
}

if (!empty($errors)) {

    $_SESSION["errors"] = $errors;

    header("Location: ../pages/register.php");
    exit;
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO users (name, email, password)
        VALUES (:name, :email, :password)";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ":name" => $name,
    ":email" => $email,
    ":password" => $hashedPassword
]);

$_SESSION["success"] = "Registration successful";

header("Location: ../pages/login.php");
exit;