    <!-- MOV ATTACHMENTS MODAL -->
    <div id="movAttachmentsModal" class="spms-modal-backdrop hidden" onclick="if(event.target === this) closeAttachmentsModal()">
        <div class="spms-modal-dialog" style="max-width: 520px;">
            <!-- Header -->
            <div class="spms-modal-header">
                <div>
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-600 dark:text-[#34d399] block mb-0.5">SUPPORTING EVIDENCE</span>
                    <h3 class="text-lg font-extrabold text-zinc-900 dark:text-white m-0">Document MOVs & Attachments</h3>
                </div>
                <button type="button" onclick="closeAttachmentsModal()" class="spms-modal-btn-close">
                    <svg style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Body -->
            <div class="spms-modal-body custom-scrollbar">
                <?php if (!empty($attachmentsList)): ?>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <?php foreach ($attachmentsList as $att): ?>
                            <div class="spms-modal-card" style="display: flex; align-items: center; justify-content: space-between; gap: 12px;">
                                <div style="display: flex; align-items: center; gap: 12px; min-width: 0;">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-zinc-800 text-emerald-600 dark:text-[#34d399] flex items-center justify-center shrink-0">
                                        <svg style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                    </div>
                                    <div style="display: flex; flex-direction: column; min-width: 0;">
                                        <span class="text-xs font-bold text-zinc-900 dark:text-white truncate" title="<?= esc($att['file_name']) ?>"><?= esc($att['file_name']) ?></span>
                                        <span class="text-[10px] text-zinc-500 dark:text-[#5a8b73]">
                                            <?= date('M d, Y', strtotime($att['created_at'])) ?>
                                        </span>
                                    </div>
                                </div>
                                <div style="flex-shrink: 0;">
                                    <a href="<?= site_url('attachments/download/' . $att['id']) ?>" class="spms-modal-btn-close" style="text-decoration: none;" title="Download">
                                        <svg style="width: 14px; height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div style="padding: 36px 16px; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-zinc-800 border border-emerald-200 dark:border-zinc-800 text-emerald-600 dark:text-[#5a8b73] flex items-center justify-center mb-3">
                            <svg style="width: 24px; height: 24px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                        </div>
                        <h4 class="text-sm font-bold text-zinc-900 dark:text-white m-0 mb-1">No Files Attached Yet</h4>
                        <p class="text-xs text-zinc-500 dark:text-[#5a8b73] max-w-[320px] m-0 leading-relaxed">
                            Supporting evidence and Means of Verification (MOVs) can be uploaded directly to each commitment row in your paper.
                        </p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Footer -->
            <div class="spms-modal-footer">
                <?php if (!empty($primaryDoc['id'])): ?>
                    <a href="<?= site_url('document/' . $primaryDoc['id']) ?>" class="spms-hub-btn-primary" style="padding: 10px 18px !important; font-size: 11px !important;">
                        Open Paper to Add MOVs
                    </a>
                <?php else: ?>
                    <div></div>
                <?php endif; ?>
                <button type="button" onclick="closeAttachmentsModal()" class="spms-hub-btn-secondary" style="padding: 9px 18px !important; font-size: 11px !important;">
                    Close
                </button>
            </div>
        </div>
    </div>

