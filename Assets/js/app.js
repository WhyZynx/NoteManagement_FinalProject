document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("registerForm");

    if (form) {
        const email = document.getElementById("email");
        const name = document.getElementById("name");
        const password = document.getElementById("password");
        const repassword = document.getElementById("confirm-password");

        form.addEventListener("submit", function (e) {
            let isValid = true;

            clearError("emailError");
            clearError("nameError");
            clearError("passwordError");
            clearError("repasswordError");

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (!emailRegex.test(email.value.trim())) {
                showError(email, "emailError", "Invalid email format");
                isValid = false;
            }

            if (name.value.trim() === "") {
                showError(name, "nameError", "Display name is required");
                isValid = false;
            }

            if (password.value.trim() === "") {
                showError(password, "passwordError", "Password is required");
                isValid = false;
            }

            if (
                repassword.value.trim() === "" ||
                repassword.value !== password.value
            ) {
                showError(
                    repassword,
                    "repasswordError",
                    "Passwords do not match"
                );
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    }

    const toggles = document.querySelectorAll(".toggle-password");

    toggles.forEach(toggle => {
        toggle.addEventListener("click", function () {
            const input = this.previousElementSibling;

            input.type =
                input.type === "password" ? "text" : "password";

            this.classList.toggle("fa-eye");
            this.classList.toggle("fa-eye-slash");
        });
    });

    function showError(input, id, message) {
        input.classList.add("is-invalid");
        const el = document.getElementById(id);
        el.innerText = message;
        el.classList.add("show");
    }

    function clearError(id, input) {
        const el = document.getElementById(id);
        el.innerText = "";
        el.classList.remove("show");

        if (input) input.classList.remove("is-invalid");
    }
});