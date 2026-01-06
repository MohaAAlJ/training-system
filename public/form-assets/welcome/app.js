// ========================================
// WELCOME PAGE - REFACTORED CODE
// ========================================

/**
 * ThemeManager - Handles theme switching and persistence
 */
class ThemeManager {
    constructor() {
        this.themeToggle = document.getElementById("themeToggle");
        this.init();
    }

    init() {
        if (this.themeToggle) {
            this.themeToggle.addEventListener("click", () =>
                this.toggleTheme()
            );
        }
    }

    toggleTheme() {
        const currentTheme =
            document.documentElement.getAttribute("data-theme");
        const newTheme = currentTheme === "dark" ? "light" : "dark";

        document.documentElement.setAttribute("data-theme", newTheme);
        localStorage.setItem("theme", newTheme);

        this.animateIcon();
    }

    animateIcon() {
        const icon = this.themeToggle?.querySelector(".mode-icon");
        if (icon) {
            icon.style.transform = "scale(0.5) rotate(180deg)";
            setTimeout(() => {
                icon.style.transform = "scale(1) rotate(360deg)";
            }, 150);
        }
    }
}

/**
 * PageAnimator - Handles page entrance animations
 */
class PageAnimator {
    static animateOnLoad() {
        const welcomeCard = document.querySelector(".welcome-card");
        const heroElement = document.querySelector(".hero");

        if (heroElement) {
            this.animateElement(heroElement, "translateY(-20px)", 500);
        }

        if (welcomeCard) {
            setTimeout(() => {
                this.animateElement(welcomeCard, "translateY(20px)", 600);
            }, 100);
        }
    }

    static animateElement(element, initialTransform, duration) {
        element.style.opacity = "0";
        element.style.transform = initialTransform;

        setTimeout(() => {
            element.style.transition = `opacity ${duration}ms ease, transform ${duration}ms ease`;
            element.style.opacity = "1";
            element.style.transform = "translateY(0)";
        }, 10);
    }
}

// ========================================
// OLD CODE - KEPT FOR REFERENCE
// ========================================

/*
// --- ORIGINAL THEME TOGGLE LOGIC ---
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
*/

// ========================================
// APPLICATION INITIALIZATION
// ========================================

document.addEventListener("DOMContentLoaded", function () {
    // Initialize managers
    const themeManager = new ThemeManager();

    // Animate page elements
    PageAnimator.animateOnLoad();
});
