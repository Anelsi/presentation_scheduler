<?php
session_start();
date_default_timezone_set('Europe/Sofia'); // Слагаме правилната часова зона и тук!

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../config/db.php";

$userId = $_SESSION["user_id"];
$now = date("Y-m-d H:i:s");

// Променяме заявката - добавяме проверка за датата и часа (да са в бъдещето)
$sql = "
SELECT presentations.*
FROM personal_schedule
JOIN presentations ON personal_schedule.presentation_id = presentations.id
WHERE personal_schedule.user_id = :user_id
AND CONCAT(presentations.presentation_date, ' ', presentations.end_time) >= :now
ORDER BY presentations.presentation_date ASC, presentations.start_time ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ":user_id" => $userId,
    ":now" => $now
]);

$presentations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Schedule</title>
    <style>
        body{ margin:0; padding:40px; background:#f4f6f9; font-family:Arial,sans-serif; }
        .page-title{ font-size:42px; margin-bottom:30px; color:#111827; text-align:center; }
        .back-link{ display:inline-block; margin-bottom:25px; text-decoration:none; color:#4f46e5; font-weight:bold; font-size:16px; }
        .schedule-container{ display:grid; grid-template-columns:repeat(auto-fill,minmax(320px,1fr)); gap:20px; }
        .schedule-card{ background:white; padding:25px; border-radius:20px; box-shadow:0 4px 15px rgba(0,0,0,0.1); transition:0.3s; }
        .schedule-card:hover{ transform:translateY(-5px); border-left: 6px solid #4f46e5; }
        .presentation-title{ font-size:24px; margin-bottom:15px; color:#111827; }
        .presentation-description{ color:#6b7280; margin-bottom:20px; line-height:1.6; }
        .info{ margin-bottom:10px; color:#374151; }
        .actions{ margin-top:20px; display:flex; gap:10px; flex-wrap:wrap; }
        .btn{ padding:10px 14px; border-radius:8px; text-decoration:none; color:white; font-size:14px; transition:0.3s; }
        .open-btn{ background:#2563eb; } .open-btn:hover{ background:#1d4ed8; }
        .remove-btn{ background:#dc2626; } .remove-btn:hover{ background:#b91c1c; }
        .empty-message { background: white; padding: 20px; border-radius: 16px; color: #6b7280; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.05); font-size: 18px; grid-column: 1 / -1; }
    </style>
</head>
<body>
    <?php require_once "../includes/navbar.php"; ?>

    <a href="dashboard.php" class="back-link">← Back to Dashboard</a>

    <h1 class="page-title">My Schedule</h1>

    <div class="schedule-container">
        <?php if (count($presentations) === 0): ?>
            <div class="empty-message">
                Your schedule is currently empty. Go to Presentations to add upcoming events!
            </div>
        <?php else: ?>
            <?php foreach ($presentations as $presentation): ?>
                <div class="schedule-card">
                    <h2 class="presentation-title"><?php echo htmlspecialchars($presentation["title"]); ?></h2>
                    <p class="presentation-description"><?php echo htmlspecialchars($presentation["description"]); ?></p>
                    <p class="info"><strong>Presenter:</strong> <?php echo htmlspecialchars($presentation["presenter"]); ?></p>
                    <p class="info"><strong>Category:</strong> <?php echo htmlspecialchars($presentation["category"]); ?></p>
                    <p class="info"><strong>Date:</strong> <?php echo $presentation["presentation_date"]; ?></p>
                    <p class="info"><strong>Time:</strong> <?php echo $presentation["start_time"]; ?> - <?php echo $presentation["end_time"]; ?></p>
                    <p class="info"><strong>Room:</strong> <?php echo htmlspecialchars($presentation["room"]); ?></p>

                    <div class="actions">
                        <?php if (!empty($presentation["file_url"])): ?>
                            <a href="<?php echo $presentation["file_url"]; ?>" target="_blank" class="btn open-btn">Open Presentation</a>
                        <?php endif; ?>
                        
                        <a href="../actions/remove_from_schedule.php?id=<?php echo $presentation["id"]; ?>" class="btn remove-btn">Remove</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>