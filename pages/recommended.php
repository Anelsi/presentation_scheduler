<?php

session_start();

require_once "../config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION["user_id"];

$scheduledSql = "SELECT presentation_id FROM personal_schedule WHERE user_id = :user_id";
$scheduledStmt = $pdo->prepare($scheduledSql);
$scheduledStmt->execute([
    ":user_id" => $userId
]);
$scheduledIds = $scheduledStmt->fetchAll(PDO::FETCH_COLUMN);

$sql = "
SELECT DISTINCT p.*
FROM presentations p
JOIN interests i
ON p.category = i.category
WHERE i.user_id = :user_id
AND CONCAT(p.presentation_date, ' ', p.end_time) >= NOW()
ORDER BY p.presentation_date ASC, p.start_time ASC
";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ":user_id" => $userId
]);

$presentations = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang='en'>
<head>
<meta charset='UTF-8'>
<title>Recommended For You</title>

<style>

body{
    margin:0;
    padding:40px;
    background:#f4f6f9;
    font-family:Arial,sans-serif;
}

.page-title{
    font-size:42px;
    text-align:center;
    margin-bottom:30px;
    color:#111827;
}

.presentations-container{
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(320px,1fr));
    gap:20px;
    align-items:stretch;
}

.presentation-card{
    background:white;
    padding:25px;
    border-radius:20px;
    box-shadow:0 4px 15px rgba(0,0,0,0.1);
    display:flex;
    flex-direction:column;
}

.presentation-title{
    font-size:24px;
    margin-bottom:10px;
}

.info{
    margin-bottom:8px;
}

.empty-message{
    background:white;
    padding:20px;
    border-radius:15px;
    text-align:center;
}

.alert-success{
    background:#d1fae5;
    color:#065f46;
    padding:15px;
    border-radius:10px;
    margin-bottom:20px;
    border:1px solid #a7f3d0;
    font-weight:bold;
}

.alert-error{
    background:#fde8e8;
    color:#dc2626;
    padding:15px;
    border-radius:10px;
    margin-bottom:20px;
    border:1px solid #f8b4b4;
    font-weight:bold;
}

.actions{
    margin-top:auto;
    padding-top:20px;
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}

.btn{
    padding:10px 14px;
    border-radius:8px;
    text-decoration:none;
    color:white;
    font-size:14px;
    flex:1;
    text-align:center;
    box-sizing:border-box;
}

.add-btn{
    background:#059669;
}

.add-btn:hover{
    background:#047857;
}

.added-btn{
    background:#e5e7eb;
    color:#6b7280;
    cursor:default;
    border:1px solid #d1d5db;
}

</style>

</head>

<body>

<?php require_once "../includes/navbar.php"; ?>

<h1 class="page-title">
    Recommended For You
</h1>

<?php if (isset($_SESSION["success"])): ?>
    <div class="alert-success"><?php echo htmlspecialchars($_SESSION["success"]); unset($_SESSION["success"]); ?></div>
<?php endif; ?>

<?php if (isset($_SESSION["error"])): ?>
    <div class="alert-error"><?php echo htmlspecialchars($_SESSION["error"]); unset($_SESSION["error"]); ?></div>
<?php endif; ?>

<?php if(count($presentations) === 0): ?>

<div class="empty-message">
    No recommendations yet.
    Select interests first.
</div>

<?php else: ?>

<div class="presentations-container">

<?php foreach($presentations as $presentation): ?>

<div class="presentation-card">

<h2 class="presentation-title">
<?php echo htmlspecialchars($presentation["title"]); ?>
</h2>

<p>
<?php echo htmlspecialchars($presentation["description"]); ?>
</p>

<p class="info">
<strong>Category:</strong>
<?php echo htmlspecialchars($presentation["category"]); ?>
</p>

<p class="info">
<strong>Presenter:</strong>
<?php echo htmlspecialchars($presentation["presenter"]); ?>
</p>

<p class="info">
<strong>Date:</strong>
<?php echo $presentation["presentation_date"]; ?>
</p>

<p class="info">
<strong>Room:</strong>
<?php echo htmlspecialchars($presentation["room"]); ?>
</p>

<div class="actions">
<?php if (in_array($presentation["id"], $scheduledIds)): ?>
    <span class="btn added-btn">Added</span>
<?php else: ?>
    <a href="../actions/add_to_schedule.php?id=<?php echo $presentation["id"]; ?>" class="btn add-btn">Add To Schedule</a>
<?php endif; ?>
</div>

</div>

<?php endforeach; ?>

</div>

<?php endif; ?>

</body>
</html>
