
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

        // Aggiorna grafico accessi giornalieri con stile minimal
        function updateDailyChart(data) {
            const ctx = document.getElementById('dailyAccesses').getContext('2d');

            if (charts.dailyChart) {
                charts.dailyChart.destroy();
            }

            // Calcola le metriche settimanali
            const sum = data.data.reduce((a, b) => a + b, 0);
            const avg = Math.round(sum / data.data.length);
            const max = Math.max(...data.data);
            const maxIndex = data.data.indexOf(max);

            // Aggiorna le metriche visualizzate
            document.getElementById('weeklyAvg').textContent = avg;
            document.getElementById('weeklyTotal').textContent = sum;
            document.getElementById('weeklyPeak').textContent = data.labels[maxIndex];

            // Colori per il grafico - usando un gradiente più sottile e moderno
            const gradientFill = ctx.createLinearGradient(0, 0, 0, 300);
            gradientFill.addColorStop(0, 'rgba(59, 130, 246, 0.2)');
            gradientFill.addColorStop(1, 'rgba(59, 130, 246, 0.02)');

            // Configurazione avanzata con stile minimal
            charts.dailyChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Active Users',
                        data: data.data,
                        backgroundColor: data.labels.map((day, index) => {
                            // Il giorno con più attività ha un colore diverso
                            if (index === maxIndex) {
                                return '#3b82f6'; // Blu per il giorno di picco
                            }
                            // Il giorno corrente (ultimo) ha un'opacità diversa
                            if (index === data.labels.length - 1) {
                                return 'rgba(59, 130, 246, 0.65)';
                            }
                            return 'rgba(59, 130, 246, 0.35)';
                        }),
                        borderWidth: 0,
                        borderRadius: 6,
                        barThickness: 32,
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
                            enabled: true,
                            mode: 'index',
                            intersect: false,
                            displayColors: false,
                            callbacks: {
                                title: function(tooltipItems) {
                                    const date = new Date();
                                    date.setDate(date.getDate() - (6 - tooltipItems[0].dataIndex));
                                    return `${tooltipItems[0].label} (${date.getDate()}/${date.getMonth()+1})`;
                                },
                                label: function(context) {
                                    return `${context.raw} active users`;
                                },
                                afterLabel: function(context) {
                                    if (context.dataIndex === maxIndex) {
                                        return 'Peak day of the week';
                                    }
                                    return '';
                                }
                            },
                            padding: 10,
                            bodySpacing: 5,
                            titleFont: {
                                size: 14
                            },
                            bodyFont: {
                                size: 13
                            },
                            titleMarginBottom: 6
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                display: true,
                                color: 'rgba(0, 0, 0, 0.04)',
                                drawBorder: false
                            },
                            ticks: {
                                stepSize: 5,
                                precision: 0,
                                font: {
                                    size: 11
                                },
                                padding: 10
                            },
                            border: {
                                display: false
                            }
                        },
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                font: {
                                    size: 12,
                                    weight: function(context) {
                                        // Oggi in grassetto
                                        return context.index === data.labels.length - 1 ? '600' : '400';
                                    }
                                },
                                padding: 5,
                                color: function(context) {
                                    // Oggi con un colore diverso
                                    return context.index === data.labels.length - 1 ? '#3b82f6' : '#6b7280';
                                }
                            },
                            border: {
                                display: false
                            }
                        }
                    },
                    layout: {
                        padding: {
                            top: 20,
                            right: 20,
                            left: 20,
                            bottom: 10
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

        // Aggiorna eventi recenti con migliore categorizzazione
        function updateEvents(events) {
            const list = document.getElementById('lastEvents');
            list.innerHTML = '';

            events.forEach(event => {
                const li = document.createElement('li');

                // Determina l'icona in base al contenuto dell'evento (più dettagliata)
                let icon = 'fa-circle-info';
                let eventClass = '';

                // Errori e warning
                if (event.toLowerCase().includes('error')) {
                    icon = 'fa-circle-exclamation';
                    eventClass = 'error-event';
                } else if (event.toLowerCase().includes('warn')) {
                    icon = 'fa-triangle-exclamation';
                    eventClass = 'warning-event';
                } else if (event.toLowerCase().includes('vulnerabilit')) {
                    icon = 'fa-shield-halved';
                    eventClass = 'security-event';
                }
                // Login e autenticazione
                else if (event.toLowerCase().includes('login')) {
                    if (event.toLowerCase().includes('fail')) {
                        icon = 'fa-user-xmark';
                        eventClass = 'warning-event';
                    } else {
                        icon = 'fa-user-check';
                    }
                }
                // IP e sicurezza
                else if (event.toLowerCase().includes('ip') || event.toLowerCase().includes('block')) {
                    icon = 'fa-ban';
                    eventClass = 'warning-event';
                } else if (event.toLowerCase().includes('security') || event.toLowerCase().includes('scan')) {
                    icon = 'fa-shield-halved';
                    eventClass = 'security-event';
                }
                // Sistema e manutenzione
                else if (event.toLowerCase().includes('deploy') || event.toLowerCase().includes('update')) {
                    icon = 'fa-code-commit';
                    eventClass = 'update-event';
                } else if (event.toLowerCase().includes('backup')) {
                    icon = 'fa-database';
                    eventClass = 'success-event';
                } else if (event.toLowerCase().includes('maintenance')) {
                    icon = 'fa-screwdriver-wrench';
                }

                // Formattazione dell'evento, evidenziando utenti e IP se presenti
                let formattedEvent = event;

                // Evidenzia utenti (username)
                formattedEvent = formattedEvent.replace(/\b([a-z0-9_]+)\b/gi, function(match) {
                    if (match.includes('_') || match.length > 3 && !match.match(/^(error|warn|fail|user|scan|backup|true|false|null)$/i)) {
                        return `<span class="event-user">${match}</span>`;
                    }
                    return match;
                });

                // Evidenzia indirizzi IP
                formattedEvent = formattedEvent.replace(/\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b/g,
                    '<span class="event-ip">$&</span>');

                // Evidenzia numeri di versione
                formattedEvent = formattedEvent.replace(/\bv\d+(\.\d+)*\b/g,
                    '<span class="event-version">$&</span>');

                // Evidenzia percentuali
                formattedEvent = formattedEvent.replace(/(\d+)%/g,
                    '$1<span class="event-percent">%</span>');

                li.className = eventClass;
                li.innerHTML = `<i class="fa-solid ${icon}"></i>${formattedEvent}`;
                list.appendChild(li);
            });
        }

        // Aggiorna stato servizi con stile più accattivante
    function updateServices(services) {
        const list = document.getElementById('serviceStatus');
        list.innerHTML = '';

        // Predefinisci i servizi che vuoi mostrare con i loro stati
        const serviceData = [
            {
                name: 'Server',
                icon: 'fa-server',
                status: 'online', // online, warning, offline
                latency: 20 // in ms, null se non disponibile
            },
            {
                name: 'Database',
                icon: 'fa-database',
                status: 'online',
                latency: 45 // Valore reale dal backend
            },
            {
                name: 'Storage',
                icon: 'fa-hard-drive',
                status: 'online',
                latency: null
            },
            {
                name: 'Galileo AI',
                icon: 'fa-robot',
                status: 'offline',
                latency: null
            },
            {
                name: 'Email',
                icon: 'fa-envelope',
                status: 'offline',
                latency: null
            }
        ];

        // Aggiorna la latenza del Database con il valore reale
        const dbService = services.find(s => s.includes('Database'));
        if (dbService) {
            const latencyMatch = dbService.match(/\((\d+)ms\)/);
            if (latencyMatch) {
                serviceData[1].latency = parseInt(latencyMatch[1]);

                // Aggiorna lo stato del DB in base alla latenza
                if (serviceData[1].latency > 100) {
                    serviceData[1].status = 'warning';
                }
            }
        }

        // Crea gli elementi dell'interfaccia per ogni servizio
        serviceData.forEach(service => {
            const serviceItem = document.createElement('li');
            serviceItem.className = 'service-item';

            // Prepara il componente della latenza se disponibile
            let latencyHTML = '';
            if (service.latency !== null) {
                let latencyClass = 'good';

                if (service.latency > 100) {
                    latencyClass = 'high';
                } else if (service.latency > 50) {
                    latencyClass = 'medium';
                }

                latencyHTML = `<div class="service-latency ${latencyClass}">
                    <span class="latency-value">${service.latency}</span>
                    <span class="latency-unit">ms</span>
                </div>`;
            }

            // Determina il testo dello stato
            let statusText = 'ONLINE';
            if (service.status === 'warning') statusText = 'WARNING';
            if (service.status === 'offline') statusText = 'OFFLINE';

            serviceItem.innerHTML = `
                <div class="service-icon">
                    <i class="fas ${service.icon}"></i>
                </div>
                <div class="service-info">
                    <div class="service-name">${service.name}</div>
                </div>
                <div class="service-status-container">
                    <div class="service-status ${service.status}">${statusText}</div>
                    ${latencyHTML}
                </div>
            `;

            list.appendChild(serviceItem);
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

        // Aggiorna la data e l'ora attuali
        setInterval(function() {
            document.querySelector('.timestamp').innerHTML =
                `<i class="fa-regular fa-clock"></i> ${new Date().toISOString().replace('T', ' ').substring(0, 19)}`;
        }, 60000); // Aggiorna ogni minuto
