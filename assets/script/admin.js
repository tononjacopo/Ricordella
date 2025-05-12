 // Aggiorna la tabella senza refresh pagina (ajax-like)
    document.getElementById('refreshTable').addEventListener('click', function() {
        let btn = this;
        btn.classList.add('spinning');
        fetch(window.location.href, { cache: "reload" })
            .then(response => response.text())
            .then(html => {
                let parser = new DOMParser();
                let doc = parser.parseFromString(html, "text/html");
                let newBody = doc.querySelector("#tableBody");
                document.querySelector("#tableBody").innerHTML = newBody.innerHTML;
                setTimeout(()=>btn.classList.remove('spinning'), 500);

                // Re-init delete handlers
                setupDeleteHandlers();
            });
    });

    function clearInput(btn) {
        const input = btn.previousElementSibling;
        input.value = '';
        input.focus();
    }

    // Gestione popup conferma eliminazione
    function setupDeleteHandlers() {
        const popup = document.getElementById('confirm-popup');
        const deleteButtons = document.querySelectorAll('.delete-user');
        let currentUserId = null;

        deleteButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                currentUserId = this.dataset.id;

                // Posizionamento del popup vicino al bottone
                const rect = this.getBoundingClientRect();
                const popupHeight = 80; // Altezza stimata del popup

                popup.style.top = (window.scrollY + rect.top - (popupHeight/2) + rect.height/2) + 'px';
                popup.style.left = (window.scrollX + rect.right + 10) + 'px';
                popup.style.display = 'block';
            });
        });

        document.getElementById('confirm-yes').addEventListener('click', function() {
            if (currentUserId) {
                window.location.href = `delete_user.php?id=${currentUserId}`;
            }
            popup.style.display = 'none';
        });

        document.getElementById('confirm-no').addEventListener('click', function() {
            popup.style.display = 'none';
        });

        // Chiudi il popup se si clicca al di fuori
        document.addEventListener('click', function(e) {
            if (popup.style.display === 'block' &&
                !popup.contains(e.target) &&
                !e.target.closest('.delete-user')) {
                popup.style.display = 'none';
            }
        });
    }

    // Inizializza i gestori per il delete
    document.addEventListener('DOMContentLoaded', setupDeleteHandlers);


    // Gestione pulsanti di cancellazione input
    function setupClearButtons() {
        document.querySelectorAll('.input-clearable input, .input-clearable select').forEach(function(input) {
            const clearBtn = input.nextElementSibling;

            // Imposta lo stato iniziale
            if (input.value) {
                clearBtn.style.display = 'block';
            } else {
                clearBtn.style.display = 'none';
            }

            // Aggiungi event listener per cambiamenti all'input
            input.addEventListener('input', function() {
                if (this.value) {
                    clearBtn.style.display = 'block';
                } else {
                    clearBtn.style.display = 'none';
                }
            });

            // Per i selects
            if (input.tagName === 'SELECT') {
                input.addEventListener('change', function() {
                    if (this.value) {
                        clearBtn.style.display = 'block';
                    } else {
                        clearBtn.style.display = 'none';
                    }
                });
            }
        });
    }