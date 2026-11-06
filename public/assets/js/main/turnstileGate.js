/**
 * Keeps login buttons disabled until Cloudflare Turnstile has passed, and disables them again if the token
 * expires, the check fails or it times out. The widget calls these functions by name through its
 * data-callback / data-expired-callback / data-error-callback / data-timeout-callback attributes, so this
 * file must load before Turnstile's own script. Buttons opt in with data-turnstile-gated and start out
 * disabled in the HTML, so they are never clickable before the check. The server rejects a login without
 * a valid token anyway (Auth\Session); this only stops people from submitting too early.
 */
(function () {
    function setPassed(passed) {
        document.querySelectorAll('[data-turnstile-gated]').forEach(function (btn) {
            btn.disabled = !passed;
            if (passed) {
                btn.removeAttribute('title');
            } else {
                btn.title = 'Complete the security check above first';
            }
        });
    }

    window.spmsTurnstilePassed = function () { setPassed(true); };
    window.spmsTurnstileReset = function () { setPassed(false); };
})();
