
    document.addEventListener('DOMContentLoaded', function() {
        // Variables to track open tabs
        let openTabs = [];
        let activeTab = null;

        // Get DOM elements
        const logFiles = document.querySelectorAll('.log-file');
        const tabsContainer = document.getElementById('editor-tabs');
        const editorContent = document.getElementById('editor-content');
        const refreshBtn = document.getElementById('refresh-logs');

        // Add click event to log files
        logFiles.forEach(file => {
            file.addEventListener('click', function() {
                const fileName = this.dataset.file;
                openFile(fileName);

                // Update active state in sidebar
                document.querySelectorAll('.log-file').forEach(f => f.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Function to open a file
        function openFile(fileName) {
            // Check if file is already open
            if (openTabs.includes(fileName)) {
                activateTab(fileName);
                return;
            }

            // Add to open tabs
            openTabs.push(fileName);

            // Create new tab
            const tab = document.createElement('div');
            tab.className = 'tab';
            tab.dataset.file = fileName;
            tab.innerHTML = `
                <span class="tab-name">${fileName}</span>
                <span class="close">×</span>
            `;

            // Add click event to tab
            tab.addEventListener('click', function(e) {
                if (!e.target.classList.contains('close')) {
                    activateTab(fileName);
                }
            });

            // Add click event to close button
            tab.querySelector('.close').addEventListener('click', function(e) {
                e.stopPropagation();
                closeTab(fileName);
            });

            // Add tab to container
            tabsContainer.appendChild(tab);

            // Create editor pane
            const pane = document.createElement('div');
            pane.className = 'editor-pane';
            pane.dataset.file = fileName;
            pane.innerHTML = '<div class="terminal-log"><pre>Loading...</pre></div>';
            editorContent.appendChild(pane);

            // Load file content
            fetchLogContent(fileName);

            // Activate this tab
            activateTab(fileName);
        }

        // Function to activate a tab
        function activateTab(fileName) {
            // Remove active class from all tabs and panes
            document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.editor-pane').forEach(pane => pane.classList.remove('active'));

            // Add active class to selected tab and pane
            document.querySelector(`.tab[data-file="${fileName}"]`).classList.add('active');
            document.querySelector(`.editor-pane[data-file="${fileName}"]`).classList.add('active');

            // Hide welcome pane
            document.querySelector('.welcome-pane')?.remove();

            // Update active tab
            activeTab = fileName;
        }

        // Function to close a tab
        function closeTab(fileName) {
            // Remove tab and pane
            document.querySelector(`.tab[data-file="${fileName}"]`).remove();
            document.querySelector(`.editor-pane[data-file="${fileName}"]`).remove();

            // Update open tabs
            openTabs = openTabs.filter(tab => tab !== fileName);

            // Update active tab
            if (activeTab === fileName) {
                if (openTabs.length > 0) {
                    activateTab(openTabs[openTabs.length - 1]);
                } else {
                    // Show welcome pane if no tabs are open
                    const welcome = document.createElement('div');
                    welcome.className = 'welcome-pane';
                    welcome.innerHTML = `
                        <i class="fas fa-file-alt"></i>
                        <h3>Seleziona un file di log</h3>
                        <p>Clicca su un file nella barra laterale per visualizzarlo</p>
                    `;
                    editorContent.appendChild(welcome);
                    activeTab = null;

                    // Remove active class from sidebar
                    document.querySelectorAll('.log-file').forEach(f => f.classList.remove('active'));
                }
            }
        }

        // Function to fetch log content
        function fetchLogContent(fileName) {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', `get_log.php?file=${encodeURIComponent(fileName)}`);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    const logContent = highlightLog(xhr.responseText);
                    const pane = document.querySelector(`.editor-pane[data-file="${fileName}"] .terminal-log pre`);
                    if (pane) {
                        pane.innerHTML = logContent;
                    }
                } else {
                    const pane = document.querySelector(`.editor-pane[data-file="${fileName}"] .terminal-log pre`);
                    if (pane) {
                        pane.innerHTML = 'Error loading log file';
                    }
                }
            };
            xhr.send();
        }

        // Function to add syntax highlighting to log content
        function highlightLog(content) {
            if (!content) return '';

            return content
                // Date/time
                .replace(/\[([0-9\-: ]+)\]/g, '[$1]'.replace('$1', '<span class="log-date">$1</span>'))
                // Info log levels
                .replace(/\b(INFO|NOTICE)\b/g, '<span class="log-info">$1</span>')
                // Warning log levels
                .replace(/\b(WARNING|WARN)\b/g, '<span class="log-warning">$1</span>')
                // Error log levels
                .replace(/\b(ERROR|EXCEPTION|FATAL)\b/g, '<span class="log-error">$1</span>')
                // Debug log levels
                .replace(/\b(DEBUG)\b/g, '<span class="log-debug">$1</span>')
                // Critical log levels
                .replace(/\b(CRITICAL|ALERT|EMERGENCY)\b/g, '<span class="log-critical">$1</span>');
        }

        // Refresh button click event
        refreshBtn.addEventListener('click', function() {
            this.classList.add('spinning');

            // If there's an active tab, reload it
            if (activeTab) {
                fetchLogContent(activeTab);
            }

            // Remove spinning class after 1 second
            setTimeout(() => {
                this.classList.remove('spinning');
            }, 1000);
        });
    });
