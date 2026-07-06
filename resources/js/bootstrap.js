/**
 * Bootstrap the application's JavaScript dependencies.
 *
 * Sets up global defaults for AJAX requests so that Laravel
 * can properly recognise XHR/fetch calls.
 */

// Set the CSRF token as a default header for all fetch / XMLHttpRequest calls.
const token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    window._token = token.content;
}
