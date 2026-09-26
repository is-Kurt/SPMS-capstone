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
    undo: {
        wrap: 'bg-warning-50 dark:bg-warning-500/10 text-warning-500 ring-warning-50 dark:ring-warning-500/5',
        btn: 'bg-warning-500 hover:bg-warning-600 shadow-warning-500/20',
        path: 'M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3',
        undoAnimation: true
    },
    info: {
        wrap: 'bg-info-50 dark:bg-info-500/10 text-info-500 ring-info-50 dark:ring-info-500/5',
        btn: 'bg-accent hover:bg-accent-hover shadow-accent/20',
        path: 'M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0Zm-9-3.75h.008v.008H12V8.25Z'
    },
    primary: {
        wrap: 'bg-accent/10 text-accent ring-accent/20',
        btn: 'bg-accent hover:bg-accent-hover shadow-accent/20',
        path: 'M3.478 2.404a.75.75 0 00-.926.941l2.432 7.905H13.5a.75.75 0 010 1.5H4.984l-2.432 7.905a.75.75 0 00.926.94 60.519 60.519 0 0018.445-8.986.75.75 0 000-1.218A60.517 60.517 0 003.478 2.404Z',
        fill: 'currentColor',
        stroke: 'none',
        flyAnimation: true
    },
    success: {
        wrap: 'bg-success-50 dark:bg-success-500/10 text-success-500 ring-success-50 dark:ring-success-500/5',
        btn: 'bg-success-500 hover:bg-success-600 shadow-success-500/20',
        path: 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0Z'
    }
};

let activeResolve = null;
let isSettling = false;
let currentVariant = null;

