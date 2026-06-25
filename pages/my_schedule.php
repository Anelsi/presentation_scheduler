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

$currentView = $_GET["view"] ?? "calendar";

if (!in_array($currentView, ["calendar", "list"], true)) {
    $currentView = "calendar";
}

$defaultMonth = count($presentations) > 0
    ? date("Y-m", strtotime($presentations[0]["presentation_date"]))
    : date("Y-m");

$selectedMonth = $_GET["month"] ?? $defaultMonth;
$monthDate = DateTime::createFromFormat("!Y-m", $selectedMonth);

if ($monthDate === false || $monthDate->format("Y-m") !== $selectedMonth) {
    $monthDate = DateTime::createFromFormat("!Y-m", $defaultMonth);
    $selectedMonth = $defaultMonth;
}

$monthStart = clone $monthDate;
$calendarStart = clone $monthStart;
$calendarStart->modify("-" . ((int) $monthStart->format("N") - 1) . " days");

$monthEnd = clone $monthStart;
$monthEnd->modify("last day of this month");

$calendarEnd = clone $monthEnd;
$calendarEnd->modify("+" . (7 - (int) $monthEnd->format("N")) . " days");

$previousMonth = (clone $monthStart)->modify("-1 month")->format("Y-m");
$nextMonth = (clone $monthStart)->modify("+1 month")->format("Y-m");
$monthLabel = $monthStart->format("F Y");

$presentationsByDate = [];

