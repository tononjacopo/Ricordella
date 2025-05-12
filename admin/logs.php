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
    <style>
        .terminal-log-container {
            display: flex;
            flex-wrap: wrap;
            gap: 2em;
            min-height: 400px;
        }
        .terminal-log {
            background: #181c20;
            color: #c3e88d;
            font-family: 'Fira Mono', monospace, 'Courier New', Courier;
            font-size: .98em;
            border-radius: 10px;
            box-shadow: var(--shadow-md);
            padding: 1.2em 1em .8em 1em;
            max-width: 570px;
            min-width: 340px;
            min-height: 350px;
            margin-bottom: 1.5em;
            position: relative;
            overflow: auto;
        }
        .terminal-log .log-title {
            font-size: 1.01em;
            color: #fff;
            margin-bottom: .6em;
            font-weight: bold;
            letter-spacing: .01em;
            display: flex;
            align-items: center;
            gap: .6em;
        }
        .terminal-log pre {
            margin: 0;
            white-space: pre-wrap;
            word-break: break-all;
        }
        .dropdown {
            min-width: 7em;
            width: 22em;
            position: relative;
            margin-top: 2em;
            z-index: 99;
        }
        .select {
            background: #181c20;
            color: #c3e88d;
            border: 1px solid #6a6a6a;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            width: 100%;
            cursor: pointer;
            transition: background 0.3s;
        }
        .select:hover{
            border-color: #29b6f6;
            box-shadow: 0 0 0.7em #29b6f6;
        }
        .caret {
            width: 0;
            height: 0;
            border-bottom: 5px solid transparent;
            border-top: 5px solid transparent;
            border-left: 6px solid #c3e88d;
            transition: 0.3s;
        }
        .caret-rotate { transform: rotate(+90deg); border-left: 6px solid #29b6f6;}
        .menu {
            list-style: none;
            padding: 0 0.5em;
            background: #181c20;
            border-radius: 0.5em;
            color: #c3e88d;
            position: absolute;
            top: 3em;
            left: 0;
            width: 100%;
            height: 0;
            transition: height 300ms cubic-bezier(0.77,0,0.18,1);
            z-index: 1;
            overflow-y: scroll;
            overflow-x: hidden;
            box-shadow: 0 1px 10px #2228;
        }
        .menu.show { height: 200px;}
        .menu li {
            padding: .5em .3em;
            cursor: pointer;
            border-radius: 4px;
        }
        .menu li:hover, .menu li.active {
            background: #263238;
            color: #fff;
        }
        /* Custom scrollbar come richiesto */
        .menu::-webkit-scrollbar { width: 6px;}
        .menu::-webkit-scrollbar-track { background: transparent;}
        .menu::-webkit-scrollbar-thumb { background: #29b6f6;}
        .menu::-webkit-scrollbar-thumb:hover { background: #f72585;}
        .menu::-webkit-scrollbar-thumb:active { background-color: #6a6a6a;}
        .terminal-log::-webkit-scrollbar { width: 8px;}
        .terminal-log::-webkit-scrollbar-thumb { background: #29b6f6;}
        .terminal-log::-webkit-scrollbar-thumb:hover { background: #f72585;}
        .terminal-log::-webkit-scrollbar-thumb:active { background-color: #6a6a6a;}
    </style>
</head>
<body>
    <header>
        <div class="logo">Ricordella Admin</div>
        <nav>
            <a href="dashboard.php">Users</a>
            <a href="logs.php" class="active">Logs</a>
            <a href="stats.php">Stats</a>
        </nav>
        <div class="user-info">
            <span>Admin: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="../logout.php" class="logout">Logout</a>
        </div>
    </header>
    <main>
        <h1>System Logs</h1>
        <div class="dropdown" id="dropdownLogs">
            <div class="select" id="selectBox">
                <span id="selectedLabel">Scegli file di log</span>
                <span class="caret"></span>
            </div>
            <ul class="menu" id="logMenu">
                <?php foreach ($logFiles as $file): ?>
                    <li data-log="<?php echo htmlspecialchars($file); ?>"><?php echo htmlspecialchars($file); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="terminal-log-container" id="logContainer"></div>
    </main>
    <footer>
        <p>&copy; <?php echo date('Y'); ?> Ricordella - Admin Panel</p>
    </footer>
    <script>
        // Custom dropdown log selector
        const selectBox = document.getElementById('selectBox');
        const caret = selectBox.querySelector('.caret');
        const menu = document.getElementById('logMenu');
        const label = document.getElementById('selectedLabel');
        const logContainer = document.getElementById('logContainer');
        let openedLogs = {};

        selectBox.addEventListener('click', () => {
            menu.classList.toggle('show');
            caret.classList.toggle('caret-rotate');
        });
        document.addEventListener('click', e => {
            if (!selectBox.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.remove('show');
                caret.classList.remove('caret-rotate');
            }
        });
        menu.querySelectorAll('li').forEach(li => {
            li.addEventListener('click', () => {
                const filename = li.getAttribute('data-log');
                if (!openedLogs[filename]) {
                    fetch('read_log.php?file=' + encodeURIComponent(filename))
                        .then(res => res.text())
                        .then(data => {
                            const logDiv = document.createElement('div');
                            logDiv.className = 'terminal-log';
                            logDiv.innerHTML = `<span class="log-title"><i class="fa-solid fa-file-lines"></i> ${filename}</span><pre>${data}</pre>`;
                            logContainer.appendChild(logDiv);
                            openedLogs[filename] = logDiv;
                        });
                }
                li.classList.add('active');
                menu.classList.remove('show');
                caret.classList.remove('caret-rotate');
                label.textContent = filename;
            });
        });
    </script>
</body>
</html>