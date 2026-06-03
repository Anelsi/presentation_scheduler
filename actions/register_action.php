<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

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

// Валидация на паролата
if (empty($password)) {
    $errors[] = "Password is required";
} else {
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    }
}

if (!empty($errors)) {
    $_SESSION["errors"] = $errors;
    
    // ЗАПАЗВАМЕ ВЪВЕДЕНИТЕ ДАННИ ПРИ ГРЕШКА
    $_SESSION["old_input"] = [
        "name" => $name,
        "email" => $email
    ];

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

$newUserId = $pdo->lastInsertId();

$_SESSION["user_id"] = $newUserId;
$_SESSION["user_name"] = $name;
$_SESSION["user_role"] = 'student';

$_SESSION["success"] = "Registration successful";

header("Location: ../pages/dashboard.php");
exit;