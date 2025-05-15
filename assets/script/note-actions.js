document.addEventListener('DOMContentLoaded', function() {
    // Delete note confirmation popup
    const popup = document.getElementById('confirm-popup');
    const deleteButtons = document.querySelectorAll('.delete-note-btn');
    let noteIdToDelete = null;

    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            noteIdToDelete = this.dataset.id;

            // Position popup near the button
            const rect = this.getBoundingClientRect();
            const popupHeight = 80; // Estimated popup height

            popup.style.top = (window.scrollY + rect.top - (popupHeight/2) + rect.height/2) + 'px';
            popup.style.left = (window.scrollX + rect.right + 10) + 'px';
            popup.style.display = 'block';
        });
    });

    document.getElementById('confirm-yes').addEventListener('click', function() {
        if (noteIdToDelete) {
            window.location.href = `delete_note.php?id=${noteIdToDelete}`;
        }
        popup.style.display = 'none';
    });

    document.getElementById('confirm-no').addEventListener('click', function() {
        popup.style.display = 'none';
    });

    // Close popup if clicking outside
    document.addEventListener('click', function(e) {
        if (popup.style.display === 'block' &&
            !popup.contains(e.target) &&
            !e.target.closest('.delete-note-btn')) {
            popup.style.display = 'none';
        }
    });

    // Premium feature explanation
    const premiumFeatures = document.querySelectorAll('.premium-feature');
    premiumFeatures.forEach(feature => {
        const tooltip = document.createElement('div');
        tooltip.className = 'premium-tooltip';
        tooltip.innerHTML = '<i class="fas fa-crown"></i> Subscribe to Premium to unlock this feature';

        feature.addEventListener('mouseenter', function() {
            feature.appendChild(tooltip);
            setTimeout(() => {
                tooltip.style.opacity = '1';
            }, 50);
        });

        feature.addEventListener('mouseleave', function() {
            tooltip.style.opacity = '0';
            setTimeout(() => {
                if (tooltip.parentElement === feature) {
                    feature.removeChild(tooltip);
                }
            }, 300);
        });
    });

    // Share dropdown handling
    const shareOption = document.getElementById('share-option');
    if (shareOption) {
        const permissionOptions = document.getElementById('permission-options');

        shareOption.addEventListener('change', function() {
            if (this.checked) {
                permissionOptions.style.display = 'block';
            } else {
                permissionOptions.style.display = 'none';
            }
        });

        // Initialize on load
        if (shareOption.checked) {
            permissionOptions.style.display = 'block';
        } else {
            permissionOptions.style.display = 'none';
        }
    }

    // Note detail modal
    const noteContents = document.querySelectorAll('.note-content');

    // Only create modal if there are notes on the page
    if (noteContents.length > 0) {
        const modal = document.createElement('div');
        modal.className = 'note-modal';
        modal.innerHTML = `
            <div class="note-modal-content">
                <div class="note-modal-header">
                    <h3></h3>
                    <button class="note-modal-close">&times;</button>
                </div>
                <div class="note-modal-body">
                    <p></p>
                </div>
                <div class="note-modal-footer">
                    <span class="priority"></span>
                    <span class="shared-badge"></span>
                    <span class="date"></span>
                </div>
            </div>
        `;
        document.body.appendChild(modal);

        const modalTitle = modal.querySelector('.note-modal-header h3');
        const modalContent = modal.querySelector('.note-modal-body p');
        const modalPriority = modal.querySelector('.note-modal-footer .priority');
        const modalSharedBadge = modal.querySelector('.note-modal-footer .shared-badge');
        const modalDate = modal.querySelector('.note-modal-footer .date');
        const closeButton = modal.querySelector('.note-modal-close');

        noteContents.forEach(content => {
            content.addEventListener('click', function(e) {
                e.preventDefault();

                const noteElement = this.closest('.note');
                const title = noteElement.querySelector('.note-header h3').textContent;
                const fullContent = this.querySelector('p').getAttribute('data-full-content') || this.querySelector('p').textContent;
                const priorityElement = noteElement.querySelector('.note-footer .priority');
                const date = noteElement.querySelector('.note-footer .date').textContent;
                const sharedBadge = noteElement.querySelector('.note-footer .shared-badge');

                // Get priority class from the note element
                const priorityClass = noteElement.className.split(' ').find(cls => cls.startsWith('priority-'));

                modalTitle.textContent = title;
                modalContent.textContent = fullContent;

                // Set priority text and styling
                modalPriority.textContent = priorityElement.textContent;
                modalPriority.className = 'priority'; // Reset classes

                // Apply the proper priority class and styling
                if (priorityClass) {
                    // Add class based on priority
                    modalPriority.classList.add(priorityClass);

                    // Apply color styles directly from the original element
                    const computedStyle = window.getComputedStyle(priorityElement);
                    modalPriority.style.backgroundColor = computedStyle.backgroundColor;
                    modalPriority.style.color = computedStyle.color;
                }

                modalDate.textContent = date;

                // Handle shared badge if exists and is visible
                if (sharedBadge && sharedBadge.style.visibility !== 'hidden') {
                    modalSharedBadge.innerHTML = sharedBadge.innerHTML;
                    modalSharedBadge.style.display = 'flex';
                } else {
                    modalSharedBadge.style.display = 'none';
                }

                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            });
        });

        closeButton.addEventListener('click', function() {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        });

        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    }

    // Shared notes tab functionality
    const sharedTabs = document.querySelectorAll('.shared-tab');
    if (sharedTabs.length) {
        sharedTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs
                document.querySelectorAll('.shared-tab').forEach(t => t.classList.remove('active'));
                // Add active class to clicked tab
                this.classList.add('active');

                // Hide all tab content
                document.querySelectorAll('.shared-tab-content').forEach(content => {
                    content.classList.remove('active');
                });

                // Show corresponding tab content
                const targetId = this.getAttribute('data-target');
                document.getElementById(targetId).classList.add('active');
            });
        });
    }
});