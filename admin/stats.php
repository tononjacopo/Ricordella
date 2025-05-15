<?php
require_once '../config/db.php';
require_once '../utils/functions.php';
requireAdmin();
header('Cache-Control: no-store');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Analytics | Ricordella Admin</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../assets/style/admin-users.css">
    <link rel="stylesheet" href="../assets/style/admin-stats.css">
    <link rel="stylesheet" href="../assets/style/font-general.css">
    <link rel="icon" href="../assets/img/logo-favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <header>
        <div class="logo">Ricordella Admin</div>
        <nav>
            <a href="stats.php" class="active">Stats</a>
            <a href="user_list.php">Users</a>
            <a href="logs.php">Logs</a>
        </nav>
        <div class="user-info">
            <span>Admin: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="../logout.php" class="logout">Logout</a>
        </div>
    </header>
    <main>
        <div class="page-header">
            <h1>Data Analytics</h1>
            <div class="timestamp">
                <i class="fa-regular fa-clock"></i> <?php echo date('Y-m-d H:i:s'); ?>
            </div>
            <button id="refreshStats" class="refresh-btn" title="Refresh Statistics">
                <i class="fa-solid fa-arrows-rotate"></i>
            </button>
        </div>

        <div id="kpi" class="kpi-cards"></div>

        <div class="charts-row">
            <div class="event-box service-box">
                <h3>Services Status</h3>
                <ul id="serviceStatus"></ul>
            </div>
            <div class="chart-box performance-chart">
                <h3>System Performance</h3>
                <div class="chart-container">
                    <canvas id="performanceChart"></canvas>
                </div>
            </div>
            <div class="admin-status-box">
                <div id="admin-status-title-container">
                    <h3>Admin Status</h3>
                </div>
                <ul class="admin-list" id="adminList">
                    <!-- Loaded dynamically -->
                </ul>
            </div>
        </div>

        <div class="chart-box full-width">
            <h3>Weekly User Activity</h3>
            <div class="activity-metrics">
                <div class="activity-stat">
                    <span class="value" id="weeklyAvg">0</span>
                    <span class="label">Avg. Daily Users</span>
                </div>
                <div class="activity-stat">
                    <span class="value" id="weeklyTotal">0</span>
                    <span class="label">Total Active Users</span>
                </div>
                <div class="activity-stat">
                    <span class="value" id="weeklyPeak">0</span>
                    <span class="label">Peak Day</span>
                </div>
            </div>
            <div class="chart-container activity-chart-container">
                <canvas id="dailyAccesses"></canvas>
            </div>
        </div>

        <div class="charts-row">
            <div class="event-box">
                <h3>Last Events</h3>
                <div class="events-container">
                    <ul id="lastEvents"></ul>
                    <a href="logs.php" class="view-all-logs">View All Logs</a>
                </div>
            </div>
            <div class="chart-box">
                <h3>User Distribution</h3>
                <button class="settings-toggle" title="Edit Labels">
                    <i class="fa-solid fa-gear"></i>
                </button>
                <div class="settings-panel">
                    <label for="label1">Regular Users Label:</label>
                    <input type="text" id="label1" value="Regular Users">
                    <label for="label2">Premium Users Label:</label>
                    <input type="text" id="label2" value="Premium Users">
                    <label for="label3">Admin Label:</label>
                    <input type="text" id="label3" value="Administrators">
                    <button id="applyLabels">Apply</button>
                </div>
                <div class="chart-container">
                    <canvas id="userRoles"></canvas>
                    <div class="total-users" id="totalUsers">
                        <span class="count">0</span>
                        Users
                    </div>
                </div>
            </div>
        </div>
    </main>
    <footer>
        <p>&copy; <?php echo date('Y'); ?> Ricordella - Admin Panel</p>
    </footer>
    <script src="../assets/script/stats-data.js"></script>
</body>
</html>