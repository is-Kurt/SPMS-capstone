let shareTargetDocId = null;
let shareTargetUserId = null;

function openShareModal(documentId) {
    shareTargetDocId = documentId;
    shareTargetUserId = null;
    document.getElementById('share-email-input').value = '';
    document.getElementById('share-search-result').classList.add('hidden');
    document.getElementById('share-error').classList.add('hidden');
    document.getElementById('share-modal').classList.remove('hidden');
}

function closeShareModal() {
    document.getElementById('share-modal').classList.add('hidden');
    shareTargetDocId = null;
    shareTargetUserId = null;
}

async function searchUser() {
    const email = document.getElementById('share-email-input').value.trim();
    const errorEl = document.getElementById('share-error');
    const resultEl = document.getElementById('share-search-result');

    errorEl.classList.add('hidden');
    resultEl.classList.add('hidden');

    if (!email) return;

    try {
        const response = await axios.get(AppConfig.userSearchUrl, { params: { email } });
        const data = response.data;

        if (data.status === 'success') {
            shareTargetUserId = data.user.id;
            document.getElementById('share-user-label').innerText = `${data.user.name} (${data.user.email})`;
            resultEl.classList.remove('hidden');
            resultEl.classList.add('flex');
        } else {
            errorEl.innerText = 'No user found with that email.';
            errorEl.classList.remove('hidden');
        }
    } catch (error) {
        errorEl.innerText = 'Search failed. Try again.';
        errorEl.classList.remove('hidden');
    }
}

async function confirmShare() {
    if (!shareTargetDocId || !shareTargetUserId) return;

    const formData = new FormData();
    formData.append('document_id', shareTargetDocId);
    formData.append('collaborator_id', shareTargetUserId);
    formData.append(AppConfig.csrfToken, AppConfig.csrfHash);

    try {
        const response = await axios.post(AppConfig.shareUrl, formData);
        if (response.data.status === 'success') {
            AppConfig.csrfHash = response.data.csrfHash;
            closeShareModal();
        }
    } catch (error) {
        document.getElementById('share-error').innerText = 'Share failed. Try again.';
        document.getElementById('share-error').classList.remove('hidden');
    }
}