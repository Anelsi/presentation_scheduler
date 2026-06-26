<?php

session_start();

require_once "../config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../pages/login.php");
    exit;
}

if (($_SESSION["user_role"] ?? "") !== "admin") {
    $_SESSION["error"] = "Only admins can delete presentations.";
    header("Location: ../pages/presentations.php");
    exit;
}

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if ($id === false || $id === null) {
    $_SESSION["error"] = "Invalid presentation selected for deletion.";
    header("Location: ../pages/presentations.php");
    exit;
}

try {
    $pdo->beginTransaction();

    $scheduleStmt = $pdo->prepare(
        "DELETE FROM personal_schedule WHERE presentation_id = :id"
    );
    $scheduleStmt->execute([
        ":id" => $id
    ]);

    $presentationStmt = $pdo->prepare(
        "DELETE FROM presentations WHERE id = :id"
    );
    $presentationStmt->execute([
        ":id" => $id
    ]);

    if ($presentationStmt->rowCount() === 0) {
        $pdo->rollBack();
        $_SESSION["error"] = "Presentation was not found.";
        header("Location: ../pages/presentations.php");
        exit;
    }

    $pdo->commit();

    $removedSchedules = $scheduleStmt->rowCount();
    $_SESSION["success"] = "Presentation deleted successfully.";

    if ($removedSchedules > 0) {
        $_SESSION["success"] .= " Removed from " . $removedSchedules . " schedule(s).";
    }
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    $_SESSION["error"] = "The presentation could not be deleted. Please try again.";
}

header("Location: ../pages/presentations.php");
exit;
