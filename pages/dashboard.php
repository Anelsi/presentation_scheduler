<?php

session_start();


if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");
    exit;

}

require_once "../config/db.php";

date_default_timezone_set("Europe/Sofia");

$now = date("Y-m-d H:i:s");
$today = date("Y-m-d");

$totalPresentations = (int) $pdo
    ->query("SELECT COUNT(*) FROM presentations")
    ->fetchColumn();

$upcomingCountSql = "
SELECT COUNT(*)
FROM presentations
WHERE CONCAT(presentation_date, ' ', end_time) >= :now
";

$upcomingCountStmt = $pdo->prepare($upcomingCountSql);
$upcomingCountStmt->execute([
    ":now" => $now
]);

$upcomingPresentationsCount = (int) $upcomingCountStmt->fetchColumn();

$popularCategorySql = "
SELECT category, COUNT(*) AS presentation_count
FROM presentations
WHERE category IS NOT NULL
AND category <> ''
GROUP BY category
ORDER BY presentation_count DESC, category ASC
LIMIT 1
";

$popularCategoryStmt = $pdo->prepare($popularCategorySql);
$popularCategoryStmt->execute();
$popularCategory = $popularCategoryStmt->fetch(PDO::FETCH_ASSOC);

$mostScheduledSql = "
SELECT p.title, COUNT(ps.id) AS schedule_count
FROM personal_schedule ps
JOIN presentations p ON ps.presentation_id = p.id
GROUP BY p.id, p.title
ORDER BY schedule_count DESC, p.title ASC
LIMIT 1
";

$mostScheduledStmt = $pdo->prepare($mostScheduledSql);
$mostScheduledStmt->execute();
$mostScheduledPresentation = $mostScheduledStmt->fetch(PDO::FETCH_ASSOC);

$todaySql = "
SELECT *
FROM presentations
WHERE presentation_date = :today
ORDER BY start_time ASC
";

$todayStmt = $pdo->prepare($todaySql);
$todayStmt->execute([
    ":today" => $today
]);

$todayPresentations = $todayStmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Dashboard</title>

    <style>

        body{
            margin:0;
            padding:40px;
            background:#f4f6f9;
            font-family:Arial,sans-serif;
        }

        .dashboard-container{
            max-width:1000px;
            margin:0 auto;
        }

        .top-bar{
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:40px;
        }

        .welcome-title{
            font-size:42px;
            color:#111827;
            margin:0;
        }

        .logout-btn{
            background:#dc2626;
            color:white;
            padding:12px 20px;
            border-radius:10px;
            text-decoration:none;
            transition:0.3s;
        }

        .logout-btn:hover{
            background:#b91c1c;
        }

        .cards-container{
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(280px,1fr));
            gap:25px;
        }

        .stats-grid{
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(210px,1fr));
            gap:18px;
            margin-bottom:30px;
        }

        .stat-card{
            background:white;
            padding:22px;
            border-radius:16px;
            box-shadow:0 4px 15px rgba(0,0,0,0.08);
            border-left:5px solid #4f46e5;
        }

        .stat-label{
            color:#6b7280;
            font-size:14px;
            font-weight:bold;
            text-transform:uppercase;
            margin-bottom:10px;
        }

        .stat-value{
            color:#111827;
            font-size:32px;
            font-weight:bold;
            line-height:1.15;
            overflow-wrap:anywhere;
        }

        .stat-value.text{
            font-size:22px;
        }

        .stat-detail{
            color:#6b7280;
            margin-top:8px;
            font-size:14px;
        }

        .dashboard-card{
            background:white;
            padding:30px;
            border-radius:20px;
            box-shadow:0 4px 15px rgba(0,0,0,0.1);
            transition:0.3s;
        }

        .dashboard-card:hover{
            transform:translateY(-5px);
        }

        .card-title{
            font-size:24px;
            margin-bottom:15px;
            color:#111827;
        }

        .card-text{
            color:#6b7280;
            margin-bottom:25px;
            line-height:1.6;
        }

        .card-btn{
            display:inline-block;
            padding:12px 18px;
            background:#4f46e5;
            color:white;
            text-decoration:none;
            border-radius:10px;
            transition:0.3s;
        }

        .card-btn:hover{
            background:#3730a3;
        }
        .today-section{
    margin-top:50px;
}

