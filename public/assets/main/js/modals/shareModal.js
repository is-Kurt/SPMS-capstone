import Modal from './Modal.js';

// State variables
let shareTargetDocId = null;
let shareTargetUserId = null;

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

async function searchUser() {
    const email = document.getElementById('share-email-input').value.trim();
    const resultEl = document.getElementById('share-search-result');
    const errorEl = document.getElementById('share-error');

    errorEl.classList.add('hidden');
    resultEl.classList.replace('flex', 'hidden');

    if (!email) return;

    try {
        const response = await axios.get(AppConfig.userSearchUrl, { params: { email } });
        const data = response.data;

        if (data.status === 'success') {
            shareTargetUserId = data.user.id;
            document.getElementById('share-user-label').innerText = `${data.user.name} (${data.user.email})`;
            resultEl.classList.replace('hidden', 'flex');
        } else {
            errorEl.innerText = 'No user found with that email.';
            errorEl.classList.remove('hidden');
        }
    } catch (error) {
        errorEl.innerText = 'Search failed. Try again.';
        errorEl.classList.remove('hidden');
    }
}

/**
 * Confirm Share Logic (POST Request with FormData)
 */
async function confirmShare() {
    if (!shareTargetDocId || !shareTargetUserId) return;

    // Use FormData to ensure CodeIgniter's getPost() works
    const formData = new FormData();
    formData.append('document_id', shareTargetDocId);
    formData.append('collaborator_id', shareTargetUserId);
    formData.append(AppConfig.csrfToken, AppConfig.csrfHash);

    try {
        const response = await axios.post(AppConfig.shareUrl, formData);
        
        if (response.data.status === 'success') {
            // Update the global CSRF hash for the next operation
            AppConfig.csrfHash = response.data.csrfHash;
            shareModal.close();
            // Optional: Show a success toast/notification here
        }
    } catch (error) {
        const errorEl = document.getElementById('share-error');
        errorEl.innerText = 'Share failed. Try again.';
        errorEl.classList.remove('hidden');
    }
}

// --- Event Listeners (Replacing onClick) ---
document.getElementById('btn-share-search').addEventListener('click', searchUser);
document.getElementById('btn-share-confirm').addEventListener('click', confirmShare);

// Allow pressing "Enter" in the email input to trigger search
document.getElementById('share-email-input').addEventListener('keypress', (e) => {
    if (e.key === 'Enter') searchUser();
});