/**
 * Trainee Form JavaScript
 * ========================
 * Handles client-side interactions for the trainee application form
 * - Theme toggle
 * - Input filtering (national ID, phone)
 * - Toast notifications
 */

document.addEventListener('livewire:initialized', initializeForm);
document.addEventListener('DOMContentLoaded', registerToastHandler);
document.addEventListener('livewire:navigated', registerToastHandler);

/**
 * Initialize form on Livewire component load
 */
function initializeForm() {
    setupThemeToggle();
    setupNationalIdFilter();
    setupPhoneFilter();
}

/**
 * Setup theme toggle functionality
 */
function setupThemeToggle() {
    const themeToggle = document.getElementById('themeToggle');
    const savedTheme = localStorage.getItem('theme') || 'light';
    
    document.documentElement.setAttribute('data-theme', savedTheme);
    
    themeToggle?.addEventListener('click', () => {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
    });
}

/**
 * Filter national ID input to accept only digits
 */
function setupNationalIdFilter() {
    const observer = new MutationObserver(() => {
        const nationalIdInputs = document.querySelectorAll('input[type="text"][pattern="[0-9]*"]');
        
        nationalIdInputs.forEach(input => {
            if (!input.dataset.filteredNationalId) {
                input.dataset.filteredNationalId = 'true';
                attachInputFilterListeners(input, /[^0-9]/g);
            }
        });
    });

    observer.observe(document.body, { childList: true, subtree: true });
}

/**
 * Filter phone input to accept only digits
 */
function setupPhoneFilter() {
    const observer = new MutationObserver(() => {
        const phoneInputs = document.querySelectorAll('input[type="tel"]');
        
        phoneInputs.forEach(input => {
            if (!input.dataset.filteredPhone) {
                input.dataset.filteredPhone = 'true';
                attachInputFilterListeners(input, /[^0-9]/g);
            }
        });
    });

    observer.observe(document.body, { childList: true, subtree: true });
}

/**
 * Attach filter listeners to input element
 * @param {HTMLInputElement} input - The input element to filter
 * @param {RegExp} filterRegex - Regex pattern for characters to remove
 */
function attachInputFilterListeners(input, filterRegex) {
    // Prevent non-digit input
    input.addEventListener('input', (e) => {
        e.target.value = e.target.value.replace(filterRegex, '');
    });
    
    // Prevent non-digit keypress
    input.addEventListener('keypress', (e) => {
        if (!/[0-9]/.test(e.key)) {
            e.preventDefault();
        }
    });
    
    // Prevent pasting non-digit content
    input.addEventListener('paste', (e) => {
        e.preventDefault();
        const pastedText = (e.clipboardData || window.clipboardData).getData('text');
        const digitsOnly = pastedText.replace(filterRegex, '');
        e.target.value = digitsOnly;
        
        // Trigger Livewire update
        e.target.dispatchEvent(new Event('input', { bubbles: true }));
    });
}

/**
 * Handle toast notifications
 */
function registerToastHandler() {
    if (window.__toastListenerRegistered) {
        return;
    }

    window.__toastListenerRegistered = true;
    
    Livewire.on('show-toast', ({ message, type }) => {
        const toast = document.getElementById('toast');
        const toastMessage = document.getElementById('toastMessage');

        if (toast && toastMessage) {
            toastMessage.textContent = message;
            toast.classList.remove('show', 'error', 'success', 'warning', 'info');
            toast.classList.add('show', type || 'success');

            setTimeout(() => {
                toast.classList.remove('show');
            }, 4000);
        }
    });
}
