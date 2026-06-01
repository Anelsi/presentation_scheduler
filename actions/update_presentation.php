<?php

session_start();

require_once "../config/db.php";

$id = $_POST["id"];

$title = trim($_POST["title"]);
$description = trim($_POST["description"]);
$presenter = trim($_POST["presenter"]);
$category = trim($_POST["category"]);

$presentation_date = $_POST["presentation_date"];

$start_time = $_POST["start_time"];
$end_time = $_POST["end_time"];

$room = trim($_POST["room"]);

$sql = "
UPDATE presentations
SET
    title = :title,
    description = :description,
    presenter = :presenter,
    category = :category,
    presentation_date = :presentation_date,
    start_time = :start_time,
    end_time = :end_time,
    room = :room
WHERE id = :id
";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ":title" => $title,
    ":description" => $description,
    ":presenter" => $presenter,
    ":category" => $category,
    ":presentation_date" => $presentation_date,
    ":start_time" => $start_time,
    ":end_time" => $end_time,
    ":room" => $room,
    ":id" => $id
]);

header("Location: ../pages/presentations.php");
exit;