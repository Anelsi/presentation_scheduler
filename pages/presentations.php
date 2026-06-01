<?php

session_start();

require_once "../config/db.php";

$sql = "
SELECT * FROM presentations
ORDER BY presentation_date ASC
";

$stmt = $pdo->query($sql);

$presentations = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Presentations</title>
    <style>

body{
    font-family: Arial, sans-serif;
    background:#f4f6f9;
    margin:0;
    padding:40px;
}

.page-title{
    font-size:42px;
    margin-bottom:20px;
    color:#222;
}

.create-btn{
    display:inline-block;
    padding:12px 20px;
    background:#4f46e5;
    color:white;
    text-decoration:none;
    border-radius:10px;
    margin-bottom:30px;
    transition:0.3s;
}

.create-btn:hover{
    background:#3730a3;
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
    transition:0.3s;

    display:flex;
    flex-direction:column;
    justify-content:space-between;

    min-height:500px;
}

.presentation-card:hover{
    transform:translateY(-5px);
}

.presentation-title{
    font-size:24px;
    margin-bottom:10px;
    color:#111827;
}

.presentation-description{
    color:#6b7280;
    margin-bottom:15px;
}

.info{
    margin-bottom:8px;
    color:#374151;
}

.actions{
    margin-top:auto;
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
}

.open-btn{
    background:#2563eb;
}

.edit-btn{
    background:#059669;
}

.delete-btn{
    background:#dc2626;
}

</style>
</head>

<body>
    <?php require_once "../includes/navbar.php"; ?>

    <h1 class="page-title">
    All Presentations
    </h1>

    <?php if ($_SESSION["user_role"] === "admin"): ?>

    <a
    href="create_presentation.php"
    class="create-btn"
    >
    + Create Presentation
    </a>

<?php endif; ?>

    <br><br>

    <div class="presentations-container">
    <?php foreach ($presentations as $presentation): ?>

        <div class="presentation-card">

            <h2 class="presentation-title">

    <a
        href="presentation_details.php?id=<?php echo $presentation["id"]; ?>"
        style="
            text-decoration:none;
            color:#111827;
        "
    >
        <?php echo htmlspecialchars($presentation["title"]); ?>
    </a>

</h2>

            <p class="presentation-description">
                <?php echo htmlspecialchars($presentation["description"]); ?>
            </p>

            <p class="info">
                Presenter:
                <?php echo htmlspecialchars($presentation["presenter"]); ?>
            </p>

            <p class="info">
                Category:
                <?php echo htmlspecialchars($presentation["category"]); ?>
            </p>

            <p class="info">
                Date:
                <?php echo $presentation["presentation_date"]; ?>
            </p>

            <p class="info">
                Time:
                <?php echo $presentation["start_time"]; ?>
                -
                <?php echo $presentation["end_time"]; ?>
            </p>

            <p class="info">
                Room:
                <?php echo htmlspecialchars($presentation["room"]); ?>
            </p>


    <?php if ($_SESSION["user_role"] === "admin"): ?>

<div class="actions">

    <?php if (!empty($presentation["file_url"])): ?>

        <a
            href="<?php echo $presentation["file_url"]; ?>"
            target="_blank"
            class="btn open-btn"
        >
            Open Presentation
        </a>

    <?php endif; ?>

    <a
        href="edit_presentation.php?id=<?php echo $presentation["id"]; ?>"
        class="btn edit-btn"
    >
        Edit
    </a>

    <a
        href="../actions/delete_presentation.php?id=<?php echo $presentation["id"]; ?>"
        class="btn delete-btn"
    >
        Delete
    </a>

</div>

<?php else: ?>

<div class="actions">

    <?php if (!empty($presentation["file_url"])): ?>

        <a
            href="<?php echo $presentation["file_url"]; ?>"
            target="_blank"
            class="btn open-btn"
        >
            Open Presentation
        </a>

    <?php endif; ?>

    <a
        href="../actions/add_to_schedule.php?id=<?php echo $presentation["id"]; ?>"
        class="btn edit-btn"
    >
        Add To Schedule
    </a>

</div>

<?php endif; ?>

        </div>

    <?php endforeach; ?>

</div>

</body>

</html>