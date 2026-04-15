window.addEventListener("storage", function (event) {
    if (event.key === "verify_success" && event.newValue === "1") {
        const banner = document.getElementById("verify-message");

        if (banner) {
            banner.className = "success-banner";
            banner.innerText = "Account verified successfully";

            setTimeout(() => {
                banner.style.opacity = "0";

                setTimeout(() => {
                    banner.remove();
                }, 500);
            }, 5000);
        }

        localStorage.removeItem("verify_success");
    }
});