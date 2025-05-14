// Variabili per l'ordinamento ciclico delle note
let notesSortState = 'desc'; // Stato iniziale: decrescente
let notesSortCycle = 0; // Contatore ciclo

// Gestione ordinamento ciclico note
function setupNotesSort() {
    const notesHeader = document.querySelector('th a[href*="sort=notes_count"]');
    if (!notesHeader) return;

    // Salva l'href originale per la modalità fallback
    const originalHref = notesHeader.getAttribute('href');

    notesHeader.addEventListener('click', function(e) {
        e.preventDefault(); // Impedisce il comportamento predefinito del link

        const rows = Array.from(document.querySelectorAll('#tableBody tr'));

        // Ordina le righe in base al valore delle note
        rows.sort((a, b) => {
            const notesA = parseInt(a.cells[5].textContent) || 0;
            const notesB = parseInt(b.cells[5].textContent) || 0;

            return notesSortState === 'desc' ? notesB - notesA : notesA - notesB;
        });

        // Applica l'ordinamento alla tabella DOM
        const tbody = document.querySelector('#tableBody');
        rows.forEach(row => tbody.appendChild(row));

        // Aggiorna lo stato dell'ordinamento per il prossimo click
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

        // Aggiorna l'indicatore visivo
        document.querySelectorAll('.lucide-chevrons-up-down').forEach(function(icon) {
            icon.classList.remove('active');
        });
        notesHeader.querySelector('.lucide-chevrons-up-down').classList.add('active');

        console.log('Ordinamento note applicato:', notesSortState);
    });
}

// Inizializza l'ordinamento delle note quando il documento è pronto
document.addEventListener('DOMContentLoaded', function() {
    // Inizializza lo stato di ordinamento in base all'URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('sort') === 'notes_count') {
        notesSortState = urlParams.get('order') || 'desc';
    }

    setupNotesSort();
    console.log('Inizializzazione ordinamento note completata');
});