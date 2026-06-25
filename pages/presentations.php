<?php
session_start();
require_once "../config/db.php";

date_default_timezone_set('Europe/Sofia');

$userId = $_SESSION["user_id"];

// 1. Взимаме ID-тата на вече запазените презентации
$scheduledSql = "SELECT presentation_id FROM personal_schedule WHERE user_id = :user_id";
$scheduledStmt = $pdo->prepare($scheduledSql);
$scheduledStmt->execute([':user_id' => $userId]);
$scheduledIds = $scheduledStmt->fetchAll(PDO::FETCH_COLUMN);

// Фиксирани категории
$allFixedCategories = ['Cloud', 'AI', 'Web', 'Security'];

// 2. Взимаме избраните категории (ще бъде масив)
$selectedCategories = $_GET['categories'] ?? [];

// ВЗИМАМЕ ТОЧНИЯ МОМЕНТ (ДАТА И ЧАС)
$now = date("Y-m-d H:i:s");

// 3. Заявка за Предстоящи (Където краят на презентацията е след ТОЗИ момент)
$upcomingSql = "SELECT * FROM presentations WHERE CONCAT(presentation_date, ' ', end_time) >= ?";
$upcomingParams = [$now];

if (!empty($selectedCategories)) {
    $inQuery = implode(',', array_fill(0, count($selectedCategories), '?'));
    $upcomingSql .= " AND category IN ($inQuery)";
    $upcomingParams = array_merge($upcomingParams, $selectedCategories);
}
$upcomingSql .= " ORDER BY presentation_date ASC, start_time ASC";

$upcomingStmt = $pdo->prepare($upcomingSql);
$upcomingStmt->execute($upcomingParams);
$upcomingPresentations = $upcomingStmt->fetchAll(PDO::FETCH_ASSOC);

// 4. Заявка за Минали (Където краят на презентацията е преди ТОЗИ момент)
$pastSql = "SELECT * FROM presentations WHERE CONCAT(presentation_date, ' ', end_time) < ?";
$pastParams = [$now];

if (!empty($selectedCategories)) {
    $inQuery = implode(',', array_fill(0, count($selectedCategories), '?'));
    $pastSql .= " AND category IN ($inQuery)";
    $pastParams = array_merge($pastParams, $selectedCategories);
}
$pastSql .= " ORDER BY presentation_date DESC, start_time DESC";

