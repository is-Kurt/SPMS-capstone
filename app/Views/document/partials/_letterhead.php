                <?php if (!empty($isCycleArchived)): ?>
                    <!-- Archived & Frozen Cycle Warning Banner -->
                    <div class="p-3.5 bg-amber-500/10 border border-amber-500/30 text-amber-900 dark:text-amber-300 rounded-xl text-xs font-bold flex items-center justify-between gap-3 shadow-xs print-hide">
                        <div class="flex items-center gap-2.5">
                            <div class="p-1 rounded-full bg-amber-500/20 text-amber-600 dark:text-amber-400 shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <span>This evaluation cycle has been closed and archived. Ratings, accomplishments, and targets are permanently frozen in read-only mode.</span>
                        </div>
                        <span class="px-2.5 py-1 rounded-md bg-amber-500/20 text-[10px] uppercase tracking-wider font-extrabold shrink-0 border border-amber-500/30">Cycle Closed</span>
                    </div>
                <?php endif; ?>

                <!-- INSTITUTIONAL OFFICIAL LETTERHEAD (BSU Seal, Gothic Masthead & Bagong Pilipinas) -->
                <div class="spms-official-letterhead flex items-center justify-between gap-3 sm:gap-6 mb-4 pb-3 border-b border-zinc-300 dark:border-zinc-700/80">
                    <!-- Left: BSU Official Seal -->
                    <div class="shrink-0 flex items-center justify-center">
                        <img src="<?= base_url('assets/images/bsu_seal.png') ?>" alt="Benguet State University Seal" class="w-16 h-16 sm:w-20 sm:h-20 object-contain drop-shadow-xs" />
                    </div>

                    <!-- Center: Republic & University Letterhead -->
                    <div class="flex-1 text-center min-w-0">
                        <p class="spms-letterhead-republic" style="font-family: 'Bookman Old Style', 'Century Schoolbook', 'Times New Roman', serif; font-size: 12px; font-weight: 600; margin: 0; line-height: 1.3;">Republic of the Philippines</p>
                        <h1 class="spms-letterhead-title" style="font-family: 'Old English Text MT', 'UnifrakturMaguntia', 'Chaucer', serif; font-size: 26px; font-weight: 700; line-height: 1.25; margin: 3px 0;">Benguet State University</h1>
                        <p class="spms-letterhead-location" style="font-family: 'Bookman Old Style', 'Century Schoolbook', 'Times New Roman', serif; font-size: 11px; font-style: italic; font-weight: 600; margin: 0; line-height: 1.3;">La Trinidad, 2601 Benguet</p>
                        <p id="spms-letterhead-dept" class="spms-letterhead-dept" style="font-family: 'Monotype Corsiva', 'Lucida Calligraphy', 'Brush Script MT', 'Dancing Script', cursive, serif; font-size: 15.5px; font-style: italic; font-weight: 700; margin: 2px 0 0 0;">
                            <?= esc(!empty($ownerInfo['dept']) ? $ownerInfo['dept'] : ($isDocOpcr ? 'Office of the University President' : 'Office of the University President')) ?>
                        </p>
                    </div>

                    <!-- Right: Bagong Pilipinas Official Logo (MC 24, s. 2023 Compliance) -->
                    <div class="shrink-0 flex items-center justify-center">
                        <img src="<?= base_url('assets/images/bagong_pilipinas.png') ?>" alt="Bagong Pilipinas" class="w-16 h-16 sm:w-20 sm:h-20 object-contain drop-shadow-xs" />
                    </div>
                </div>

