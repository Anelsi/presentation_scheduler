<?php

session_start();

require_once "../config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION["user_id"];

$allCategories = [
    'Cloud',
    'AI',
    'Web',
    'Security'
];

$sql = "
SELECT category
FROM interests
WHERE user_id = :user_id
";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ":user_id" => $userId
]);

$userInterests = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>My Interests</title>

<style>

body{
    margin:0;
    padding:40px;
    background:#f4f6f9;
    font-family:Arial,sans-serif;
}

.container{
    max-width:700px;
    margin:auto;
}

.card{
    background:white;
    padding:30px;
    border-radius:20px;
    box-shadow:0 4px 15px rgba(0,0,0,0.1);
}

.page-title{
    font-size:42px;
    margin-bottom:30px;
    text-align:center;
}

.category{
    margin-bottom:15px;
    font-size:18px;
}

.save-btn{
    margin-top:25px;
    padding:12px 20px;
    background:#4f46e5;
    color:white;
    border:none;
    border-radius:10px;
    cursor:pointer;
}

.alert{
    background:#d1fae5;
    color:#065f46;
    padding:15px;
    border-radius:10px;
    margin-bottom:20px;
}

</style>

</head>

<body>

<?php require_once "../includes/navbar.php"; ?>

<div class="container">

<h1 class="page-title">My Interests</h1>

<?php if(isset($_SESSION["success"])): ?>
<div class="alert">
    <?php
    echo $_SESSION["success"];
    unset($_SESSION["success"]);
    ?>
</div>
<?php endif; ?>

<div class="card">

<form action="../actions/save_interests.php" method="POST">

<?php foreach($allCategories as $category): ?>

<div class="category">

<label>

<input
type="checkbox"
name="categories[]"
value="<?php echo $category; ?>"
<?php if(in_array($category,$userInterests)) echo "checked"; ?>
>

<?php echo $category; ?>

</label>

</div>

<?php endforeach; ?>

<button type="submit" class="save-btn">
Save Interests
</button>

</form>

</div>

</div>

</body>
</html>