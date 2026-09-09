<!-- MEANS OF VERIFICATION (MOV) EVIDENCE MODAL -->
<div id="mov-modal" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-xs hidden flex items-center justify-center p-3 sm:p-6 transition-all duration-200" aria-modal="true" role="dialog">
    <div class="bg-surface border border-surface-border rounded-2xl shadow-2xl w-full max-w-4xl max-h-[92vh] flex flex-col overflow-hidden transform transition-all duration-200 scale-95 opacity-0" id="mov-modal-card">
        
        <!-- MODAL HEADER -->
        <div class="flex-none flex items-center justify-between p-4 sm:p-5 border-b border-surface-border bg-surface-header gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <div class="p-2 sm:p-2.5 rounded-xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 shrink-0 border border-emerald-500/30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <h3 class="text-base sm:text-lg font-black text-text truncate">Means of Verification (MOV)</h3>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 shrink-0" id="mov-badge-count">
                            0 Files
                        </span>
                    </div>
                    <p class="text-xs text-text-muted truncate mt-0.5" id="mov-modal-subtitle">
                        Verifiable evidence documents supporting this accomplishment
                    </p>
                </div>
            </div>

            <button type="button" onclick="closeMovModal()" class="text-text-muted hover:text-text p-2 rounded-lg hover:bg-surface-border/40 transition-colors cursor-pointer shrink-0" title="Close modal">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- ACCOMPLISHMENT SNIPPET BANNER -->
        <div class="flex-none px-4 sm:px-6 py-2.5 bg-surface-border/10 border-b border-surface-border text-xs text-text flex items-start gap-2">
            <span class="font-bold text-text-muted uppercase text-[10px] tracking-wider shrink-0 mt-0.5">Target Output:</span>
            <span class="font-medium text-text italic truncate" id="mov-accomplishment-text">—</span>
        </div>

        <!-- MODAL BODY (Scrollable) -->
        <div class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-6 custom-scrollbar" id="mov-modal-body">
            
            <!-- IN-APP PREVIEW CONTAINER (Hidden by default, shows when clicking Preview) -->
            <div id="mov-preview-container" class="hidden space-y-3 pb-4 border-b border-surface-border">
                <div class="flex items-center justify-between bg-surface-header p-2.5 sm:p-3 rounded-xl border border-surface-border gap-2">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="text-xs font-black text-text truncate" id="mov-preview-filename">Previewing Evidence</span>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <a id="mov-preview-external-link" href="#" target="_blank" class="inline-flex items-center gap-1.5 text-[11px] font-bold text-accent hover:underline px-2 py-1 rounded bg-accent/10 border border-accent/20 transition-colors">
                            <span>Open in Tab</span>
                            <svg class="w-3 h-3 inline-block shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                        <button type="button" onclick="closeMovPreview()" class="inline-flex items-center gap-1 text-[11px] font-bold text-text-muted hover:text-text px-2 py-1 rounded hover:bg-surface-border/40 transition-colors cursor-pointer">
                            <svg class="w-3 h-3 inline-block shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            <span>Close Preview</span>
                        </button>
                    </div>
                </div>

                <!-- Preview Viewport Frame -->
                <div id="mov-preview-viewport" class="w-full min-h-[350px] max-h-[550px] rounded-xl overflow-hidden border border-surface-border bg-zinc-950 flex items-center justify-center relative">
                    <!-- Dynamic iFrame or Image injected here -->
                </div>
            </div>

            <!-- EVIDENCE FILES LIST -->
            <div>
                <h4 class="text-xs font-black uppercase tracking-wider text-text-muted mb-3 flex items-center justify-between">
                    <span>Attached Proof Documents</span>
                    <span class="text-[11px] font-normal normal-case text-text-muted" id="mov-list-meta"></span>
                </h4>

                <div id="mov-files-list" class="space-y-2.5">
                    <!-- Dynamic file rows populated via JS -->
                </div>

                <!-- Empty State -->
                <div id="mov-empty-state" class="hidden py-8 text-center border-2 border-dashed border-surface-border rounded-xl p-6">
                    <div class="w-12 h-12 mx-auto mb-2 text-text-muted/60 flex items-center justify-center rounded-full bg-surface-border/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-text">No evidence documents attached</p>
                    <p class="text-xs text-text-muted mt-1"><?= empty($isEvaluationPhase) ? 'Means of Verification (MOV) is uploaded during the Evaluation Phase when reporting accomplishments.' : 'Attach certified grade sheets, publication papers, or official certificates as verifiable proof.' ?></p>
                </div>
            </div>

            <!-- UPLOAD DROPZONE (Only visible during Evaluation Phase when editable by owner) -->
            <?php if (!empty($isOwner) && empty($isCycleArchived) && !empty($isEvaluationPhase) && !empty($canEditEvaluation)): ?>
            <div id="mov-upload-zone" class="space-y-2 pt-2 border-t border-surface-border">
                <h4 class="text-xs font-black uppercase tracking-wider text-text-muted">
                    Attach New Evidence
                </h4>
                
                <div id="mov-drop-target" 
                     onclick="document.getElementById('mov-file-input').click()" 
                     ondragover="event.preventDefault(); this.classList.add('border-emerald-500', 'bg-emerald-500/5');" 
                     ondragleave="this.classList.remove('border-emerald-500', 'bg-emerald-500/5');" 
                     ondrop="handleMovDrop(event)"
                     class="border-2 border-dashed border-surface-border hover:border-emerald-500/60 rounded-xl p-5 text-center cursor-pointer transition-all bg-surface-header/30 hover:bg-emerald-500/5">
                    
                    <input type="file" id="mov-file-input" class="hidden" accept=".pdf,.png,.jpg,.jpeg,.webp,.docx,.doc" onchange="handleMovFileSelect(this)">
                    
                    <div class="flex flex-col items-center justify-center gap-1.5">
                        <div class="p-2 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                        </div>
                        <span class="text-xs sm:text-sm font-bold text-text">
                            Click to browse or drop proof file here
                        </span>
                        <span class="text-[11px] text-text-muted">
                            Supported: PDF, PNG, JPG, WEBP, DOCX (Max: 15MB)
                        </span>
                    </div>
                </div>

                <!-- Upload Progress Banner -->
                <div id="mov-upload-progress" class="hidden p-3 bg-emerald-500/10 border border-emerald-500/30 rounded-xl flex items-center justify-between text-xs text-emerald-700 dark:text-emerald-300 font-bold animate-pulse">
                    <div class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span id="mov-upload-progress-text">Uploading proof document...</span>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-wider">Processing</span>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <!-- MODAL FOOTER -->
        <div class="flex-none p-3 sm:p-4 border-t border-surface-border bg-surface-header flex items-center justify-between text-xs">
            <span class="text-[11px] text-text-muted font-medium italic">
                CSC SPMS Means of Verification (Annex Guideline)
            </span>
            <button type="button" onclick="closeMovModal()" class="px-4 py-2 bg-surface-border/40 hover:bg-surface-border/60 text-text font-bold rounded-lg transition-colors cursor-pointer">
                Done
            </button>
        </div>

    </div>
