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

const formCreateTeam = document.getElementById('form-create-team');
if (formCreateTeam && submitBtn) {
    formCreateTeam.addEventListener('submit', (e) => {
        e.preventDefault();
        submitBtn.disabled = true;
        submitBtn.innerText = 'Creating...';

        const formData = new FormData(formCreateTeam);

        apiPost(formCreateTeam.getAttribute('action'), formData, {
            onSuccess: (data) => {
                window.location.href = '/teams?team_id=' + data.id;
            },
            onError: async (errMsg) => {
                await window.appAlert(errMsg || 'Something went wrong.', { variant: 'danger' });
                submitBtn.disabled = false;
                submitBtn.innerText = 'Create & Select';
            }
        });
    });
}

// Restore button state if the user navigates back via cache (bfcache)
window.addEventListener('pageshow', () => {
    if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.innerText = 'Create & Select';
    }
});
