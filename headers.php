
<?php

require_once __DIR__ . "/functions.php";

requireLogin();

$pageTitle = $pageTitle ?? "Dashboard";
$activePage = $activePage ?? "dashboard";
$username = $_SESSION["username"] ?? "Admin";
$userRole = currentUserRole();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= clean($pageTitle) ?> | Jobowtak Fitness GYM</title>

    <link rel="stylesheet" href="style.css">
    <?php if ($activePage === "dashboard"): ?>
        <link rel="stylesheet" href="dashboard.css">
    <?php endif; ?>
</head>

<body>
<div class="app">
    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <div class="logo-avatar">
                <img src="assets/images/jabowtak-logo.jpg"
                     alt="Jabowtak Fitness GYM Logo">
            </div>
            <div>
                <h2>JOBOWTAK</h2>
                <span>FITNESS GYM</span>
            </div>
        </div>

        <p class="tagline">Stronger Today, Healthier Tomorrow</p>

        <nav>
            <a href="dashboard.php" class="<?= $activePage === "dashboard" ? "active" : "" ?>">
                Dashboard
            </a>
            <a href="members.php" class="<?= $activePage === "members" ? "active" : "" ?>">
                Members
            </a>
            <a href="workouts.php" class="<?= $activePage === "workouts" ? "active" : "" ?>">
                Workout Plans
            </a>
            <a href="exercises.php" class="<?= $activePage === "exercises" ? "active" : "" ?>">
                Exercises
            </a>
            <a href="progress.php" class="<?= $activePage === "progress" ? "active" : "" ?>">
                Progress Tracking
            </a>
            <a href="memberships.php" class="<?= $activePage === "memberships" ? "active" : "" ?>">
                Memberships
            </a>
            <a href="payments.php" class="<?= $activePage === "payments" ? "active" : "" ?>">
                Payments
            </a>
            <a href="reports.php" class="<?= $activePage === "reports" ? "active" : "" ?>">
                Reports
            </a>
        </nav>

        <div class="sidebar-bottom">
            <form
                action="logout.php"
                method="POST"
                onsubmit="return confirm('Are you sure you want to log out?');"
            >
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= clean($_SESSION["csrf_token"] ?? "") ?>"
                >
                <button type="submit" class="btn">Logout</button>
            </form>
        </div>
    </aside>

    <main class="main">
        <header class="topbar">
            <button type="button" id="menuBtn" class="menu-btn" aria-label="Toggle navigation">
                ☰
            </button>

            <div class="top-user">
                <span class="online-dot"></span>
                <?= clean($username) ?>
                <small><?= clean(ucfirst($userRole)) ?></small>
            </div>
        </header>

        <section class="content">
            <?php showMessage(); ?>
