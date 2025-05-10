document.addEventListener('DOMContentLoaded', function() {
    const passwordField = document.querySelector('input[name="password"]');
    const confirmField = document.querySelector('input[name="confirm_password"]');
    const submitButton = document.querySelector('button[type="submit"]');

    function checkPasswordMatch() {
        if (passwordField.value !== confirmField.value) {
            confirmField.setCustomValidity("Passwords do not match");
        } else {
            confirmField.setCustomValidity("");
        }
    }

    if (passwordField && confirmField) {
        passwordField.addEventListener('change', checkPasswordMatch);
        confirmField.addEventListener('keyup', checkPasswordMatch);
    }
});