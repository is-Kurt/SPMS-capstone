<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?= view('components/header') ?>

<div class="p-8 max-w-7xl mx-auto flex flex-col md:flex-row gap-8">
    
    <div class="w-full md:w-64 flex-shrink-0">
        <nav class="flex flex-col gap-1">
            <?php
            $navItems = [
                ['id' => 'all', 'label' => 'All Documents'],
                ['id' => 'locked', 'label' => 'Locked'],
                ['id' => 'pending', 'label' => 'Pending Review'],
                ['id' => 'unevaluated', 'label' => 'Unevaluated'],
                ['id' => 'evaluated', 'label' => 'Evaluated'],
            ];

            foreach ($navItems as $item):
                $isActive = ($filter === $item['id']);
                $count = $counts[$item['id']] ?? 0;
            ?>
                <a href="<?= site_url('submissions?docs=' . $item['id']) ?>" 
                   class="px-3 py-2 rounded-md text-sm font-medium transition-colors flex justify-between items-center 
                   <?= $isActive ? 'bg-highlight/10 text-highlight' : 'text-sub-text hover:bg-main-border/20 hover:text-foreground' ?>">
                    <span><?= $item['label'] ?></span>
                    <?php if ($count > 0): ?>
                        <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-main-border/10 border border-main-border/30 opacity-80"><?= $count ?></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>

    <div class="flex-1">
        <div class="flex justify-between items-center mb-6 px-1">
            <h1 class="text-2xl font-bold uppercase tracking-tight">
                <?= ($filter === 'all' || !$filter) ? "All Submissions" : str_replace('_', ' ', $filter) ?>
            </h1>
        </div>

        <div class="bg-card-bg border border-main-border rounded-lg overflow-hidden shadow-sm">
            <div class="grid grid-cols-12 gap-4 px-6 py-3 bg-main-border/10 border-b border-main-border text-xs font-semibold uppercase tracking-wider text-sub-text">
                <div class="col-span-6">Document Details</div>
                <div class="col-span-4">Evaluation Period</div>
                <div class="col-span-2 text-right">Status</div>
            </div>

            <?php if (empty($docs)): ?>
                <div class="p-20 text-center text-sub-text italic">
                    No documents found in this category.
                </div>
            <?php else: ?>
                <div class="divide-y divide-main-border">
                    <?php foreach ($docs as $doc): 
                        $isLocked = strtotime($doc['eval_date_start']) > time();
                        $isOverdue = strtotime($doc['eval_date_end']) < time() && !$doc['is_rated'];
                    ?>
                        <div class="grid grid-cols-12 gap-4 px-6 py-4 items-center hover:bg-main-border/5 transition-colors group">
                            
                            <div class="col-span-6 flex items-center gap-4">
                                <div class="<?= $isLocked ? 'bg-gray-100 text-gray-400' : 'bg-highlight/10 text-highlight' ?> p-2 rounded">
                                    <?php if ($isLocked): ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                    <?php else: ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <?php endif; ?>
                                </div>
                                <div class="flex flex-col">
                                    <a href="<?= $isLocked ? '#' : site_url('submission?Id=' . $doc['id']) ?>" 
                                       class="font-medium text-sm <?= $isLocked ? 'text-gray-400 cursor-not-allowed' : 'hover:text-highlight' ?> transition-colors truncate max-w-xs md:max-w-md">
                                        <?= esc($doc['title']) ?>
                                    </a>
                                    <span class="text-[10px] text-sub-text uppercase">IPCR Submission</span>
                                </div>
                            </div>

                            <div class="col-span-4 flex flex-col gap-0.5">
                                <div class="text-[11px] font-bold text-gray-600 dark:text-zinc-300">
                                    <?= date('m/d/y h:i A', strtotime($doc['eval_date_start'])) ?> — <?= date('m/d/y h:i A', strtotime($doc['eval_date_end'])) ?>
                                </div>
                                <div class="text-[10px] text-sub-text italic">
                                    Updated: <?= date('m/d/y h:i A', strtotime($doc['updated_at'])) ?>
                                </div>
                            </div>

                            <div class="col-span-2 text-right flex justify-end">
                                <?php if ($isLocked): ?>
                                    <span class="px-2 py-1 rounded text-[9px] font-bold bg-slate-100 text-slate-500 border border-slate-200">LOCKED</span>
                                <?php elseif ($doc['is_rated']): ?>
                                    <span class="px-2 py-1 rounded text-[9px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-200 uppercase">Evaluated</span>
                                <?php elseif ($isOverdue): ?>
                                    <span class="px-2 py-1 rounded text-[9px] font-bold bg-red-50 text-red-600 border border-red-200 uppercase">Unevaluated</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 rounded text-[9px] font-bold bg-amber-50 text-amber-600 border border-amber-200 uppercase">Pending</span>
                                <?php endif; ?>
                            </div>

                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>