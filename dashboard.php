<?php
require_once __DIR__ . "/db.php";
require_once __DIR__ . "/functions.php";

requireLogin();

$pageTitle = "Dashboard";
$activePage = "dashboard";

$stats = [
    "members_total" => 0,
    "active_memberships" => 0,
    "revenue_this_month" => 0.00,
    "workout_plans" => 0,
];

$recentMembers = [];
$revenueSeries = [42, 58, 61, 83, 71, 92, 78];
$chartLabels = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];
$systemNotice = "";

try {
    $tablesResult = $conn->query("SHOW TABLES");
    $tableNames = [];

    if ($tablesResult) {
        while ($row = $tablesResult->fetch_row()) {
            $tableNames[] = strtolower((string) $row[0]);
        }
    }

    if (in_array("members", $tableNames, true)) {
        $membersCountQuery = $conn->query("SELECT COUNT(*) AS total FROM members");
        if ($membersCountQuery && $membersCountQuery->num_rows > 0) {
            $stats["members_total"] = (int) ($membersCountQuery->fetch_assoc()["total"] ?? 0);
        }

        $recentMembersQuery = $conn->query(
            "SELECT full_name, email, created_at FROM members ORDER BY id DESC LIMIT 5"
        );

        if ($recentMembersQuery) {
            while ($member = $recentMembersQuery->fetch_assoc()) {
                $recentMembers[] = [
                    "name" => $member["full_name"] ?? "Unknown",
                    "email" => $member["email"] ?? "-",
                    "joined" => !empty($member["created_at"]) ? date("M j, Y", strtotime($member["created_at"])) : "-",
                ];
            }
        }
    }

    if (in_array("memberships", $tableNames, true)) {
        $activeMembershipsQuery = $conn->query(
            "SELECT COUNT(*) AS total FROM memberships WHERE LOWER(status) = 'active'"
        );

        if ($activeMembershipsQuery && $activeMembershipsQuery->num_rows > 0) {
            $stats["active_memberships"] = (int) ($activeMembershipsQuery->fetch_assoc()["total"] ?? 0);
        }
    }

    if (in_array("payments", $tableNames, true)) {
        $revenueQuery = $conn->query(
            "SELECT COALESCE(SUM(amount), 0) AS total
             FROM payments
             WHERE created_at >= DATE_FORMAT(CURRENT_DATE, '%Y-%m-01')
               AND created_at < DATE_ADD(DATE_FORMAT(CURRENT_DATE, '%Y-%m-01'), INTERVAL 1 MONTH)"
        );

        if ($revenueQuery && $revenueQuery->num_rows > 0) {
            $stats["revenue_this_month"] = (float) ($revenueQuery->fetch_assoc()["total"] ?? 0);
        }
    }

    if (in_array("workouts", $tableNames, true)) {
        $workoutsQuery = $conn->query("SELECT COUNT(*) AS total FROM workouts");
        if ($workoutsQuery && $workoutsQuery->num_rows > 0) {
            $stats["workout_plans"] = (int) ($workoutsQuery->fetch_assoc()["total"] ?? 0);
        }
    }
} catch (Throwable $e) {
    $systemNotice = "Dashboard data is temporarily unavailable.";
}

$membershipActivePct = 62;
$membershipPendingPct = 23;
$membershipExpiredPct = 15;
$revenueAmount = number_format((float) $stats["revenue_this_month"], 2, ".", ",");

require_once __DIR__ . "/headers.php";
?>

