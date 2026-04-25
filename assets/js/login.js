// ============================================================
// assets/js/login.js
// Password visibility toggle for the login form.
// ============================================================

/**
 * togglePassword()
 * Called when the user clicks the eye icon inside the password field.
 * Switches the input between type="password" (dots) and type="text" (visible).
 */
function togglePassword() {
    const input   = document.getElementById('pwdInput');
    const eyeIcon = document.getElementById('eyeIcon');

    const isHidden = input.type === 'password';

    // Switch the input type
    input.type = isHidden ? 'text' : 'password';

    // Swap the SVG path to show open-eye or slashed-eye icon
    eyeIcon.innerHTML = isHidden
        ? `<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
           <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
           <line x1="1" y1="1" x2="23" y2="23"/>`
        : `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
           <circle cx="12" cy="12" r="3"/>`;
}
