<?php

session_start();

require_once "../config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../pages/login.php");
    exit;
}

$referer = $_SERVER["HTTP_REFERER"] ?? "../pages/presentations.php";
$userId = (int) $_SESSION["user_id"];
$presentationId = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$presentationId) {
    $_SESSION["error"] = "Invalid presentation.";
    header("Location: " . $referer);
    exit;
}

$presentationSql = "
SELECT id, title, presentation_date, start_time, end_time
FROM presentations
WHERE id = :id
";

$presentationStmt = $pdo->prepare($presentationSql);
$presentationStmt->execute([
    ":id" => $presentationId
]);

$presentation = $presentationStmt->fetch(PDO::FETCH_ASSOC);

if (!$presentation) {
    $_SESSION["error"] = "Presentation not found.";
    header("Location: " . $referer);
    exit;
}

$checkSql = "
SELECT id
FROM personal_schedule
WHERE user_id = :user_id
AND presentation_id = :presentation_id
";

$checkStmt = $pdo->prepare($checkSql);
$checkStmt->execute([
    ":user_id" => $userId,
    ":presentation_id" => $presentationId
]);

$existing = $checkStmt->fetch();

if ($existing) {
    $_SESSION["error"] = "This presentation is already in your schedule.";
    header("Location: " . $referer);
    exit;
}

$conflictSql = "
SELECT p.title, p.start_time, p.end_time
FROM personal_schedule ps
JOIN presentations p ON ps.presentation_id = p.id
WHERE ps.user_id = :user_id
AND p.presentation_date = :presentation_date
AND p.start_time < :end_time
AND p.end_time > :start_time
ORDER BY p.start_time ASC
LIMIT 1
";

$conflictStmt = $pdo->prepare($conflictSql);
$conflictStmt->execute([
    ":user_id" => $userId,
    ":presentation_date" => $presentation["presentation_date"],
    ":start_time" => $presentation["start_time"],
    ":end_time" => $presentation["end_time"]
]);

$conflict = $conflictStmt->fetch(PDO::FETCH_ASSOC);

if ($conflict) {
    $_SESSION["error"] = "Schedule conflict: this presentation overlaps with \""
        . $conflict["title"]
        . "\" ("
        . substr($conflict["start_time"], 0, 5)
        . " - "
        . substr($conflict["end_time"], 0, 5)
        . ").";

    header("Location: " . $referer);
    exit;
}

$sql = "
INSERT INTO personal_schedule
(user_id, presentation_id)
VALUES
(:user_id, :presentation_id)
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ":user_id" => $userId,
    ":presentation_id" => $presentationId
]);

$_SESSION["success"] = "Presentation successfully added to your schedule!";

header("Location: " . $referer);
exit;
