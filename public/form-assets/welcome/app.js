// --- THEME TOGGLE LOGIC ---
const themeToggle = document.getElementById("themeToggle");
if (themeToggle) {
    themeToggle.addEventListener("click", () => {
        const currentTheme =
            document.documentElement.getAttribute("data-theme");
        const newTheme = currentTheme === "dark" ? "light" : "dark";

        document.documentElement.setAttribute("data-theme", newTheme);
        localStorage.setItem("theme", newTheme);

        // Micro-animation for the icon
        const icon = themeToggle.querySelector(".mode-icon");
        if (icon) {
            icon.style.transform = "scale(0.5) rotate(180deg)";
            setTimeout(() => {
                icon.style.transform = "scale(1) rotate(360deg)";
            }, 150);
        }
    });
}

document.addEventListener("DOMContentLoaded", function () {
    // Add smooth entrance animation
    const welcomeCard = document.querySelector(".welcome-card");
    if (welcomeCard) {
        welcomeCard.style.opacity = "0";
        welcomeCard.style.transform = "translateY(20px)";

        setTimeout(() => {
            welcomeCard.style.transition =
                "opacity 0.6s ease, transform 0.6s ease";
            welcomeCard.style.opacity = "1";
            welcomeCard.style.transform = "translateY(0)";
        }, 100);
    }
});
