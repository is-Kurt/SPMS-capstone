async function deleteDocument(documentId) {
    if (!confirm('Delete this document?')) return;

    const formData = new FormData();
    formData.append('id', documentId);
    formData.append('_method', 'DELETE');
    formData.append(AppConfig.csrfToken, AppConfig.csrfHash);

    try {
        const response = await axios.post(AppConfig.deleteUrl, formData);
        if (response.data.status === 'success') {
            AppConfig.csrfHash = response.data.csrfHash;
            document.querySelector(`[data-doc-id="${documentId}"]`).remove();

            const remaining = document.querySelectorAll('[data-doc-id]');
            if (remaining.length === 0) {
                document.getElementById('table-body-container').innerHTML = `
                    <div class="p-20 text-center text-sub-text italic">
                        No documents found in your account.
                    </div>
                `;
            }
        }
    } catch (error) {
        console.error('Delete failed:', error);
    }
}