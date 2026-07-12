import Modal from './Modal.js';

const teamModal = new Modal('modal-create-team');

const submitBtn = document.getElementById('btn-create-team');

const btnOpenDesktop = document.getElementById('btn-create-team-modal');
if (btnOpenDesktop) {
    btnOpenDesktop.addEventListener('click', () => teamModal.open());
}

const btnOpenMobile = document.getElementById('btn-create-team-mobile');
if (btnOpenMobile) {
    btnOpenMobile.addEventListener('click', () => teamModal.open());
}

document.getElementById('btn-close-create-team').addEventListener('click', () => teamModal.close());

// Disable the submit button while the real (non-AJAX) form submission is in flight
const formCreateTeam = document.getElementById('form-create-team');
if (formCreateTeam && submitBtn) {
    formCreateTeam.addEventListener('submit', () => {
        submitBtn.disabled = true;
        submitBtn.innerText = 'Creating...';
    });
}

// Restore button state if the user navigates back via cache (bfcache)
window.addEventListener('pageshow', () => {
    if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.innerText = 'Create & Select';
    }
});