foreach ($presentations as $presentation) {
    $date = $presentation["presentation_date"];

    if (!isset($presentationsByDate[$date])) {
        $presentationsByDate[$date] = [];
    }

    $presentationsByDate[$date][] = $presentation;
}
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
        .view-tabs{ display:flex; justify-content:center; gap:12px; margin:-10px 0 25px; flex-wrap:wrap; }
        .view-tab{ padding:10px 18px; border-radius:10px; text-decoration:none; color:#374151; background:white; border:1px solid #d1d5db; font-weight:bold; }
        .view-tab.active{ background:#4f46e5; color:white; border-color:#4f46e5; }
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
        .schedule-actions{ text-align:center; margin:-10px 0 30px; }
        .print-btn{ display:inline-block; padding:12px 18px; background:#111827; color:white; text-decoration:none; border-radius:10px; font-weight:bold; transition:0.3s; }
        .print-btn:hover{ background:#374151; transform:translateY(-2px); }
        .empty-message { background: white; padding: 20px; border-radius: 16px; color: #6b7280; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.05); font-size: 18px; grid-column: 1 / -1; }
        .calendar-toolbar{ display:flex; justify-content:space-between; align-items:center; gap:16px; margin-bottom:20px; background:white; padding:18px 20px; border-radius:16px; box-shadow:0 4px 15px rgba(0,0,0,0.05); }
        .calendar-month{ margin:0; color:#111827; font-size:28px; text-align:center; }
        .month-link{ padding:10px 14px; border-radius:8px; text-decoration:none; background:#111827; color:white; font-weight:bold; white-space:nowrap; }
        .month-link:hover{ background:#374151; }
        .calendar-grid{ display:grid; grid-template-columns:repeat(7, minmax(0, 1fr)); gap:1px; background:#d1d5db; border:1px solid #d1d5db; border-radius:16px; overflow:hidden; box-shadow:0 4px 15px rgba(0,0,0,0.08); }
        .calendar-weekday{ background:#111827; color:white; padding:12px 8px; text-align:center; font-weight:bold; }
        .calendar-day{ min-height:150px; background:white; padding:10px; display:flex; flex-direction:column; gap:8px; }
        .calendar-day.outside-month{ background:#f3f4f6; color:#9ca3af; }
        .calendar-day.today{ box-shadow:inset 0 0 0 3px #4f46e5; }
        .day-number{ font-weight:bold; color:#111827; }
        .outside-month .day-number{ color:#9ca3af; }
        .calendar-event{ display:block; padding:8px; border-radius:8px; background:#eef2ff; color:#111827; text-decoration:none; border-left:4px solid #4f46e5; font-size:13px; line-height:1.35; }
        .calendar-event:hover{ background:#e0e7ff; }
        .event-time{ display:block; font-weight:bold; color:#4f46e5; margin-bottom:2px; }
        .event-title{ display:block; font-weight:bold; }
        .event-room{ display:block; color:#6b7280; margin-top:2px; }
        .event-remove{ display:inline-block; margin-top:6px; color:#dc2626; font-weight:bold; text-decoration:none; font-size:12px; }
        .event-remove:hover{ text-decoration:underline; }
        @media (max-width: 900px){
            .calendar-grid{ grid-template-columns:1fr; background:transparent; border:none; box-shadow:none; gap:12px; }
            .calendar-weekday{ display:none; }
            .calendar-day{ min-height:auto; border-radius:14px; box-shadow:0 4px 15px rgba(0,0,0,0.06); }
            .calendar-day.outside-month{ display:none; }
            .calendar-toolbar{ flex-direction:column; }
        }
    </style>
</head>
<body>
    <?php require_once "../includes/navbar.php"; ?>

    <a href="dashboard.php" class="back-link">← Back to Dashboard</a>

    <h1 class="page-title">My Schedule</h1>

    <div class="view-tabs">
        <a href="my_schedule.php?view=calendar&month=<?php echo urlencode($selectedMonth); ?>" class="view-tab <?php echo $currentView === "calendar" ? "active" : ""; ?>">Calendar View</a>
        <a href="my_schedule.php?view=list&month=<?php echo urlencode($selectedMonth); ?>" class="view-tab <?php echo $currentView === "list" ? "active" : ""; ?>">List View</a>
    </div>

    <div class="schedule-actions">
        <a href="../actions/print_schedule_pdf.php" class="print-btn">Print schedule to PDF</a>
    </div>

    <?php if (count($presentations) === 0): ?>
        <div class="schedule-container">
            <div class="empty-message">
                Your schedule is currently empty. Go to Presentations to add upcoming events!
            </div>
        </div>
    <?php elseif ($currentView === "calendar"): ?>
        <div class="calendar-toolbar">
            <a href="my_schedule.php?view=calendar&month=<?php echo urlencode($previousMonth); ?>" class="month-link">Previous</a>
            <h2 class="calendar-month"><?php echo htmlspecialchars($monthLabel); ?></h2>
            <a href="my_schedule.php?view=calendar&month=<?php echo urlencode($nextMonth); ?>" class="month-link">Next</a>
        </div>

        <div class="calendar-grid">
            <div class="calendar-weekday">Mon</div>
            <div class="calendar-weekday">Tue</div>
            <div class="calendar-weekday">Wed</div>
            <div class="calendar-weekday">Thu</div>
            <div class="calendar-weekday">Fri</div>
            <div class="calendar-weekday">Sat</div>
            <div class="calendar-weekday">Sun</div>

            <?php for ($day = clone $calendarStart; $day <= $calendarEnd; $day->modify("+1 day")): ?>
                <?php
                    $dateKey = $day->format("Y-m-d");
                    $dayClasses = ["calendar-day"];

                    if ($day->format("Y-m") !== $selectedMonth) {
                        $dayClasses[] = "outside-month";
                    }

                    if ($dateKey === date("Y-m-d")) {
                        $dayClasses[] = "today";
                    }
                ?>
                <div class="<?php echo implode(" ", $dayClasses); ?>">
                    <div class="day-number"><?php echo $day->format("j"); ?></div>

                    <?php foreach ($presentationsByDate[$dateKey] ?? [] as $presentation): ?>
                        <a href="presentation_details.php?id=<?php echo $presentation["id"]; ?>" class="calendar-event">
                            <span class="event-time"><?php echo htmlspecialchars(substr($presentation["start_time"], 0, 5)); ?> - <?php echo htmlspecialchars(substr($presentation["end_time"], 0, 5)); ?></span>
                            <span class="event-title"><?php echo htmlspecialchars($presentation["title"]); ?></span>
                            <span class="event-room"><?php echo htmlspecialchars($presentation["room"]); ?></span>
                        </a>
                        <a href="../actions/remove_from_schedule.php?id=<?php echo $presentation["id"]; ?>" class="event-remove">Remove</a>
                    <?php endforeach; ?>
                </div>
            <?php endfor; ?>
        </div>
    <?php else: ?>
        <div class="schedule-container">
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
        </div>
    <?php endif; ?>
</body>
</html>
