<div id="tab-content-weights" class="tab-content <?= $activeTab === 'weights' ? 'flex' : 'hidden' ?> flex-col lg:absolute lg:inset-0 bg-transparent lg:bg-surface overflow-y-auto custom-scrollbar p-4 md:p-6 lg:p-8">

    <div class="max-w-5xl mx-auto w-full flex flex-col gap-6">
        
        <!-- Header / Guideline Banner -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-5 rounded-2xl bg-zinc-50 dark:bg-zinc-800/50 border border-surface-border">
            <div class="flex items-start gap-3.5">
                <div class="p-2.5 rounded-xl bg-accent/10 text-accent shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-text">Default Function Percentage Weights</h2>
                    <p class="text-xs text-text-muted mt-1 leading-relaxed">
                        Configure institutional baseline percentage weights for <strong>OPCR</strong>, <strong>DPCR</strong>, and <strong>IPCR</strong>. These percentages automatically prefill when new performance commitments are created.
                        Each document's category weights must sum to exactly <strong class="text-text">100%</strong> in accordance with Civil Service Commission (CSC MC No. 6, s. 2012) SPMS guidelines.
                    </p>
                </div>
            </div>
            <div class="shrink-0 flex items-center gap-2">
                <button type="button" onclick="resetToCscDefaults()" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-surface-border bg-surface hover:bg-zinc-100 dark:hover:bg-zinc-700 text-text-muted hover:text-text text-xs font-bold transition-colors shadow-xs cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Reset to CSC Standards</span>
                </button>
            </div>
        </div>

        <!-- Form for saving default weights -->
        <form id="form-default-weights" onsubmit="saveDefaultWeights(event)" class="flex flex-col gap-6">
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <?php 
                $docTypesConfig = [
                    'OPCR' => [
                        'title'        => 'OPCR',
                        'subtitle'     => 'Office Performance Commitment',
                        'target'       => 'Executive & Head of Office',
                        'color'        => 'emerald',
                        'default_core' => 70,
                        'default_strat'=> 20,
                        'default_supp' => 10,
                        'description'  => 'Applied to University President, Vice Presidents, and Campus Directors.',
                    ],
                    'DPCR' => [
                        'title'        => 'DPCR',
                        'subtitle'     => 'Department Performance Commitment',
                        'target'       => 'Deans & Department Chairs',
                        'color'        => 'blue',
                        'default_core' => 70,
                        'default_strat'=> 15,
                        'default_supp' => 15,
                        'description'  => 'Applied to Academic Colleges, Academic Departments, and Division Units.',
                    ],
                    'IPCR' => [
                        'title'        => 'IPCR',
                        'subtitle'     => 'Individual Performance Commitment',
                        'target'       => 'Faculty & Regular Staff',
                        'color'        => 'purple',
                        'default_core' => 70,
                        'default_strat'=> 15,
                        'default_supp' => 15,
                        'description'  => 'Applied to individual faculty members and permanent administrative personnel.',
                    ],
                ];
                ?>

                <?php foreach ($docTypesConfig as $typeKey => $cfg): 
                    $w = $defaultWeights[$typeKey] ?? [];
                    $coreVal  = isset($w['core_weight']) ? (float)$w['core_weight'] : $cfg['default_core'];
                    $stratVal = isset($w['strategic_weight']) ? (float)$w['strategic_weight'] : $cfg['default_strat'];
                    $suppVal  = isset($w['support_weight']) ? (float)$w['support_weight'] : $cfg['default_supp'];
                    $curTotal = round($coreVal + $stratVal + $suppVal, 2);
                ?>
                <div class="flex flex-col rounded-2xl bg-surface border border-surface-border shadow-sm p-5 relative overflow-hidden transition-all duration-200 hover:border-accent/40" data-doc-card="<?= $typeKey ?>">
                    
                    <!-- Card Top Header -->
                    <div class="flex items-start justify-between gap-3 pb-5 border-b border-surface-border">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-black bg-accent/10 text-accent tracking-wider uppercase"><?= $cfg['title'] ?></span>
                                <span class="text-xs font-bold text-text-muted"><?= $cfg['target'] ?></span>
                            </div>
                            <h3 class="text-sm font-black text-text mt-2"><?= $cfg['subtitle'] ?></h3>
                            <p class="text-[11px] text-text-muted mt-0.5 leading-snug"><?= $cfg['description'] ?></p>
                        </div>
                    </div>

                    <!-- Input Fields -->
                    <div class="flex flex-col gap-5 py-6 my-auto w-fit mx-auto">
                        
                        <!-- Core Functions -->
                        <div>
                            <label for="input-<?= strtolower($typeKey) ?>-core" class="text-xs font-bold text-text flex items-center gap-2 mb-2 cursor-pointer">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block shrink-0"></span>
                                <span>Core Functions</span>
                            </label>
                            <div class="flex items-center gap-2.5">
                                <input type="number" 
                                       step="1" 
                                       min="0" 
                                       max="100" 
                                       name="weights[<?= $typeKey ?>][core_weight]" 
                                       id="input-<?= strtolower($typeKey) ?>-core" 
                                       value="<?= $coreVal ?>" 
                                       required 
                                       data-type="<?= $typeKey ?>" 
                                       data-category="core" 
                                       oninput="calculateCardTotal('<?= $typeKey ?>')" 
                                       class="w-28 bg-zinc-50 dark:bg-zinc-800 border border-surface-border rounded-xl px-3 py-2 text-sm font-bold text-text text-center focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all shadow-xs">
                                <span class="text-xs font-bold text-text-muted">%</span>
                            </div>
                        </div>

                        <!-- Strategic Functions -->
                        <div>
                            <label for="input-<?= strtolower($typeKey) ?>-strategic" class="text-xs font-bold text-text flex items-center gap-2 mb-2 cursor-pointer">
                                <span class="w-2 h-2 rounded-full bg-blue-500 inline-block shrink-0"></span>
                                <span>Strategic Functions</span>
                            </label>
                            <div class="flex items-center gap-2.5">
                                <input type="number" 
                                       step="1" 
                                       min="0" 
                                       max="100" 
                                       name="weights[<?= $typeKey ?>][strategic_weight]" 
                                       id="input-<?= strtolower($typeKey) ?>-strategic" 
                                       value="<?= $stratVal ?>" 
                                       required 
                                       data-type="<?= $typeKey ?>" 
                                       data-category="strategic" 
                                       oninput="calculateCardTotal('<?= $typeKey ?>')" 
                                       class="w-28 bg-zinc-50 dark:bg-zinc-800 border border-surface-border rounded-xl px-3 py-2 text-sm font-bold text-text text-center focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all shadow-xs">
                                <span class="text-xs font-bold text-text-muted">%</span>
                            </div>
                        </div>

                        <!-- Support Functions -->
                        <div>
                            <label for="input-<?= strtolower($typeKey) ?>-support" class="text-xs font-bold text-text flex items-center gap-2 mb-2 cursor-pointer">
                                <span class="w-2 h-2 rounded-full bg-amber-500 inline-block shrink-0"></span>
                                <span>Support Functions</span>
                            </label>
                            <div class="flex items-center gap-2.5">
                                <input type="number" 
                                       step="1" 
                                       min="0" 
                                       max="100" 
                                       name="weights[<?= $typeKey ?>][support_weight]" 
                                       id="input-<?= strtolower($typeKey) ?>-support" 
                                       value="<?= $suppVal ?>" 
                                       required 
                                       data-type="<?= $typeKey ?>" 
                                       data-category="support" 
                                       oninput="calculateCardTotal('<?= $typeKey ?>')" 
                                       class="w-28 bg-zinc-50 dark:bg-zinc-800 border border-surface-border rounded-xl px-3 py-2 text-sm font-bold text-text text-center focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all shadow-xs">
                                <span class="text-xs font-bold text-text-muted">%</span>
                            </div>
                        </div>

                    </div>

                    <!-- Live Total Status Badge -->
                    <div class="mt-auto pt-5 border-t border-surface-border flex items-center justify-between">
                        <span class="text-xs font-bold text-text-muted">Total Sum:</span>
                        <div id="badge-total-<?= $typeKey ?>" class="px-2.5 py-1 rounded-lg text-xs font-black transition-colors <?= $curTotal == 100 ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-rose-500/10 text-rose-600 dark:text-rose-400' ?>">
                            <span id="text-total-<?= $typeKey ?>"><?= $curTotal ?></span>% <?= $curTotal == 100 ? '✓' : '(Must be 100%)' ?>
                        </div>
                    </div>

                </div>
                <?php endforeach; ?>

            </div>

            <!-- IPERF Advisory Callout -->
            <div class="p-4 rounded-xl bg-surface border border-surface-border/70 flex items-start gap-3">
                <div class="p-1.5 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-text-muted shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="text-xs text-text-muted leading-relaxed">
                    <strong class="text-text font-bold">Note on IPERF:</strong> The Individual Performance Evaluation Rating Form (IPERF) is designed exclusively for Contract of Service (COS) and Job Order (JO) personnel. Under CSC guidelines, IPERF evaluates direct Office PPAs / Expected Outputs (100% Core) without percentage split across categories.
                </div>
            </div>

            <!-- Bottom Actions -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-surface-border">
                <div id="weights-status-message" class="text-xs font-bold text-text-muted">
                    Changes take effect immediately for newly created documents.
                </div>
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <button type="submit" id="btn-save-weights" class="w-full sm:w-auto flex items-center justify-center gap-2 bg-accent hover:bg-accent-hover text-black px-6 py-2.5 rounded-xl text-xs font-black transition-all shadow-sm shadow-accent/20 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Save Default Weights</span>
                    </button>
                </div>
            </div>

        </form>

    </div>

</div>

