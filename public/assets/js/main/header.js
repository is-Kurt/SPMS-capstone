// SPMS Header & Table Utilities

// 7. Keep frozen table headers (data-frozen-header) pixel-aligned with their
// scrollable body (data-frozen-body) by matching the body's real scrollbar width.
// A ResizeObserver on the body catches window resizes, tab switches, and
// filtered rows toggling the scrollbar on/off - no manual re-sync calls needed.
document.addEventListener('DOMContentLoaded', () => {
    const pairs = [];
    document.querySelectorAll('[data-frozen-body]').forEach(body => {
        const header = body.previousElementSibling;
        if (header && header.hasAttribute('data-frozen-header')) {
            pairs.push({ header, body });
        }
    });

    if (pairs.length === 0) return;

    const sync = () => {
        pairs.forEach(({ header, body }) => {
            header.style.paddingRight = (body.offsetWidth - body.clientWidth) + 'px';
        });
    };

    const ro = new ResizeObserver(sync);
    pairs.forEach(({ body }) => ro.observe(body));
    sync();
});

// 8. On localhost there's no real cron running the email-queue-draining Spark
// commands (UpdateStatuses/CheckFolderDeadlines), so this keeps the queue
// moving in the background on every authenticated page. Only runs in
// development (see the SPMS_ENV check below) - on a real server the cron
// commands are the sole drain, avoiding two triggers racing on the same rows.
// Also guarded to pages that render the header (i.e. an active session), since
// the endpoint requires one and would otherwise just fail quietly on login/signup.
function processBackgroundEmails() {
    const formData = new FormData();

    apiPost('/account/process-queue', formData, {
        onSuccess: (data) => {
            if (data.queue_state === 'working' && data.remaining > 0) {
                setTimeout(processBackgroundEmails, 2000);
            }
        },
        onError: () => {}
    });
}

document.addEventListener('DOMContentLoaded', () => {
    // Development only - on a real server the email queue is drained by cron
    // (spms:update-statuses/spms:check-folder-deadlines) instead of client polling.
    if (window.SPMS_ENV === 'development' && document.getElementById('profile-btn-mobile')) {
        processBackgroundEmails();
    }
});