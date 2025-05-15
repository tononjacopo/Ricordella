<?php
require_once '../config/db.php';
require_once '../utils/functions.php';
requireAdmin();

$logDir = __DIR__ . '/../logs/';
$logFiles = array_filter(scandir($logDir), function($f) {
    return is_file(__DIR__ . '/../logs/' . $f) && preg_match('/\.log$/', $f);
});
sort($logFiles, SORT_NATURAL | SORT_FLAG_CASE);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <title>Logs | Ricordella Admin</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../assets/style/admin-users.css">
    <link rel="stylesheet" href="../assets/style/admin-logs.css">
    <link rel="stylesheet" href="../assets/style/font-general.css">
    <link rel="icon" href="../assets/img/logo-favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="logo">Ricordella Admin</div>
        <nav>
            <a href="stats.php">Stats</a>
            <a href="user_list.php">Users</a>
            <a href="logs.php" class="active">Logs</a>
        </nav>
        <div class="user-info">
            <span>Admin: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="../logout.php" class="logout">Logout</a>
        </div>
    </header>
    <main>
        <!-- Explorer sidebar -->
        <div class="explorer">
            <div class="explorer-header">
                <span>Explorer</span>
                <!-- Refresh button -->
                <button type="button" class="refresh-btn" id="refresh-logs" title="Aggiorna logs">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
            <ul class="log-files">
                <?php foreach ($logFiles as $file): ?>
                    <li class="log-file" data-file="<?php echo htmlspecialchars($file); ?>">
                        <i class="fas fa-file-lines"></i>
                        <span><?php echo htmlspecialchars($file); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Editor area -->
        <div class="editor-area">
            <!-- Tabs bar -->
            <div class="tabs" id="editor-tabs"></div>

            <!-- Editor content -->
            <div class="editor-content" id="editor-content">
                <div class="welcome-pane">
                    <i class="fas fa-file-alt"></i>
                    <h3>Select a log file</h3>
                    <p>Click on a file in the sidebar to view it</p>
                </div>
            </div>

        </div>
    </main>
    <footer>
        <p>&copy; <?php echo date('Y'); ?> Ricordella - Admin Panel</p>
    </footer>

<script src="../assets/script/logs.js"></script>
</body>
</html>