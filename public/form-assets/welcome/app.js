// Welcome page JavaScript
// Add any welcome page specific functionality here

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
