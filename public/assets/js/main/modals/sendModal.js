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
        sendAllTargetId = sendBtn.getAttribute('data-id');
        const title = sendBtn.getAttribute('data-title');
        
        document.getElementById('send-all-doc-title').innerText = `"${title}"`;
        sendAllModal.open();
    }
});

async function confirmSendAll() {
    if (!sendAllTargetId || isSendingAll) return;

    const btnConfirm = document.getElementById('btn-confirm-send-all');
    const originalText = btnConfirm.innerText;

    isSendingAll = true;
    btnConfirm.innerText = "Sending...";
    btnConfirm.classList.add('opacity-75', 'cursor-not-allowed');

    const formData = new FormData();
    formData.append('folder_id', sendAllTargetId); 
    
    apiPost(`folder/send`, formData, {
        onSuccess: () => {
            console.log("Folder successfully distributed to all users!");
            isSendingAll = false;
            resetButton(btnConfirm, originalText);
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