.today-title{
    font-size:32px;
    color:#111827;
    margin-bottom:20px;
}

.today-card{
    background:white;
    padding:20px;
    border-radius:15px;
    margin-bottom:15px;
    box-shadow:0 4px 15px rgba(0,0,0,0.08);
}

.today-card h3{
    margin:0 0 10px 0;
}

.today-info{
    color:#6b7280;
    margin-bottom:5px;
}

.empty-message{
    background:white;
    padding:20px;
    border-radius:15px;
    color:#6b7280;
}

    </style>

</head>

<body>
    <?php require_once "../includes/navbar.php"; ?>

    <div class="dashboard-container">

        <div class="top-bar">

            <h1 class="welcome-title">
                Welcome,
                <?php echo htmlspecialchars($_SESSION["user_name"]); ?>
            </h1>

        </div>

        <div class="stats-grid">

            <div class="stat-card">
                <div class="stat-label">Total Presentations</div>
                <div class="stat-value">
                    <?php echo $totalPresentations; ?>
                </div>
                <div class="stat-detail">All created events</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Upcoming Events</div>
                <div class="stat-value">
                    <?php echo $upcomingPresentationsCount; ?>
                </div>
                <div class="stat-detail">Still available to attend</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Popular Category</div>
                <div class="stat-value text">
                    <?php echo $popularCategory ? htmlspecialchars($popularCategory["category"]) : "None"; ?>
                </div>
                <div class="stat-detail">
                    <?php if ($popularCategory): ?>
                        <?php echo (int) $popularCategory["presentation_count"]; ?> presentation(s)
                    <?php else: ?>
                        No categories yet
                    <?php endif; ?>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Most Scheduled</div>
                <div class="stat-value text">
                    <?php echo $mostScheduledPresentation ? htmlspecialchars($mostScheduledPresentation["title"]) : "None"; ?>
                </div>
                <div class="stat-detail">
                    <?php if ($mostScheduledPresentation): ?>
                        <?php echo (int) $mostScheduledPresentation["schedule_count"]; ?> schedule add(s)
                    <?php else: ?>
                        No schedule activity yet
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <div class="cards-container">

            <div class="dashboard-card">

                <h2 class="card-title">
                    Presentations
                </h2>

                <p class="card-text">
                    View all created presentations,
                    uploaded files and schedules.
                </p>

                <a
                    href="presentations.php"
                    class="card-btn"
                >
                    Open Presentations
                </a>

            </div>

            <div class="dashboard-card">

                <h2 class="card-title">
                    Create Presentation
                </h2>

                <p class="card-text">
                    Create a new presentation,
                    upload files and organize events.
                </p>

                <a
                    href="create_presentation.php"
                    class="card-btn"
                >
                    Create Now
                </a>

            </div>

        </div>

        <div class="today-section">

    <h2 class="today-title">
        Today's Presentations
    </h2>

    <?php if (count($todayPresentations) > 0): ?>

        <?php foreach ($todayPresentations as $presentation): ?>

            <div class="today-card">

                <h3>
                    <?php echo htmlspecialchars($presentation["title"]); ?>
                </h3>

                <div class="today-info">
                    Presenter:
                    <?php echo htmlspecialchars($presentation["presenter"]); ?>
                </div>

                <div class="today-info">
                    Time:
                    <?php echo $presentation["start_time"]; ?>
                    -
                    <?php echo $presentation["end_time"]; ?>
                </div>

                <div class="today-info">
                    Room:
                    <?php echo htmlspecialchars($presentation["room"]); ?>
                </div>

            </div>

        <?php endforeach; ?>

    <?php else: ?>

        <div class="empty-message">
            No presentations scheduled for today.
        </div>

    <?php endif; ?>

</div>

    </div>

</body>

</html>
