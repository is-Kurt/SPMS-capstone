import Modal from './Modal.js';

const modal = new Modal('confirm-modal');

const elIconWrap = document.getElementById('confirm-modal-icon-wrap');
const elIcon = document.getElementById('confirm-modal-icon');
const elTitle = document.getElementById('confirm-modal-title');
const elMessage = document.getElementById('confirm-modal-message');
const btnOk = document.getElementById('btn-confirm-ok');
const btnCancel = document.getElementById('btn-confirm-cancel');

const VARIANTS = {
    danger: {
        wrap: 'bg-danger-50 dark:bg-danger-500/10 text-danger-500 ring-danger-50 dark:ring-danger-500/5',
        btn: 'bg-danger-500 hover:bg-danger-600 shadow-danger-500/20',
        path: 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16'
    },
    warning: {
        wrap: 'bg-warning-50 dark:bg-warning-500/10 text-warning-500 ring-warning-50 dark:ring-warning-500/5',
        btn: 'bg-warning-500 hover:bg-warning-600 shadow-warning-500/20',
        path: 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z'
    },
    info: {
        wrap: 'bg-info-50 dark:bg-info-500/10 text-info-500 ring-info-50 dark:ring-info-500/5',
        btn: 'bg-accent hover:bg-accent-hover shadow-accent/20',
        path: 'M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0Zm-9-3.75h.008v.008H12V8.25Z'
    },
    success: {
        wrap: 'bg-success-50 dark:bg-success-500/10 text-success-500 ring-success-50 dark:ring-success-500/5',
        btn: 'bg-success-500 hover:bg-success-600 shadow-success-500/20',
        path: 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0Z'
    }
};

let activeResolve = null;
let isSettling = false;

function applyVariant(variant) {
    const cfg = VARIANTS[variant] || VARIANTS.warning;
    elIconWrap.className = `inline-flex items-center justify-center w-14 h-14 rounded-full mb-4 ring-4 ${cfg.wrap}`;
    elIcon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" d="${cfg.path}" />`;
    btnOk.className = `w-full text-white font-bold py-3.5 rounded-xl shadow-lg transition-all text-sm cursor-pointer active:scale-[0.98] ${cfg.btn}`;
}

function settle(result) {
    if (isSettling) return;
    isSettling = true;

    modal.close();

    if (activeResolve) {
        activeResolve(result);
        activeResolve = null;
    }

    isSettling = false;
}

function open({ title, message, confirmText, cancelText, variant, showCancel }) {
    return new Promise((resolve) => {
        activeResolve = resolve;

        elTitle.innerText = title;
        elMessage.innerText = message;
        btnOk.innerText = confirmText;
        applyVariant(variant);

        if (showCancel) {
            btnCancel.classList.remove('hidden');
            btnCancel.innerText = cancelText;
        } else {
            btnCancel.classList.add('hidden');
        }

        modal.open();
    });
}

btnOk.addEventListener('click', () => settle(true));
btnCancel.addEventListener('click', () => settle(false));

// Treat a backdrop-click dismissal (wired up by the Modal class itself) as a cancel.
modal.onClose = () => settle(false);

/**
 * Shows a confirm dialog and resolves true/false based on the user's choice.
 * Replaces the native confirm().
 */
window.appConfirm = function (message, options = {}) {
    return open({
        title: options.title || 'Are you sure?',
        message,
        confirmText: options.confirmText || 'Confirm',
        cancelText: options.cancelText || 'Cancel, keep it',
        variant: options.variant || 'danger',
        showCancel: true
    });
};

/**
 * Shows a single-button notice dialog. Replaces the native alert().
 */
window.appAlert = function (message, options = {}) {
    return open({
        title: options.title || 'Notice',
        message,
        confirmText: options.okText || 'OK',
        variant: options.variant || 'warning',
        showCancel: false
    });
};

// Any <form data-confirm="..."> is intercepted and re-submitted after the user confirms.
// A <form data-blocked-message="..."> is always stopped and just shows an explanatory alert.
document.addEventListener('submit', async (e) => {
    const form = e.target;
    if (!(form instanceof HTMLFormElement)) return;

    if (form.hasAttribute('data-blocked-message')) {
        e.preventDefault();
        await window.appAlert(form.dataset.blockedMessage, {
            title: form.dataset.blockedTitle || 'Action Blocked',
            variant: 'danger'
        });
        return;
    }

    if (!form.hasAttribute('data-confirm') || form.dataset.confirmed === 'true') return;

    e.preventDefault();

    const ok = await window.appConfirm(form.dataset.confirm, {
        title: form.dataset.confirmTitle || undefined,
        confirmText: form.dataset.confirmText || 'Delete',
        variant: form.dataset.confirmVariant || 'danger'
    });

    if (ok) {
        form.dataset.confirmed = 'true';
        form.submit();
    }
});
