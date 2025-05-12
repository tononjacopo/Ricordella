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
    <link rel="stylesheet" href="../assets/style/admin.css">
    <link rel="stylesheet" href="../assets/style/font-general.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5em;
        }
        .refresh-btn {
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 50%;
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            transition: all 0.2s;
        }
        .refresh-btn:hover {
            background: var(--primary-dark);
            transform: rotate(15deg);
        }
        .refresh-btn i {
            font-size: 18px;
        }
        .refresh-btn.spinning i {
            animation: spin 1s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        .kpi-cards { display: flex; gap: 1.2em; margin-bottom: 2.5em; flex-wrap: wrap;}
        .kpi-card {
            background: #fff; border-radius: 1.2em; box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            padding: 1.2em 1.3em; min-width: 180px; flex:1; text-align:left;
            display:flex; flex-direction:column; align-items:flex-start;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.12);
        }
        .kpi-card .label { font-size:.98em; color: #6b7280;}
        .kpi-card .value { font-size:2.1em; font-weight:bold;}
        .kpi-card.blue .value { color: #3b82f6;}
        .kpi-card.green .value { color: #16a34a;}
        .kpi-card.red .value { color: #ef4444;}
        .kpi-card.yellow .value { color: #eab308;}
        .kpi-card.purple .value { color: #8b5cf6;}

        .charts-row { display: flex; gap:2em; margin-bottom:2em; flex-wrap:wrap;}
        .chart-box {
            background: #fff; border-radius: 1.2em; box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            padding: 1.5em; flex:1; min-width:360px; min-height:340px;
            display:flex; flex-direction:column; align-items:center; position: relative;
        }
        .chart-box h3 {
            font-size: 1.1em;
            color: #4b5563;
            margin: 0 0 1em 0;
            align-self: flex-start;
        }
        .chart-container {
            position: relative;
            width: 100%;
            height: 300px;
        }

        .total-users {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            font-size: 1.2em;
            color: #6b7280;
            pointer-events: none;
            z-index: 5;
        }
        .total-users .count {
            display: block;
            font-size: 1.6em;
            font-weight: bold;
            color: #4b5563;
        }

        .percentage-bar {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            width: 70%;
            height: 4px;
            background: #e5e7eb;
            border-radius: 2px;
            overflow: hidden;
            z-index: 5;
        }
        .percentage-segment {
            height: 100%;
            float: left;
        }

        .admin-status-box {
            flex: 0 0 300px;
            background: #fff;
            border-radius: 1.2em;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            padding: 1.5em;
        }
        .admin-status-box h3 {
            font-size: 1.1em;
            color: #4b5563;
            margin: 0 0 1em 0;
        }
        .admin-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .admin-list li {
            padding: 0.8em 0;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .admin-list li:last-child {
            border-bottom: none;
        }
        .admin-name {
            font-weight: 500;
        }
        .admin-info {
            font-size: 0.9em;
            color: #6b7280;
        }
        .admin-status {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
            display: inline-block;
        }
        .admin-status.green { background: #16a34a; }
        .admin-status.yellow { background: #eab308; }
        .admin-status.red { background: #ef4444; }

        .events-row { display: flex; gap:2em; margin-top:2em; flex-wrap: wrap;}
        .event-box {
            background: #fff; border-radius: 1.2em; box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            padding: 1.2em; flex:1; min-width:260px;
        }
        .event-box h3 { font-size:1.15em; margin-bottom:.7em; color: #374151;}
        .event-box ul {
            font-size:.98em;
            color: #555;
            list-style-type: none;
            padding: 0;
        }
        .event-box li {
            margin-bottom: .35em;
            padding: 0.5em 0.7em;
            border-radius: 6px;
            transition: background-color 0.2s;
        }
        .event-box li:hover {
            background: #f9fafb;
        }
        .event-box li i {
            margin-right: 8px;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .charts-row {
                flex-direction: column;
            }
            .chart-box, .admin-status-box {
                min-width: 100%;
            }
        }

        /* Stili per la latenza del DB */
        .latency {
            display: inline-block;
            font-family: monospace;
            padding: 0 6px;
            border-radius: 4px;
            margin-left: 4px;
            font-weight: 600;
        }
        .latency.good { background: rgba(22, 163, 74, 0.1); color: #16a34a; }
        .latency.medium { background: rgba(234, 179, 8, 0.1); color: #eab308; }
        .latency.high { background: rgba(239, 68, 68, 0.1); color: #ef4444; }

        /* Settings Panel */
        .settings-panel {
            display: none;
            position: absolute;
            top: 10px;
            right: 10px;
            background: white;
            border-radius: 6px;
            padding: 10px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
            z-index: 10;
            width: 200px;
        }
        .settings-toggle {
            position: absolute;
            top: 10px;
            right: 10px;
            background: none;
            border: none;
            color: #6b7280;
            cursor: pointer;
            padding: 5px;
            z-index: 11;
        }
        .settings-toggle:hover {
            color: #1f2937;
        }
        .settings-panel label {
            display: block;
            margin-bottom: 5px;
            font-size: 12px;
            color: #6b7280;
        }
        .settings-panel input {
            width: 100%;
            padding: 6px;
            margin-bottom: 10px;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            font-size: 13px;
        }
        .settings-panel button {
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 4px;
            padding: 6px 12px;
            font-size: 13px;
            cursor: pointer;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--primary); }
        ::-webkit-scrollbar-thumb:hover { background: var(--secondary); }
        ::-webkit-scrollbar-thumb:active { background: var(--secondary); }
        html { scrollbar-color: var(--primary) transparent; scrollbar-width: thin; }
    </style>
</head>
<body>
    <header>
        <div class="logo">Ricordella Admin</div>
        <nav>
            <a href="dashboard.php">Users</a>
            <a href="logs.php">Logs</a>
            <a href="stats.php" class="active">Stats</a>
        </nav>
        <div class="user-info">
            <span>Admin: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="../logout.php" class="logout">Logout</a>
        </div>
    </header>
    <main>
        <div class="page-header">
            <h1>Data Analytics</h1>
            <button id="refreshStats" class="refresh-btn" title="Refresh Statistics">
                <i class="fa-solid fa-arrows-rotate"></i>
            </button>
        </div>

        <div id="kpi" class="kpi-cards"></div>

        <div class="charts-row">
            <div class="chart-box">
                <h3>Weekly User Activity</h3>
                <div class="chart-container">
                    <canvas id="dailyAccesses"></canvas>
                </div>
            </div>
            <div class="admin-status-box">
                <h3>Admin Status</h3>
                <ul class="admin-list" id="adminList">
                    <!-- Loaded dynamically -->
                </ul>
            </div>
        </div>

        <div class="charts-row">
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
                <div class="percentage-bar" id="percentageBar"></div>
            </div>
            <div class="chart-box">
                <h3>System Performance</h3>
                <div class="chart-container">
                    <canvas id="performanceChart"></canvas>
                </div>
            </div>
        </div>

        <div class="events-row">
            <div class="event-box">
                <h3>Last Events</h3>
                <ul id="lastEvents"></ul>
            </div>
            <div class="event-box">
                <h3>Services Status</h3>
                <ul id="serviceStatus"></ul>
            </div>
        </div>
    </main>
    <footer>
        <p>&copy; <?php echo date('Y'); ?> Ricordella - Admin Panel</p>
    </footer>
    <script>
        // Configurazioni globali chart.js
        Chart.defaults.font.family = 'system-ui, -apple-system, sans-serif';
        Chart.defaults.color = '#6b7280';
        Chart.defaults.plugins.tooltip.backgroundColor = '#1e293b';
        Chart.defaults.plugins.legend.position = 'top';

        // Inizializzazione
        const refreshBtn = document.getElementById('refreshStats');
        let charts = {};

        // Etichette personalizzate per il grafico di distribuzione utenti
        const userLabels = {
            regular: 'Regular Users',
            premium: 'Premium Users',
            admin: 'Administrators'
        };

        // Aggiornamento dati
        function fetchStats() {
            refreshBtn.classList.add('spinning');

            fetch('stats_data.php?v=' + new Date().getTime())
                .then(response => response.json())
                .then(data => {
                    updateKPI(data.kpi);
                    updateDailyChart(data.daily_accesses);
                    updateUserRolesChart(data.user_roles);
                    updatePerformanceChart(data.performance);
                    updateAdminStatus(data.admins);
                    updateEvents(data.events);
                    updateServices(data.services);

                    setTimeout(() => {
                        refreshBtn.classList.remove('spinning');
                    }, 500);
                })
                .catch(error => {
                    console.error('Error loading stats:', error);
                    refreshBtn.classList.remove('spinning');
                });
        }

        // Aggiorna KPI cards
        function updateKPI(kpi) {
            document.getElementById('kpi').innerHTML = `
                <div class="kpi-card purple">
                    <div class="label">Premium Subscriptions (7d)</div>
                    <div class="value">${kpi.new_premium_users}</div>
                </div>
                <div class="kpi-card green">
                    <div class="label">Registrations (30d)</div>
                    <div class="value">${kpi.registrations_30d}</div>
                </div>
                <div class="kpi-card red">
                    <div class="label">System Errors</div>
                    <div class="value">${kpi.errors}</div>
                </div>
                <div class="kpi-card yellow">
                    <div class="label">Failed Access</div>
                    <div class="value">${kpi.failed_logins}</div>
                </div>
            `;
        }

        // Aggiorna grafico accessi giornalieri
        function updateDailyChart(data) {
            const ctx = document.getElementById('dailyAccesses').getContext('2d');

            if (charts.dailyChart) {
                charts.dailyChart.destroy();
            }

            // Colori per il grafico
            const gradientFill = ctx.createLinearGradient(0, 0, 0, 300);
            gradientFill.addColorStop(0, 'rgba(59, 130, 246, 0.6)');
            gradientFill.addColorStop(1, 'rgba(59, 130, 246, 0.1)');

            // Configurazione avanzata
            charts.dailyChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Daily User Activity',
                        data: data.data,
                        borderColor: '#3b82f6',
                        backgroundColor: gradientFill,
                        borderWidth: 2,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#3b82f6',
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            displayColors: false,
                            callbacks: {
                                title: function(tooltipItems) {
                                    return tooltipItems[0].label;
                                },
                                label: function(context) {
                                    return `${context.raw} users`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 2,
                                precision: 0
                            },
                            grid: {
                                drawBorder: false,
                                color: '#e5e7eb'
                            }
                        },
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                font: {
                                    weight: function(context) {
                                        // Oggi in grassetto
                                        return context.index === data.labels.length - 1 ? 'bold' : 'normal';
                                    }
                                }
                            }
                        }
                    }
                }
            });
        }

        // Aggiorna grafico ruoli utenti
        function updateUserRolesChart(data) {
            const ctx = document.getElementById('userRoles').getContext('2d');

            if (charts.rolesChart) {
                charts.rolesChart.destroy();
            }

            // Colori per i diversi tipi di utente
            const colors = [
                '#3b82f6', // Regular users
                '#f59e0b', // Premium users
                '#10b981'  // Admin
            ];

            // Calcola percentuali e totale
            const total = data.data.reduce((a, b) => a + b, 0);
            document.getElementById('totalUsers').querySelector('.count').textContent = total;

            // Aggiorna la barra delle percentuali
            const percentageBar = document.getElementById('percentageBar');
            percentageBar.innerHTML = '';
            data.data.forEach((value, i) => {
                const pct = (value / total) * 100;
                const segment = document.createElement('div');
                segment.className = 'percentage-segment';
                segment.style.width = pct + '%';
                segment.style.backgroundColor = colors[i];
                percentageBar.appendChild(segment);
            });

            // Usa le etichette personalizzate o quelle predefinite
            const customLabels = [
                userLabels.regular,
                userLabels.premium,
                userLabels.admin
            ];

            charts.rolesChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: customLabels.map((label, i) => {
                        const value = data.data[i];
                        const pct = ((value / total) * 100).toFixed(1);
                        return `${label} (${pct}%)`;
                    }),
                    datasets: [{
                        data: data.data,
                        backgroundColor: colors,
                        borderColor: '#ffffff',
                        borderWidth: 2,
                        hoverOffset: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                padding: 10,
                                usePointStyle: true,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    const value = context.raw;
                                    const pct = ((value / total) * 100).toFixed(1);
                                    // Estrae il nome senza la percentuale
                                    const label = context.label.split(' (')[0];
                                    return `${label}: ${value} (${pct}%)`;
                                }
                            }
                        }
                    },
                    elements: {
                        arc: {
                            borderWidth: 0
                        }
                    }
                }
            });
        }

        // Aggiorna grafico performance
        function updatePerformanceChart(data) {
            const ctx = document.getElementById('performanceChart').getContext('2d');

            if (charts.performanceChart) {
                charts.performanceChart.destroy();
            }

            charts.performanceChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [
                        {
                            label: 'Server',
                            data: data.server_response,
                            borderColor: '#3b82f6', // Blu
                            backgroundColor: 'rgba(59, 130, 246, 0.2)',
                            fill: false,
                            tension: 0.3,
                            borderWidth: 2
                        },
                        {
                            label: 'API',
                            data: data.api_response,
                            borderColor: '#10b981', // Verde
                            backgroundColor: 'rgba(16, 185, 129, 0.2)',
                            fill: false,
                            tension: 0.3,
                            borderWidth: 2
                        },
                        {
                            label: 'Galileo',
                            data: data.galileo_response,
                            borderColor: '#a855f7', // Fucsia/viola
                            backgroundColor: 'rgba(168, 85, 247, 0.2)',
                            fill: false,
                            tension: 0.3,
                            borderWidth: 2
                        },
                        {
                            label: 'Email',
                            data: data.email_response,
                            borderColor: '#eab308', // Giallo
                            backgroundColor: 'rgba(234, 179, 8, 0.2)',
                            fill: false,
                            tension: 0.3,
                            borderWidth: 2,
                            borderDash: [5, 5] // Linea tratteggiata per indicare servizio inattivo
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    stacked: false,
                    plugins: {
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        },
                        legend: {
                            labels: {
                                usePointStyle: true
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Response Time (ms)'
                            },
                            grid: {
                                color: function(context) {
                                    if (context.tick.value === 0) {
                                        return '#e5e7eb';
                                    }
                                    return 'rgba(0, 0, 0, 0.05)';
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        // Aggiorna stato admin
        function updateAdminStatus(admins) {
            const list = document.getElementById('adminList');
            list.innerHTML = '';

            admins.forEach(admin => {
                const li = document.createElement('li');

                // Determina il colore dello stato
                let statusClass = '';
                let statusText = '';

                if (admin.logged_today) {
                    statusClass = 'green';
                    statusText = 'Online Today';
                } else {
                    const now = new Date();
                    const isAfter5PM = now.getHours() >= 17;
                    const yesterday = new Date();
                    yesterday.setDate(yesterday.getDate() - 1);
                    const yesterdayWasNotSunday = yesterday.getDay() !== 0;

                    if (isAfter5PM || (yesterdayWasNotSunday && !admin.logged_yesterday)) {
                        statusClass = 'red';
                        statusText = isAfter5PM ? 'Missing Today' : 'Missing Yesterday';
                    } else {
                        statusClass = 'yellow';
                        statusText = 'Not Yet Today';
                    }
                }

                li.innerHTML = `
                    <div>
                        <span class="admin-name">${admin.username}</span>
                        <div class="admin-info">Last: ${admin.last_login}</div>
                    </div>
                    <div>
                        <span class="admin-status ${statusClass}"></span>${statusText}
                    </div>
                `;

                list.appendChild(li);
            });
        }

        // Aggiorna eventi recenti
        function updateEvents(events) {
            const list = document.getElementById('lastEvents');
            list.innerHTML = '';

            events.forEach(event => {
                const li = document.createElement('li');

                // Icona in base al tipo di evento
                let icon = 'fa-circle-info';
                if (event.includes('Error') || event.includes('error')) {
                    icon = 'fa-circle-exclamation';
                } else if (event.includes('Login') || event.includes('login')) {
                    icon = 'fa-user-check';
                } else if (event.includes('Deploy') || event.includes('Update')) {
                    icon = 'fa-code-commit';
                } else if (event.includes('Backup')) {
                    icon = 'fa-database';
                }

                li.innerHTML = `<i class="fa-solid ${icon}"></i>${event}`;
                list.appendChild(li);
            });
        }

        // Aggiorna stato servizi
        function updateServices(services) {
            const list = document.getElementById('serviceStatus');
            list.innerHTML = '';

            services.forEach(service => {
                const li = document.createElement('li');

                // Verifica se contiene info di latenza
                if (service.includes('DB') || service.includes('Database')) {
                    // Esempio: "✅ Database: Online (12ms)"
                    const latencyMatch = service.match(/\((\d+)ms\)/);
                    if (latencyMatch) {
                        const latency = parseInt(latencyMatch[1]);
                        let latencyClass = 'good';

                        if (latency > 100) {
                            latencyClass = 'high';
                        } else if (latency > 50) {
                            latencyClass = 'medium';
                        }

                        // Rimuovi la parte della latenza dal testo originale
                        const baseText = service.replace(/\s*\(\d+ms\)/, '');
                        li.innerHTML = `${baseText} <span class="latency ${latencyClass}">${latency}ms</span>`;
                    } else {
                        li.textContent = service;
                    }
                } else {
                    li.textContent = service;
                }

                list.appendChild(li);
            });
        }

        // Gestione pannello impostazioni
        const settingsToggle = document.querySelector('.settings-toggle');
        const settingsPanel = document.querySelector('.settings-panel');
        const applyLabelsBtn = document.getElementById('applyLabels');

        settingsToggle.addEventListener('click', function() {
            settingsPanel.style.display = settingsPanel.style.display === 'block' ? 'none' : 'block';
        });

        document.addEventListener('click', function(e) {
            if (!settingsPanel.contains(e.target) && !settingsToggle.contains(e.target)) {
                settingsPanel.style.display = 'none';
            }
        });

        applyLabelsBtn.addEventListener('click', function() {
            userLabels.regular = document.getElementById('label1').value || 'Regular Users';
            userLabels.premium = document.getElementById('label2').value || 'Premium Users';
            userLabels.admin = document.getElementById('label3').value || 'Administrators';

            // Riaggiorna il grafico con le nuove etichette
            fetchStats();
            settingsPanel.style.display = 'none';
        });

        // Eventi
        refreshBtn.addEventListener('click', fetchStats);

        // Caricamento iniziale
        fetchStats();
    </script>
</body>
</html>