</div>

<script>
    window.activeMovRowId = null;
    window.documentAttachments = <?= json_encode($attachmentsByRow ?? []) ?>;

    function openMovModal(rowId, btnEl) {
        window.activeMovRowId = rowId;

        // Retrieve accomplishment description from table row
        const row = document.querySelector(`tr[data-row-id="${rowId}"]`);
        const accText = row ? (row.querySelector('.field-accomplishments')?.value?.trim() || row.querySelector('.field-mfo')?.value?.trim() || 'No description entered yet') : 'Accomplishment Row';
        
        const subtitleEl = document.getElementById('mov-accomplishment-text');
        if (subtitleEl) subtitleEl.innerText = accText;

        renderMovFileList();
        closeMovPreview();

        const modal = document.getElementById('mov-modal');
        const card = document.getElementById('mov-modal-card');
        if (modal && card) {
            modal.classList.remove('hidden');
            setTimeout(() => {
                card.classList.remove('scale-95', 'opacity-0');
                card.classList.add('scale-100', 'opacity-100');
            }, 10);
        }
    }

    function closeMovModal() {
        const modal = document.getElementById('mov-modal');
        const card = document.getElementById('mov-modal-card');
        if (modal && card) {
            card.classList.remove('scale-100', 'opacity-100');
            card.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                closeMovPreview();
                window.activeMovRowId = null;
            }, 150);
        }
    }

    function renderMovFileList() {
        const rowId = window.activeMovRowId;
        const listEl = document.getElementById('mov-files-list');
        const emptyEl = document.getElementById('mov-empty-state');
        const badgeCountEl = document.getElementById('mov-badge-count');
        if (!listEl) return;

        const files = (window.documentAttachments && window.documentAttachments[rowId]) || [];
        const count = files.length;

        if (badgeCountEl) {
            badgeCountEl.innerText = `${count} ${count === 1 ? 'File' : 'Files'}`;
        }

        // Also update the row's paperclip button in the document sheet
        updateRowMovBadge(rowId, count);

        if (count === 0) {
            listEl.innerHTML = '';
            if (emptyEl) emptyEl.classList.remove('hidden');
            return;
        }

        if (emptyEl) emptyEl.classList.add('hidden');

        let html = '';
        files.forEach(file => {
            const ext = (file.file_name.split('.').pop() || '').toLowerCase();
            let iconColor = 'text-blue-500 bg-blue-500/10 border-blue-500/20';
            let iconLabel = 'DOC';

            if (['pdf'].includes(ext)) {
                iconColor = 'text-rose-500 bg-rose-500/10 border-rose-500/20';
                iconLabel = 'PDF';
            } else if (['png', 'jpg', 'jpeg', 'webp'].includes(ext)) {
                iconColor = 'text-emerald-500 bg-emerald-500/10 border-emerald-500/20';
                iconLabel = 'IMG';
            }

            const canDelete = <?= (!empty($isOwner) && empty($isCycleArchived) && !empty($isEvaluationPhase) && !empty($canEditEvaluation)) ? 'true' : 'false' ?>;
            const viewUrl = `<?= site_url('attachments/view') ?>/${file.id}`;
            const downloadUrl = `<?= site_url('attachments/download') ?>/${file.id}`;

            html += `
                <div class="flex items-center justify-between p-3 sm:p-3.5 rounded-xl border border-surface-border bg-surface-header hover:border-emerald-500/40 transition-all gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="px-2 py-1 rounded text-[10px] font-black uppercase tracking-wider border ${iconColor} shrink-0">
                            ${iconLabel}
                        </span>
                        <div class="min-w-0">
                            <p class="text-xs sm:text-sm font-bold text-text truncate" title="${escapeHtml(file.file_name)}">
                                ${escapeHtml(file.file_name)}
                            </p>
                            <p class="text-[11px] text-text-muted flex items-center gap-2 mt-0.5">
                                <span>${file.formatted_size || ''}</span>
                                <span>•</span>
                                <span>Uploaded by ${escapeHtml(file.uploader_name || 'Staff')}</span>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5 shrink-0">
                        <button type="button" onclick="previewMovAttachment(${file.id}, '${escapeHtml(file.file_name)}', '${file.file_type}')" 
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-bold bg-accent/10 hover:bg-accent/20 text-accent border border-accent/20 transition-all cursor-pointer" title="Preview file in-app">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <span class="hidden sm:inline">Preview</span>
                        </button>

                        <a href="${downloadUrl}" download 
                           class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-bold bg-surface-border/30 hover:bg-surface-border/60 text-text border border-surface-border transition-all" title="Download file">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span class="hidden sm:inline">Download</span>
                        </a>

                        ${canDelete ? `
                            <button type="button" onclick="deleteMovAttachment(${file.id})" 
                                    class="p-1.5 rounded-lg text-rose-500 hover:text-rose-600 hover:bg-rose-500/10 border border-transparent hover:border-rose-500/20 transition-all cursor-pointer" title="Delete attachment">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        ` : ''}
                    </div>
                </div>
            `;
        });

        listEl.innerHTML = html;
    }

    function updateRowMovBadge(rowId, count) {
        const btn = document.getElementById(`btn-mov-${rowId}`);
        if (!btn) return;

        const textEl = btn.querySelector('.mov-btn-text');
        const iconEl = btn.querySelector('svg');

        if (count > 0) {
            btn.className = 'btn-mov-attachment inline-flex items-center gap-1.5 px-2 py-1 rounded-md font-bold transition-all border shadow-xs cursor-pointer bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border-emerald-500/40 hover:bg-emerald-500/25';
            if (textEl) textEl.innerText = `MOV (${count})`;
            if (iconEl) iconEl.className = 'w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400';
        } else {
            btn.className = 'btn-mov-attachment inline-flex items-center gap-1.5 px-2 py-1 rounded-md font-bold transition-all border shadow-xs cursor-pointer bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 border-slate-300 dark:border-slate-700';
            if (textEl) textEl.innerText = 'Attach MOV';
            if (iconEl) iconEl.className = 'w-3.5 h-3.5 text-slate-500';
        }
    }

    function previewMovAttachment(id, fileName, fileType) {
        const container = document.getElementById('mov-preview-container');
        const viewport = document.getElementById('mov-preview-viewport');
        const nameEl = document.getElementById('mov-preview-filename');
        const extLink = document.getElementById('mov-preview-external-link');

        if (!container || !viewport) return;

        const viewUrl = `<?= site_url('attachments/view') ?>/${id}`;
        if (nameEl) nameEl.innerText = fileName;
        if (extLink) extLink.href = viewUrl;

        const ext = (fileName.split('.').pop() || '').toLowerCase();

        if (ext === 'pdf') {
            viewport.innerHTML = `<iframe src="${viewUrl}#toolbar=1" class="w-full h-[500px] border-none bg-white rounded-lg"></iframe>`;
        } else if (['png', 'jpg', 'jpeg', 'webp'].includes(ext)) {
            viewport.innerHTML = `<div class="w-full h-[500px] overflow-auto flex items-center justify-center p-3 bg-zinc-950/80"><img src="${viewUrl}" class="max-h-[460px] object-contain rounded-lg shadow-lg" alt="${escapeHtml(fileName)}" /></div>`;
        } else {
            const downloadUrl = `<?= site_url('attachments/download') ?>/${id}`;
            viewport.innerHTML = `
                <div class="text-center p-8 space-y-3">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-blue-500/10 text-blue-500 flex items-center justify-center font-black text-xl border border-blue-500/20">
                        DOC
                    </div>
                    <h4 class="text-sm font-bold text-white">${escapeHtml(fileName)}</h4>
                    <p class="text-xs text-slate-400 max-w-sm mx-auto">Word and binary documents cannot be rendered in the browser. Download the file to view its contents.</p>
                    <a href="${downloadUrl}" download class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition-all shadow-md">
                        Download Word Document
                    </a>
                </div>
            `;
        }

        container.classList.remove('hidden');
        container.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function closeMovPreview() {
        const container = document.getElementById('mov-preview-container');
        const viewport = document.getElementById('mov-preview-viewport');
        if (container) container.classList.add('hidden');
        if (viewport) viewport.innerHTML = '';
    }

    function handleMovFileSelect(input) {
        if (input.files && input.files[0]) {
            uploadMovFile(input.files[0]);
            input.value = '';
        }
    }

    function handleMovDrop(e) {
        e.preventDefault();
        const dropTarget = document.getElementById('mov-drop-target');
        if (dropTarget) dropTarget.classList.remove('border-emerald-500', 'bg-emerald-500/5');

        if (e.dataTransfer.files && e.dataTransfer.files[0]) {
            uploadMovFile(e.dataTransfer.files[0]);
        }
    }

    async function uploadMovFile(file) {
        const rowId = window.activeMovRowId;
        if (!rowId) return;

        const progressEl = document.getElementById('mov-upload-progress');
        if (progressEl) progressEl.classList.remove('hidden');

        const formData = new FormData();
        formData.append('document_id', '<?= (int)($doc['id'] ?? 0) ?>');
        formData.append('row_id', rowId);
        formData.append('file', file);

        try {
            const res = await axios.post('<?= site_url('attachments/upload') ?>', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (res.data && res.data.status === 'success') {
                if (!window.documentAttachments[rowId]) {
                    window.documentAttachments[rowId] = [];
                }
                window.documentAttachments[rowId].push(res.data.attachment);
                renderMovFileList();
                showToast('Evidence attached successfully!');
            } else {
                alert(res.data?.message || 'Error uploading file.');
            }
        } catch (err) {
            console.error('MOV Upload Error:', err);
            const msg = err.response?.data?.message || 'Failed to upload evidence file.';
            alert(msg);
        } finally {
            if (progressEl) progressEl.classList.add('hidden');
        }
    }

    async function deleteMovAttachment(id) {
        if (!confirm('Are you sure you want to remove this evidence file?')) return;

        try {
            const res = await axios.delete(`<?= site_url('attachments') ?>/${id}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (res.data && res.data.status === 'success') {
                const rowId = window.activeMovRowId;
                if (window.documentAttachments[rowId]) {
                    window.documentAttachments[rowId] = window.documentAttachments[rowId].filter(f => f.id != id);
                }
                renderMovFileList();
                closeMovPreview();
                showToast('Attachment removed.');
            } else {
                alert(res.data?.message || 'Error deleting attachment.');
            }
        } catch (err) {
            console.error('MOV Delete Error:', err);
            alert(err.response?.data?.message || 'Failed to delete attachment.');
        }
    }
</script>
