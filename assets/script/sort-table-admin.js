 // Variabili per l'ordinamento ciclico delle note
    let notesSortState = 'desc'; // Stato iniziale: decrescente
    let notesSortCycle = 0; // Contatore ciclo

    // Gestione ordinamento ciclico note
    function setupNotesSort() {
        const notesHeader = document.querySelector('a[href*="sort=notes_count"]');
        if (notesHeader) {
            notesHeader.addEventListener('click', function(e) {
                e.preventDefault();

                const rows = Array.from(document.querySelectorAll('#tableBody tr'));
                //const maxNotes = Math.max(...rows.map(row => parseInt(row.cells[5].textContent) || 0));

                // Ordina le righe in base al valore ciclico delle note
                rows.sort((a, b) => {
                    const notesA = parseInt(a.cells[5].textContent) || 0;
                    const notesB = parseInt(b.cells[5].textContent) || 0;

                    // Algoritmo di ordinamento ciclico: comincia dai valori più alti poi cicla
                    return notesSortState === 'desc' ?
                        notesB - notesA :
                        notesA - notesB;
                });

                // Applica l'ordinamento
                const tbody = document.querySelector('#tableBody');
                rows.forEach(row => tbody.appendChild(row));

                // Aggiorna lo stato dell'ordinamento
                notesSortCycle++;
                if (notesSortCycle > 1) {
                    notesSortCycle = 0;
                    notesSortState = notesSortState === 'desc' ? 'asc' : 'desc';
                }

                // Aggiorna l'URL senza ricaricare la pagina
                const url = new URL(window.location);
                url.searchParams.set('sort', 'notes_count');
                url.searchParams.set('order', notesSortState);
                history.pushState({}, '', url);
            });
        }
    }

    // Inizializza tutti i gestori
    document.addEventListener('DOMContentLoaded', function() {
        setupDeleteHandlers();
        setupClearButtons();
        setupNotesSort();

        // Inizializza lo stato di ordinamento in base all'URL
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('sort') === 'notes_count') {
            notesSortState = urlParams.get('order') || 'desc';
        }
    });