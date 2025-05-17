document.addEventListener('DOMContentLoaded', function() {
    // Store open files and active file
    const openFiles = new Set();
    let activeFile = null;

    // Elements
    const logFiles = document.querySelectorAll('.log-file');
    const editorTabs = document.getElementById('editor-tabs');
    const editorContent = document.getElementById('editor-content');
    const refreshBtn = document.getElementById('refresh-logs');

    // Add click event to all log files in sidebar
    logFiles.forEach(file => {
        file.addEventListener('click', function() {
            const fileName = this.getAttribute('data-file');
            openFile(fileName);
        });
    });

    // Function to open a file
    function openFile(fileName) {
        // If file is already open, just activate its tab
        if (openFiles.has(fileName)) {
            activateFile(fileName);
            return;
        }

        // Show loading state
        refreshBtn.classList.add('spinning');

        // Fetch file content
        fetch(`../utils/get_log.php?file=${encodeURIComponent(fileName)}`)
            .then(response => {
                if (!response.ok) throw new Error('File not found');
                return response.text();
            })
            .then(content => {
                // Add to open files set
                openFiles.add(fileName);
                
                // Create new tab
                createTab(fileName);
                
                // Create editor pane
                createEditorPane(fileName, content);
                
                // Activate this file
                activateFile(fileName);
                
                // Remove loading state
                refreshBtn.classList.remove('spinning');
            })
            .catch(error => {
                console.error('Error loading file:', error);
                alert('Error loading file: ' + error.message);
                refreshBtn.classList.remove('spinning');
            });
    }

    // Create a new tab for a file
    function createTab(fileName) {
        const tab = document.createElement('div');
        tab.className = 'tab';
        tab.setAttribute('data-file', fileName);
        tab.innerHTML = `
            <span class="tab-name">${fileName}</span>
            <span class="close" title="Close"><i class="fas fa-times"></i></span>
        `;
        
        // Add click event to activate this tab
        tab.addEventListener('click', function(e) {
            if (!e.target.closest('.close')) {
                activateFile(fileName);
            }
        });
        
        // Add click event to close button
        tab.querySelector('.close').addEventListener('click', function(e) {
            e.stopPropagation();
            closeFile(fileName);
        });
        
        editorTabs.appendChild(tab);
    }

    // Create editor pane for file content
    function createEditorPane(fileName, content) {
        const pane = document.createElement('div');
        pane.className = 'editor-pane';
        pane.setAttribute('data-file', fileName);
        
        const terminal = document.createElement('div');
        terminal.className = 'terminal-log';
        
        // Format log content with color highlighting
        const formattedContent = formatLogContent(content);
        terminal.innerHTML = `<pre>${formattedContent}</pre>`;
        
        pane.appendChild(terminal);
        editorContent.appendChild(pane);
    }

    // Format log content with color highlighting
    function formatLogContent(content) {
        if (!content) return '';
        
        // Apply color formatting to log lines
        return content
            .replace(/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/g, '<span class="log-date">[$1]</span>')
            .replace(/\[INFO\]/g, '<span class="log-info">[INFO]</span>')
            .replace(/\[WARNING\]/g, '<span class="log-warning">[WARNING]</span>')
            .replace(/\[ERROR\]/g, '<span class="log-error">[ERROR]</span>')
            .replace(/\[DEBUG\]/g, '<span class="log-debug">[DEBUG]</span>')
            .replace(/\[CRITICAL\]/g, '<span class="log-critical">[CRITICAL]</span>');
    }

    // Activate a file tab and content
    function activateFile(fileName) {
        // Remove active class from all tabs and panes
        document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
        document.querySelectorAll('.editor-pane').forEach(pane => pane.classList.remove('active'));
        document.querySelectorAll('.log-file').forEach(file => file.classList.remove('active'));
        
        // Hide welcome pane if it exists
        const welcomePane = document.querySelector('.welcome-pane');
        if (welcomePane) welcomePane.style.display = 'none';
        
        // Add active class to selected tab, pane and sidebar item
        document.querySelector(`.tab[data-file="${fileName}"]`).classList.add('active');
        document.querySelector(`.editor-pane[data-file="${fileName}"]`).classList.add('active');
        document.querySelector(`.log-file[data-file="${fileName}"]`).classList.add('active');
        
        activeFile = fileName;
    }

    // Close a file
    function closeFile(fileName) {
        // Remove tab and pane
        const tab = document.querySelector(`.tab[data-file="${fileName}"]`);
        const pane = document.querySelector(`.editor-pane[data-file="${fileName}"]`);
        
        if (tab) tab.remove();
        if (pane) pane.remove();
        
        // Remove from open files
        openFiles.delete(fileName);
        
        // If this was the active file, activate another one or show welcome pane
        if (activeFile === fileName) {
            if (openFiles.size > 0) {
                activateFile(Array.from(openFiles)[0]);
            } else {
                activeFile = null;
                const welcomePane = document.querySelector('.welcome-pane');
                if (welcomePane) welcomePane.style.display = 'flex';
            }
        }
    }

    // Refresh logs button
    refreshBtn.addEventListener('click', function() {
        if (activeFile) {
            const fileName = activeFile;
            refreshBtn.classList.add('spinning');
            
            fetch(`../utils/get_log.php?file=${encodeURIComponent(fileName)}`)
                .then(response => response.text())
                .then(content => {
                    const pane = document.querySelector(`.editor-pane[data-file="${fileName}"] .terminal-log pre`);
                    if (pane) {
                        pane.innerHTML = formatLogContent(content);
                    }
                    refreshBtn.classList.remove('spinning');
                })
                .catch(error => {
                    console.error('Error refreshing file:', error);
                    refreshBtn.classList.remove('spinning');
                });
        }
    });
});