function applyVariant(variant, customPath = null) {
    currentVariant = variant;
    const cfg = VARIANTS[variant] || VARIANTS.warning;
    elIconWrap.className = `inline-flex items-center justify-center w-14 h-14 rounded-full mb-4 ring-4 ${cfg.wrap}`;
    const isFilled = Boolean(cfg.fill && cfg.fill !== 'none');
    elIcon.setAttribute('fill', cfg.fill || 'none');
    elIcon.setAttribute('stroke', cfg.stroke || (isFilled ? 'none' : 'currentColor'));
    elIcon.setAttribute('stroke-width', cfg.strokeWidth || (isFilled ? '0' : '2'));
    elIcon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" d="${customPath || cfg.path}" />`;
    elIcon.style.transform = cfg.flyAnimation ? 'translate3d(0, 0, 0) rotate(-14deg)' : '';
    elIcon.style.opacity = '1';
    elIconWrap.style.transform = '';
    elIconWrap.style.opacity = '1';
    btnOk.className = `w-full text-white font-bold py-3.5 rounded-xl shadow-lg transition-all text-sm cursor-pointer active:scale-[0.98] ${cfg.btn}`;
    btnOk.style.opacity = '1';
    btnOk.disabled = false;
    btnCancel.disabled = false;
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

function open({ title, message, confirmText, cancelText, variant, iconPath, showCancel }) {
    return new Promise((resolve) => {
        activeResolve = resolve;

        elTitle.innerText = title;
        elMessage.innerText = message;
        btnOk.innerText = confirmText;
        applyVariant(variant, iconPath);

        if (showCancel) {
            btnCancel.classList.remove('hidden');
            btnCancel.innerText = cancelText;
        } else {
            btnCancel.classList.add('hidden');
        }

        modal.open();
    });
}

btnOk.addEventListener('click', async () => {
    const cfg = VARIANTS[currentVariant];
    if (cfg && cfg.flyAnimation) {
        btnOk.disabled = true;
        btnCancel.disabled = true;
        btnOk.style.transition = 'opacity 0.2s ease';
        btnOk.style.opacity = '0.75';

        // Pure GPU Compositor Animation via Web Animations API
        const planeAnim = elIcon.animate([
            { transform: 'translate3d(0, 0, 0) rotate(-14deg) scale(1)', opacity: 1 },
            { transform: 'translate3d(28px, -14px, 0) rotate(-22deg) scale(1.05)', opacity: 0.98, offset: 0.22 },
            { transform: 'translate3d(95px, -60px, 0) rotate(-34deg) scale(0.88)', opacity: 0.75, offset: 0.6 },
            { transform: 'translate3d(210px, -150px, 0) rotate(-46deg) scale(0.28)', opacity: 0 }
        ], {
            duration: 520,
            easing: 'cubic-bezier(0.22, 1, 0.36, 1)',
            fill: 'forwards'
        });

        elIconWrap.animate([
            { transform: 'scale(1)', opacity: 1 },
            { transform: 'scale(1.06)', opacity: 0.85, offset: 0.25 },
            { transform: 'scale(0.92)', opacity: 0.4 }
        ], {
            duration: 520,
            easing: 'cubic-bezier(0.22, 1, 0.36, 1)',
            fill: 'forwards'
        });

        await planeAnim.finished;
    } else if (cfg && cfg.undoAnimation) {
        btnOk.disabled = true;
        btnCancel.disabled = true;
        btnOk.style.transition = 'opacity 0.2s ease';
        btnOk.style.opacity = '0.75';

        // Smooth rewind / counter-clockwise spin & recoil
        const undoAnim = elIcon.animate([
            { transform: 'translate3d(0, 0, 0) rotate(0deg) scale(1)', opacity: 1 },
            { transform: 'translate3d(3px, -2px, 0) rotate(16deg) scale(1.08)', opacity: 1, offset: 0.2 },
            { transform: 'translate3d(-5px, 2px, 0) rotate(-180deg) scale(0.95)', opacity: 0.85, offset: 0.7 },
            { transform: 'translate3d(0, 0, 0) rotate(-210deg) scale(0.85)', opacity: 0.2, offset: 1 }
        ], {
            duration: 480,
            easing: 'cubic-bezier(0.22, 1, 0.36, 1)',
            fill: 'forwards'
        });

        elIconWrap.animate([
            { transform: 'scale(1)', opacity: 1 },
            { transform: 'scale(1.08)', opacity: 0.85, offset: 0.25 },
            { transform: 'scale(0.94)', opacity: 0.4 }
        ], {
            duration: 480,
            easing: 'cubic-bezier(0.22, 1, 0.36, 1)',
            fill: 'forwards'
        });

        await undoAnim.finished;
    }
    settle(true);
});
btnCancel.addEventListener('click', () => settle(false));

// Treat a backdrop-click dismissal (wired up by the Modal class itself) as a cancel.
modal.onClose = () => settle(false);

/**
 * Shows a confirm dialog and resolves true/false based on the user's choice.
 * Replaces the native confirm().
 */
window.appConfirm = function (message, options = {}) {
    if (typeof message === 'object' && message !== null) {
        options = message;
        message = options.message || '';
    }
    const variant = options.variant || 'danger';
    return open({
        title: options.title || 'Are you sure?',
        message,
        confirmText: options.confirmText || 'Confirm',
        cancelText: options.cancelText || (variant === 'danger' ? 'Cancel, keep it' : 'Cancel'),
        variant,
        iconPath: options.iconPath || null,
        showCancel: true
    });
};

/**
 * Shows a single-button notice dialog. Replaces the native alert().
 */
window.appAlert = function (message, options = {}) {
    if (typeof message === 'object' && message !== null) {
        options = message;
        message = options.message || '';
    }
    return open({
        title: options.title || 'Notice',
        message,
        confirmText: options.okText || 'OK',
        variant: options.variant || 'warning',
        iconPath: options.iconPath || null,
        showCancel: false
    });
};

/**
 * Shows a modern floating toast notification.
 * @param {string} message
 * @param {'success'|'danger'|'warning'|'info'} type
 */
window.showToast = function (message, type = 'success') {
    let container = document.getElementById('global-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'global-toast-container';
        container.className = 'fixed bottom-5 right-5 z-[300] flex flex-col gap-2 pointer-events-none max-w-sm w-full px-4 sm:px-0';
        document.body.appendChild(container);
    }

    const typeConfig = {
        success: {
            bg: 'bg-emerald-600 text-white dark:bg-emerald-500 shadow-emerald-900/30',
            icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0Z" />'
        },
        danger: {
            bg: 'bg-rose-600 text-white dark:bg-rose-500 shadow-rose-900/30',
            icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0Zm-9 3.75h.008v.008H12v-.008Z" />'
        },
        warning: {
            bg: 'bg-amber-600 text-white dark:bg-amber-500 shadow-amber-900/30',
            icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />'
        },
        info: {
            bg: 'bg-accent text-white shadow-accent/30',
            icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0Zm-9-3.75h.008v.008H12V8.25Z" />'
        }
    };

    const cfg = typeConfig[type] || typeConfig.success;

    const toast = document.createElement('div');
    toast.className = `pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-xl shadow-xl text-xs font-bold transition-all duration-300 transform translate-y-3 opacity-0 ${cfg.bg}`;
    toast.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            ${cfg.icon}
        </svg>
        <span class="flex-1 leading-snug">${message}</span>
        <button type="button" class="shrink-0 opacity-70 hover:opacity-100 transition-opacity p-0.5 cursor-pointer" onclick="this.parentElement.remove()">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    `;

    container.appendChild(toast);

    requestAnimationFrame(() => {
        toast.classList.remove('translate-y-3', 'opacity-0');
        toast.classList.add('translate-y-0', 'opacity-100');
    });

    const timeout = setTimeout(() => {
        toast.classList.remove('translate-y-0', 'opacity-100');
        toast.classList.add('translate-y-3', 'opacity-0');
        setTimeout(() => toast.remove(), 300);
    }, 3500);

    toast.addEventListener('mouseenter', () => clearTimeout(timeout));
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