$pastStmt = $pdo->prepare($pastSql);
$pastStmt->execute($pastParams);
$pastPresentations = $pastStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Presentations</title>
    <style>
        body{ font-family: Arial, sans-serif; background:#f4f6f9; margin:0; padding:40px; }
        .page-title{ font-size:42px; margin-bottom:20px; color:#222; }
        .section-title { font-size: 30px; margin-bottom: 25px; margin-top: 40px; color: #111827; border-bottom: 2px solid #e5e7eb; padding-bottom: 10px; }
        .page-actions{ display:flex; gap:12px; align-items:center; flex-wrap:wrap; margin-bottom:30px; }
        .create-btn{ display:inline-block; padding:12px 20px; background:#4f46e5; color:white; text-decoration:none; border-radius:10px; transition:0.3s; }
        .create-btn:hover{ background:#3730a3; }
        .export-btn{ display:inline-block; padding:12px 20px; background:#111827; color:white; text-decoration:none; border-radius:10px; transition:0.3s; }
        .export-btn:hover{ background:#374151; }
        .excel-btn{ display:inline-block; padding:12px 20px; background:#047857; color:white; text-decoration:none; border-radius:10px; transition:0.3s; }
        .excel-btn:hover{ background:#065f46; }
        
        .filter-form { display: flex; gap: 20px; align-items: center; margin-bottom: 30px; background: white; padding: 20px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); flex-wrap: wrap; }
        .filter-checkboxes { display: flex; gap: 15px; }
        .filter-checkboxes label { cursor: pointer; font-size: 16px; color: #374151; display: flex; align-items: center; gap: 5px; }
        .filter-btn { padding: 10px 20px; background: #111827; color: white; border: none; border-radius: 8px; cursor: pointer; }
        .clear-filter { color: #dc2626; text-decoration: none; font-weight: bold; margin-left: auto; }
        
        .alert-success { background: #d1fae5; color: #065f46; padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #a7f3d0; font-weight: bold; }
        .alert-error { background: #fde8e8; color: #dc2626; padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #f8b4b4; font-weight: bold; }
        .empty-message { background: white; padding: 20px; border-radius: 16px; color: #6b7280; margin-bottom: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); font-size: 18px; }

        .presentations-container{ display:grid; grid-template-columns:repeat(auto-fill,minmax(320px,1fr)); gap:20px; align-items:stretch; }
        .presentation-card{ background:white; padding:25px; border-radius:20px; box-shadow:0 4px 15px rgba(0,0,0,0.1); transition:0.3s; display:flex; flex-direction:column; justify-content:space-between; min-height:450px; }
        .presentation-card:hover{ transform:translateY(-5px); }
        
        .upcoming { border-left: 6px solid #059669; }
        .presented { border-left: 6px solid #dc2626; opacity: 0.85; }

        .presentation-title{ font-size:24px; margin-bottom:10px; color:#111827; }
        .presentation-description{ color:#6b7280; margin-bottom:15px; }
        .info{ margin-bottom:8px; color:#374151; }
        .actions{ margin-top:20px; display:flex; gap:10px; flex-wrap:wrap; }
        .btn{ padding:10px 14px; border-radius:8px; text-decoration:none; color:white; font-size:14px; flex:1; text-align:center; transition: 0.3s; box-sizing: border-box; }
        
        .open-btn{ background:#2563eb; } .open-btn:hover{ background:#1d4ed8; }
        .edit-btn{ background:#059669; } .edit-btn:hover{ background:#047857; }
        .delete-btn{ background:#dc2626; } .delete-btn:hover{ background:#b91c1c; }
        .added-btn { background: #e5e7eb; color: #6b7280; cursor: default; border: 1px solid #d1d5db; }
        .added-btn:hover { transform: none; background: #e5e7eb; }
    </style>
</head>
<body>
    <?php require_once "../includes/navbar.php"; ?>

    <h1 class="page-title">All Presentations</h1>

    <?php if (isset($_SESSION["success"])): ?>
        <div class="alert-success"><?php echo htmlspecialchars($_SESSION["success"]); unset($_SESSION["success"]); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION["error"])): ?>
        <div class="alert-error"><?php echo htmlspecialchars($_SESSION["error"]); unset($_SESSION["error"]); ?></div>
    <?php endif; ?>

    <div class="page-actions">
        <?php if ($_SESSION["user_role"] === "admin"): ?>
            <a href="create_presentation.php" class="create-btn">+ Create Presentation</a>
        <?php endif; ?>

        <a href="../actions/export_presentations_csv.php" class="export-btn">Export CSV</a>
        <a href="../actions/export_presentations_excel.php" class="excel-btn">Export Excel</a>
    </div>

    <form class="filter-form" action="presentations.php" method="GET">
        <label style="font-weight: bold; color: #374151;">Filter by Categories:</label>
        
        <div class="filter-checkboxes">
            <?php foreach ($allFixedCategories as $cat): ?>
                <label>
                    <input type="checkbox" name="categories[]" value="<?php echo htmlspecialchars($cat); ?>" 
                        <?php if(in_array($cat, $selectedCategories)) echo 'checked'; ?>>
                    <?php echo htmlspecialchars($cat); ?>
                </label>
            <?php endforeach; ?>
        </div>

        <button type="submit" class="filter-btn">Apply Filter</button>
        
        <?php if (!empty($selectedCategories)): ?>
            <a href="presentations.php" class="clear-filter">Clear Filter</a>
        <?php endif; ?>
    </form>

    <h2 class="section-title">Upcoming Presentations</h2>
    <?php if (count($upcomingPresentations) === 0): ?>
        <div class="empty-message">There are no incoming presentations from that category.</div>
    <?php else: ?>
        <div class="presentations-container">
            <?php foreach ($upcomingPresentations as $presentation): ?>
                <div class="presentation-card upcoming">
                    <div>
                        <h2 class="presentation-title">
                            <a href="presentation_details.php?id=<?php echo $presentation["id"]; ?>" style="text-decoration:none; color:#111827;">
                                <?php echo htmlspecialchars($presentation["title"]); ?>
                            </a>
                        </h2>
                        <p class="presentation-description"><?php echo htmlspecialchars($presentation["description"]); ?></p>
                        <p class="info"><strong>Presenter:</strong> <?php echo htmlspecialchars($presentation["presenter"]); ?></p>
                        <p class="info"><strong>Category:</strong> <?php echo htmlspecialchars($presentation["category"]); ?></p>
                        <p class="info"><strong>Date:</strong> <?php echo $presentation["presentation_date"]; ?></p>
                        <p class="info"><strong>Time:</strong> <?php echo $presentation["start_time"]; ?> - <?php echo $presentation["end_time"]; ?></p>
                        <p class="info"><strong>Room:</strong> <?php echo htmlspecialchars($presentation["room"]); ?></p>
                    </div>

                    <div class="actions">
                        <?php if (!empty($presentation["file_url"])): ?>
                            <a href="<?php echo $presentation["file_url"]; ?>" target="_blank" class="btn open-btn">Open Presentation</a>
                        <?php endif; ?>

                        <?php if ($_SESSION["user_role"] === "admin"): ?>
                            <a href="edit_presentation.php?id=<?php echo $presentation["id"]; ?>" class="btn edit-btn">Edit</a>
                            <a href="../actions/delete_presentation.php?id=<?php echo $presentation["id"]; ?>" class="btn delete-btn">Delete</a>
                        <?php else: ?>
                            <?php if (in_array($presentation["id"], $scheduledIds)): ?>
                                <span class="btn added-btn">✓ Added</span>
                            <?php else: ?>
                                <a href="../actions/add_to_schedule.php?id=<?php echo $presentation["id"]; ?>" class="btn edit-btn">Add To Schedule</a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <h2 class="section-title">Past Presentations</h2>
    <?php if (count($pastPresentations) === 0): ?>
        <div class="empty-message">There are no past presentations from that category.</div>
    <?php else: ?>
        <div class="presentations-container">
            <?php foreach ($pastPresentations as $presentation): ?>
                <div class="presentation-card presented">
                    <div>
                        <h2 class="presentation-title">
                            <a href="presentation_details.php?id=<?php echo $presentation["id"]; ?>" style="text-decoration:none; color:#111827;">
                                <?php echo htmlspecialchars($presentation["title"]); ?>
                            </a>
                        </h2>
                        <p class="presentation-description"><?php echo htmlspecialchars($presentation["description"]); ?></p>
                        <p class="info"><strong>Presenter:</strong> <?php echo htmlspecialchars($presentation["presenter"]); ?></p>
                        <p class="info"><strong>Category:</strong> <?php echo htmlspecialchars($presentation["category"]); ?></p>
                        <p class="info"><strong>Date:</strong> <?php echo $presentation["presentation_date"]; ?></p>
                        <p class="info"><strong>Time:</strong> <?php echo $presentation["start_time"]; ?> - <?php echo $presentation["end_time"]; ?></p>
                        <p class="info"><strong>Room:</strong> <?php echo htmlspecialchars($presentation["room"]); ?></p>
                    </div>

                    <div class="actions">
                        <?php if (!empty($presentation["file_url"])): ?>
                            <a href="<?php echo $presentation["file_url"]; ?>" target="_blank" class="btn open-btn">Open Presentation</a>
                        <?php endif; ?>

                        <?php if ($_SESSION["user_role"] === "admin"): ?>
                            <a href="edit_presentation.php?id=<?php echo $presentation["id"]; ?>" class="btn edit-btn">Edit</a>
                            <a href="../actions/delete_presentation.php?id=<?php echo $presentation["id"]; ?>" class="btn delete-btn">Delete</a>
                        <?php else: ?>
                            <?php if (in_array($presentation["id"], $scheduledIds)): ?>
                                <span class="btn added-btn">✓ Added</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</body>
</html>
