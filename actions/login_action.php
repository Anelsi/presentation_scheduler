<?php

session_start();

require_once "../config/db.php";

$email = trim($_POST["email"]);
$password = trim($_POST["password"]);

$sql = "SELECT * FROM users WHERE email = :email";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ":email" => $email
]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION["error"] = "User not found or invalid credentials";
    // Запазваме имейла, дори и да не е намерен такъв потребител
    $_SESSION["old_input"] = ["email" => $email]; 
    header("Location: ../pages/login.php");
    exit;
}

if (!password_verify($password, $user["password"])) {
    $_SESSION["error"] = "Invalid password";
    // Запазваме имейла, за да остане попълнен
    $_SESSION["old_input"] = ["email" => $email]; 
    header("Location: ../pages/login.php");
    exit;
}

$_SESSION["user_id"] = $user["id"];
$_SESSION["user_name"] = $user["name"];
$_SESSION["user_role"] = $user["role"];

header("Location: ../pages/dashboard.php");
exit;