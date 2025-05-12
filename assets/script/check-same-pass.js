document.addEventListener('DOMContentLoaded', function() {
    const password = document.querySelector('input[name="password"]');
    const confirmPassword = document.querySelector('input[name="confirm_password"]');
    const form = document.querySelector('form');

    form.addEventListener('submit', function(event) {
        // Clear previous error messages
        const existingError = document.querySelector('.password-error');
        if (existingError) {
            existingError.remove();
        }

        // Check if passwords match
        if (password.value !== confirmPassword.value) {
            event.preventDefault();

            // Show error message
            const errorMessage = document.createElement('p');
            errorMessage.classList.add('password-error');
            errorMessage.style.color = 'red';
            errorMessage.textContent = 'Passwords do not match!';

            // Insert after confirm password field
            confirmPassword.parentNode.insertBefore(errorMessage, confirmPassword.nextSibling);

            // Change border color to indicate error
            confirmPassword.style.borderColor = 'var(--danger)';
        }
    });

    // Real-time validation
    confirmPassword.addEventListener('input', function() {
        const existingError = document.querySelector('.password-error');

        if (password.value !== confirmPassword.value) {
            confirmPassword.style.borderColor = 'var(--danger)';

            // Only add error message if it doesn't exist
            if (!existingError) {
                const errorMessage = document.createElement('p');
                errorMessage.classList.add('password-error');
                errorMessage.style.color = 'red';
                errorMessage.textContent = 'Passwords do not match!';
                confirmPassword.parentNode.insertBefore(errorMessage, confirmPassword.nextSibling);
            }
        } else {
            confirmPassword.style.borderColor = 'var(--success)';

            // Remove error message if passwords match
            if (existingError) {
                existingError.remove();
            }
        }
    });
});