<style>
    .dashboard-shell {
        display: grid;
        gap: 22px;
        color: #f5f5f5;
    }

    .dashboard-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        flex-wrap: wrap;
    }

    .dashboard-header h1 {
        margin: 8px 0 0;
        font-size: clamp(2rem, 3.5vw, 2.7rem);
        line-height: 1.1;
        color: #f4f1e8;
        letter-spacing: -0.04em;
    }

    .eyebrow {
        display: inline-block;
        color: #d7b35a;
        font-size: 11px;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        font-weight: 700;
    }

    .chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(215, 179, 90, 0.35);
        background: rgba(215, 179, 90, 0.08);
        color: #f5dd9a;
        border-radius: 999px;
        padding: 10px 16px;
        min-height: 42px;
        font-size: 12px;
        font-weight: 600;
    }

    .alert-box {
        background: rgba(226, 145, 90, 0.08);
        border: 1px solid rgba(226, 145, 90, 0.4);
        color: #f8d8c5;
        border-radius: 12px;
        padding: 12px 14px;
        font-size: 13px;
    }

    .hero-panel {
        display: grid;
        grid-template-columns: 1.5fr 0.7fr;
        gap: 18px;
        background: linear-gradient(135deg, #111418 0%, #1b1c1f 42%, #0f1216 100%);
        border: 1px solid rgba(255, 255, 255, 0.06);
        border-radius: 22px;
        padding: clamp(20px, 3vw, 34px);
        box-shadow: 0 16px 48px rgba(0, 0, 0, 0.18);
    }

    .hero-copy {
        display: grid;
        gap: 14px;
    }

    .hero-copy h2 {
        font-size: clamp(1.8rem, 3vw, 3rem);
        line-height: 1.1;
        color: #fff;
        margin: 0;
        letter-spacing: -0.04em;
    }

    .hero-copy p {
        color: rgba(255, 255, 255, 0.72);
        font-size: 14px;
        max-width: 620px;
        margin: 0;
    }

    .hero-badge {
        display: grid;
        place-items: center;
        border-radius: 50%;
        width: 120px;
        height: 120px;
        margin-left: auto;
        background: rgba(212, 162, 50, 0.12);
        border: 3px solid rgba(212, 162, 50, 0.9);
        color: #f5d67d;
        font-size: 2rem;
        font-weight: 800;
        box-shadow: inset 0 0 22px rgba(212, 162, 50, 0.12);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 18px;
    }

    .stat-card {
        background: rgba(25, 29, 33, 0.95);
        border: 1px solid rgba(255, 255, 255, 0.06);
        border-left: 4px solid #d7b35a;
        border-radius: 18px;
        padding: 22px 20px;
        box-shadow: 0 12px 26px rgba(0, 0, 0, 0.12);
    }

    .stat-card.green { border-left-color: #37b26e; }
    .stat-card.blue { border-left-color: #5a8cff; }
    .stat-card.red { border-left-color: #ea6d5f; }

    .stat-icon {
        font-size: 1.5rem;
        margin-bottom: 10px;
    }

    .stat-label {
        color: rgba(255, 255, 255, 0.7);
        font-size: 11px;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        margin-bottom: 10px;
    }

    .stat-value {
        color: #fff;
        font-size: clamp(1.7rem, 3vw, 2.2rem);
        font-weight: 800;
        letter-spacing: -0.04em;
    }

    .stat-meta {
        margin-top: 10px;
        color: rgba(255, 255, 255, 0.6);
        font-size: 12px;
    }

    .content-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.7fr) minmax(280px, 1fr);
        gap: 20px;
    }

    .panel {
        background: rgba(25, 29, 33, 0.95);
        border: 1px solid rgba(255, 255, 255, 0.06);
        border-radius: 20px;
        padding: 22px;
        box-shadow: 0 12px 26px rgba(0, 0, 0, 0.12);
    }

    .panel-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 20px;
    }

    .panel-head h3 {
        margin: 0;
        color: #f4f1e8;
        font-size: 1.1rem;
    }

    .text-link {
        color: #d7b35a;
        text-decoration: none;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .chart {
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 12px;
        height: 220px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        padding: 10px 4px 0;
    }

    .chart-column {
        display: flex;
        flex: 1;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        height: 100%;
    }

    .chart-value {
        font-size: 10px;
        color: rgba(255, 255, 255, 0.72);
    }

    .chart-bar-wrap {
        display: flex;
        align-items: end;
        width: 100%;
        height: 100%;
        justify-content: center;
    }

    .chart-bar {
        width: 52%;
        min-height: 12px;
        border-radius: 12px 12px 0 0;
        background: linear-gradient(180deg, #f5d67d 0%, #d7b35a 100%);
    }

    .chart-label {
        font-size: 11px;
        color: rgba(255, 255, 255, 0.62);
    }

    .membership-wrap {
        display: grid;
        justify-items: center;
        gap: 18px;
    }

    .membership-ring {
        width: 170px;
        height: 170px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        background: conic-gradient(#38b66a 0 62%, #d7b35a 62% 85%, #e9695d 85% 100%);
    }

    .membership-inner {
        width: 118px;
        height: 118px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        background: rgba(18, 22, 26, 0.96);
        border: 1px solid rgba(255, 255, 255, 0.08);
        text-align: center;
    }

    .membership-inner strong {
        display: block;
        font-size: 2rem;
        color: #fff;
        line-height: 1;
    }

    .membership-inner span {
        color: rgba(255, 255, 255, 0.7);
        font-size: 11px;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .status-list {
        display: grid;
        gap: 12px;
        width: 100%;
    }

    .status-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        font-size: 13px;
        color: rgba(255, 255, 255, 0.8);
    }

    .status-left {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .status-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.7fr) minmax(260px, 1fr);
        gap: 20px;
    }

    .table-wrap {
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 500px;
    }

    th, td {
        text-align: left;
        padding: 14px 12px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    th {
        color: #d7b35a;
        font-size: 11px;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    td {
        color: rgba(255, 255, 255, 0.85);
        font-size: 13px;
    }

    .muted {
        color: rgba(255, 255, 255, 0.62);
        font-size: 12px;
    }

    .quick-actions {
        display: grid;
        gap: 12px;
    }

    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 48px;
        border-radius: 12px;
        padding: 12px 16px;
        font-weight: 700;
        text-decoration: none;
        transition: transform 0.2s ease, filter 0.2s ease;
    }

    .action-btn:hover {
        transform: translateY(-1px);
        filter: brightness(1.04);
    }

    .action-btn.primary {
        background: linear-gradient(180deg, #f5d67d 0%, #d7b35a 100%);
        color: #111;
    }

    .action-btn.secondary {
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.09);
        color: #fff;
    }

    .empty-state {
        color: rgba(255, 255, 255, 0.62);
        font-size: 13px;
        padding: 12px 0;
    }

    @media (max-width: 980px) {
        .stats-grid,
        .content-grid,
        .detail-grid,
        .hero-panel {
            grid-template-columns: 1fr;
        }

        .hero-badge {
            margin-left: 0;
        }
    }

    @media (max-width: 640px) {
        .dashboard-shell {
            gap: 18px;
        }

        .panel,
        .stat-card,
        .hero-panel {
            border-radius: 16px;
        }

        .stats-grid {
            grid-template-columns: 1fr 1fr;
        }

        .panel-head {
            align-items: flex-start;
            flex-direction: column;
        }
    }

    @media (max-width: 480px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .chart {
            gap: 8px;
        }
    }
</style>

<div class="dashboard-shell">
    <?php if (!empty($systemNotice)): ?>
        <div class="alert-box"><?= clean($systemNotice) ?></div>
    <?php endif; ?>

    <header class="dashboard-header">
        <div>
            <span class="eyebrow">Executive overview</span>
            <h1>Fitness Dashboard</h1>
        </div>
        <div class="chip">Updated <?= date("M j, Y") ?></div>
    </header>

    <section class="hero-panel">
        <div class="hero-copy">
            <span class="eyebrow">Welcome back</span>
            <h2>Keep the club strong, premium, and fully booked.</h2>
            <p>Monitor revenue, memberships, member activity, and workout performance from a polished admin dashboard built for a premium fitness brand.</p>
        </div>
        <div class="hero-badge"><?= number_format((int) $stats["members_total"]) ?></div>
    </section>

    <section class="stats-grid">
        <article class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-label">Members</div>
            <div class="stat-value"><?= number_format((int) $stats["members_total"]) ?></div>
            <div class="stat-meta">Registered members</div>
        </article>

        <article class="stat-card green">
            <div class="stat-icon">✅</div>
            <div class="stat-label">Active</div>
            <div class="stat-value"><?= number_format((int) $stats["active_memberships"]) ?></div>
            <div class="stat-meta">Current memberships</div>
        </article>

        <article class="stat-card blue">
            <div class="stat-icon">💰</div>
            <div class="stat-label">Revenue</div>
            <div class="stat-value">$<?= clean($revenueAmount) ?></div>
            <div class="stat-meta">This month</div>
        </article>

        <article class="stat-card red">
            <div class="stat-icon">🏋️</div>
            <div class="stat-label">Plans</div>
            <div class="stat-value"><?= number_format((int) $stats["workout_plans"]) ?></div>
            <div class="stat-meta">Workout library</div>
        </article>
    </section>

    <section class="content-grid">
        <div class="panel">
            <div class="panel-head">
                <h3>Revenue overview</h3>
                <a class="text-link" href="payments.php">View payments</a>
            </div>

            <div class="chart">
                <?php foreach ($revenueSeries as $index => $value): ?>
                    <div class="chart-column">
                        <div class="chart-value">$<?= $value ?></div>
                        <div class="chart-bar-wrap">
                            <div class="chart-bar" style="height: <?= max(18, $value) ?>%;"></div>
                        </div>
                        <div class="chart-label"><?= $chartLabels[$index] ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <h3>Membership status</h3>
                <a class="text-link" href="memberships.php">Manage</a>
            </div>

            <div class="membership-wrap">
                <div class="membership-ring">
                    <div class="membership-inner">
                        <div>
                            <strong><?= $membershipActivePct ?>%</strong>
                            <span>Active</span>
                        </div>
                    </div>
                </div>

                <div class="status-list">
                    <div class="status-item">
                        <span class="status-left"><span class="status-dot" style="background:#38b66a"></span>Active</span>
                        <strong><?= $membershipActivePct ?>%</strong>
                    </div>
                    <div class="status-item">
                        <span class="status-left"><span class="status-dot" style="background:#d7b35a"></span>Pending</span>
                        <strong><?= $membershipPendingPct ?>%</strong>
                    </div>
                    <div class="status-item">
                        <span class="status-left"><span class="status-dot" style="background:#e9695d"></span>Expired</span>
                        <strong><?= $membershipExpiredPct ?>%</strong>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="detail-grid">
        <div class="panel">
            <div class="panel-head">
                <h3>Recent members</h3>
                <a class="text-link" href="members.php">Open list</a>
            </div>

            <?php if (!empty($recentMembers)): ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentMembers as $member): ?>
                                <tr>
                                    <td><?= clean($member["name"]) ?></td>
                                    <td class="muted"><?= clean($member["email"]) ?></td>
                                    <td class="muted"><?= clean($member["joined"]) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">No member records are available yet.</div>
            <?php endif; ?>
        </div>

        <div class="panel">
            <div class="panel-head">
                <h3>Quick actions</h3>
            </div>

            <div class="quick-actions">
                <a href="members.php" class="action-btn primary">Add new member</a>
                <a href="workouts.php" class="action-btn secondary">Create workout plan</a>
                <a href="payments.php" class="action-btn secondary">Review payments</a>
                <a href="reports.php" class="action-btn secondary">Open reports</a>
            </div>
        </div>
    </section>
</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
