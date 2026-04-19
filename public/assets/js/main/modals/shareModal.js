import Modal from './Modal.js';

let shareTargetDocId = null;
let shareTargetUserId = null;
let isSharing = false;

const shareModal = new Modal('share-modal', {
    onClose: () => {
        shareTargetDocId = null;
        shareTargetUserId = null;
        document.getElementById('share-search-result').classList.replace('flex', 'hidden');
    }
});

window.openShareModal = (documentId) => {
    shareTargetDocId = documentId;
    shareModal.open();
};

async function findUser() {
    const email = document.getElementById('share-email-input').value.trim();
    const resultEl = document.getElementById('share-search-result');
    const errorEl = document.getElementById('share-error');

    resultEl.classList.replace('flex', 'hidden');
    errorEl.classList.add('hidden');

    if (!email) return;

    apiGet('user/find', {
        email: email
    }, {
        onSuccess: (data) => {
            const user = data.user;
            shareTargetUserId = user.id;
            document.getElementById('share-user-label').innerText = `${user['username']} (${user['email']})`;
            resultEl.classList.replace('hidden', 'flex');
        },
        onError: (errorMessage) => {
            errorEl.innerText = errorMessage;
            errorEl.classList.remove('hidden');
        }
    }
    )
}

async function confirmShare() {
    if (!shareTargetDocId || !shareTargetUserId || isSharing) return;

    const formData = new FormData();
    formData.append('document_id', shareTargetDocId);
    formData.append('collaborator_id', shareTargetUserId);

    isSharing = true;
    apiPost(`${AppConfig.baseUrl}/share`, formData, {
            onSuccess: () => {
                isSharing = false;
                shareModal.close();
            },
            onError: () => { isSharing = false; }
        }
    )
}

document.getElementById('btn-share-search').addEventListener('click', findUser);
document.getElementById('btn-share-confirm').addEventListener('click', confirmShare);

document.getElementById('share-email-input').addEventListener('keypress', (e) => {
    if (e.key === 'Enter') findUser();
});