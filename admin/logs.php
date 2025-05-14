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
    <link rel="stylesheet" href="../assets/style/admin.css">
    <link rel="stylesheet" href="../assets/style/font-general.css">
    <link rel="icon" href="../assets/img/logo-favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        main {
            display: flex;
            position: relative;
            padding: 0;
            height: calc(100vh - 120px);
        }

        /* Explorer sidebar */
        .explorer {
            width: 250px;
            background: #252526;
            color: #cccccc;
            overflow-y: auto;
            border-right: 1px solid #1e1e1e;
            height: 100%;
            padding: 10px 0;
        }

        .explorer-header {
            padding: 0 20px;
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
            color: #6c757d;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .log-files {
            list-style: none;
        }

        .log-file {
            padding: 6px 20px;
            cursor: pointer;
            display: flex;
            align-items: center;
            font-size: 13px;
            transition: background 0.2s;
        }

        .log-file:hover {
            background: #2a2d2e;
        }

        .log-file.active {
            background: #37373d;
        }

        .log-file i {
            margin-right: 8px;
            color: #75beff;
        }

        /* Editor area */
        .editor-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #1e1e1e;
            position: relative;
        }

        /* Tabs */
        .tabs {
            display: flex;
            background: #252526;
            border-bottom: 1px solid #1e1e1e;
            height: 35px;
            overflow-x: auto;
            white-space: nowrap;
        }

        .tabs::-webkit-scrollbar {
            height: 3px;
        }

        .tabs::-webkit-scrollbar-thumb {
            background: #3e3e42;
        }

        .tab {
            padding: 0 10px;
            height: 35px;
            line-height: 35px;
            font-size: 13px;
            color: #cccccc;
            border-right: 1px solid #1e1e1e;
            cursor: pointer;
            display: flex;
            align-items: center;
            min-width: 100px;
            max-width: 180px;
            position: relative;
        }

        .tab.active {
            background: #1e1e1e;
            color: #ffffff;
            border-top: 1px solid #4361ee;
        }

        .tab-name {
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .tab .close {
            width: 16px;
            height: 16px;
            line-height: 16px;
            text-align: center;
            border-radius: 3px;
            margin-left: 5px;
            font-size: 12px;
            visibility: hidden;
        }

        .tab:hover .close {
            visibility: visible;
        }

        .tab .close:hover {
            background: #3e3e42;
        }

        /* Editor Content */
        .editor-content {
            flex: 1;
            overflow: hidden;
            position: relative;
        }

        .editor-pane {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: none;
            overflow-y: auto;
            padding: 10px;
        }

        .editor-pane.active {
            display: block;
        }

        .terminal-log {
            font-family: 'Fira Mono', monospace, 'Courier New', Courier;
            font-size: 13px;
            line-height: 1.5;
            color: #d4d4d4;
            padding: 10px;
        }

        .terminal-log pre {
            margin: 0;
            white-space: pre-wrap;
            word-break: break-all;
        }

        /* Highlight colors for log entries */
        .log-date { color: #569cd6; }
        .log-info { color: #3ec46d; }
        .log-warning { color: #ce9178; }
        .log-error { color: #f14c4c; }
        .log-debug { color: #9cdcfe; }
        .log-critical { color: #ff5252; }

        /* Welcome pane when no file is open */
        .welcome-pane {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #6c757d;
        }

        .welcome-pane i {
            font-size: 3em;
            margin-bottom: 20px;
            color: #4361ee;
        }

        /* Refresh button */
        .refresh-btn {
            position: absolute;
            right: 20px;
            top: 20px;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 38px;
            height: 38px;
            box-shadow: var(--shadow-md);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background .2s;
            z-index: 10;
        }

        .refresh-btn:hover {
            background: var(--primary-dark);
        }

        .refresh-btn i {
            font-size: 1.2em;
        }

        .refresh-btn.spinning i {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {transform: rotate(360deg);}
        }

        /* Scrollbars */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #1e1e1e;
        }

        ::-webkit-scrollbar-thumb {
            background: #3e3e42;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #4361ee;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">Ricordella Admin</div>
        <nav>
            <a href="stats.php">Stats</a>
            <a href="dashboard.php">Users</a>
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
                    <h3>Seleziona un file di log</h3>
                    <p>Clicca su un file nella barra laterale per visualizzarlo</p>
                </div>
            </div>

            <!-- Refresh button -->
            <button type="button" class="refresh-btn" id="refresh-logs" title="Aggiorna logs">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </main>
    <footer>
        <p>&copy; <?php echo date('Y'); ?> Ricordella - Admin Panel</p>
    </footer>

</body>
</html>