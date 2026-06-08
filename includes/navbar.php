<style>
.navbar{
    background:#111827;
    padding:20px 40px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin:-40px -40px 40px -40px;
}
.nav-left,
.nav-right{
    display:flex;
    gap:20px;
    align-items:center;
}
.nav-logo{
    color:white;
    font-size:24px;
    font-weight:bold;
    text-decoration:none;
}
.nav-link{
    color:#d1d5db;
    text-decoration:none;
    font-size:16px;
    transition:0.3s;
}
.nav-link:hover{
    color:white;
}
.logout-btn{
    background:#dc2626;
    color:white;
    padding:10px 16px;
    border-radius:10px;
    text-decoration:none;
    transition:0.3s;
}
.logout-btn:hover{
    background:#b91c1c;
}
</style>

<div class="navbar">
    <div class="nav-left">
        <a href="dashboard.php" class="nav-logo">
            Presentation Scheduler
        </a>
        <a href="dashboard.php" class="nav-link">
            Dashboard
        </a>
        <a href="presentations.php" class="nav-link">
            Presentations
        </a>
        <?php if ($_SESSION["user_role"] === "student"): ?>
            <a href="my_schedule.php" class="nav-link">
                My Schedule
            </a>
            <a href="my_interests.php" class="nav-link">
                My Interests
            </a>
            <a href="recommended.php" class="nav-link">
                Recommended
            </a>
        <?php endif; ?>
    </div>
    <div class="nav-right">
        <span style="color:white;">
            <?php echo htmlspecialchars($_SESSION["user_name"]); ?>
        </span>
        <a href="../actions/logout.php" class="logout-btn">
            Logout
        </a>
    </div>
</div>