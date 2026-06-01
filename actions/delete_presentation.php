<?php

session_start();

require_once "../config/db.php";

$id = $_GET["id"];

$sql = "DELETE FROM presentations WHERE id = :id";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ":id" => $id
]);

header("Location: ../pages/presentations.php");
exit;