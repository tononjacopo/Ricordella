let notesCycleOffset = 0;

document.querySelector('[data-sort="notes_count"]').addEventListener('click', () => {
    const rows = Array.from(document.querySelectorAll('tbody tr'));
    const maxNotes = Math.max(...rows.map(row => parseInt(row.dataset.notesCount, 10) || 0));

    // Funzione per il valore "ciclico"
    function cycleValue(n) {
        return (n - notesCycleOffset + maxNotes + 1) % (maxNotes + 1);
    }

    rows.sort((a, b) => {
        const aVal = parseInt(a.dataset.notesCount, 10) || 0;
        const bVal = parseInt(b.dataset.notesCount, 10) || 0;
        return cycleValue(aVal) - cycleValue(bVal);
    });

    const tbody = document.querySelector('tbody');
    rows.forEach(row => tbody.appendChild(row));

    notesCycleOffset = (notesCycleOffset + 1) % (maxNotes + 1); // Avanza l'offset ciclico
});