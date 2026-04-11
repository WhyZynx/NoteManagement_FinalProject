document.addEventListener("DOMContentLoaded", function () {
    const loginForm = document.getElementById('loginForm');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const toggle = document.getElementById("togglePassword");

    loginForm.addEventListener('submit', function (e) {
        let isValid = true;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (emailInput.value.trim() === "") {
            showError(emailInput, 'emailError', 'Email address is required');
            isValid = false;
        } else if (!emailRegex.test(emailInput.value.trim())) {
            showError(emailInput, 'emailError', 'Invalid email format');
            isValid = false;
        } else {
            hideError(emailInput, 'emailError');
        }

        if (passwordInput.value.trim() === "") {
            showError(passwordInput, 'passwordError', 'Password is required');
            isValid = false;
        } else {
            hideError(passwordInput, 'passwordError');
        }

        if (!isValid) e.preventDefault();
    });

    function showError(input, spanId, message) {
        input.classList.add('is-invalid');
        const span = document.getElementById(spanId);
        span.style.display = 'block';
        span.innerText = message;
    }

    function hideError(input, spanId) {
        input.classList.remove('is-invalid');
        const span = document.getElementById(spanId);
        span.style.display = 'none';
        span.innerText = '';
    }

    if (toggle) {
        toggle.addEventListener("click", function () {
            const type = passwordInput.type === "password" ? "text" : "password";
            passwordInput.type = type;
            this.classList.toggle("fa-eye");
            this.classList.toggle("fa-eye-slash");
        });
    }
});