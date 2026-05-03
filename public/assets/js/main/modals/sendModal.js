import Modal from './Modal.js';

let sendAllTargetId = null;
let isSendingAll = false;

const sendAllModal = new Modal('send-all-modal', {
    onClose: () => {
        sendAllTargetId = null;
    }
});

document.addEventListener('click', (e) => {
    const sendBtn = e.target.closest('.btn-trigger-send-all');
    if (sendBtn) {
        // Now capturing the Folder ID from the button[cite: 40]
        sendAllTargetId = sendBtn.getAttribute('data-id');
        const title = sendBtn.getAttribute('data-title');
        
        document.getElementById('send-all-doc-title').innerText = `"${title}"`;
        sendAllModal.open();
    }
});

async function confirmSendAll() {
    if (!sendAllTargetId || isSendingAll) return;

    const titleInput = document.getElementById('send-rating-title');
    const titleError = document.getElementById('send-rating-error');
    const ratingTitle = titleInput.value.trim();

    if (!ratingTitle) {
        titleError.classList.remove('hidden');
        titleInput.classList.add('border-red-500', 'ring-1', 'ring-red-500');
        return; 
    } else {
        titleError.classList.add('hidden');
        titleInput.classList.remove('border-red-500', 'ring-1', 'ring-red-500');
    }

    const btnConfirm = document.getElementById('btn-confirm-send-all');
    const originalText = btnConfirm.innerText;

    isSendingAll = true;
    btnConfirm.innerText = "Sending...";
    btnConfirm.classList.add('opacity-75', 'cursor-not-allowed');

    const formData = new FormData();
    // CHANGED: The backend Folder::send method now expects 'folder_id'
    formData.append('folder_id', sendAllTargetId); 
    formData.append('rating_title', ratingTitle); 
    
    // apiPost will target folder/send based on your AppConfig
    apiPost(`${AppConfig.baseUrl}/send`, formData, {
        onSuccess: () => {
            console.log("Folder contents successfully distributed to all users!");
            
            isSendingAll = false;
            resetButton(btnConfirm, originalText);
            titleInput.value = ''; 
            sendAllModal.close();
        },
        onError: () => { 
            isSendingAll = false; 
            resetButton(btnConfirm, originalText);
        }
    });
}

function resetButton(btn, text) {
    btn.innerText = text;
    btn.classList.remove('opacity-75', 'cursor-not-allowed');
}

document.getElementById('btn-confirm-send-all').addEventListener('click', confirmSendAll);
document.getElementById('btn-close-send-all').addEventListener('click', () => sendAllModal.close());