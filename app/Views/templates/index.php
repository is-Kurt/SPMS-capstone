<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?= view('components/header') ?>

<div class="p-4 md:p-8 max-w-7xl mx-auto flex flex-col gap-6 md:gap-8 pb-20">
    
    <div class="flex flex-col sm:flex-row sm:items-center justify-between shrink-0 gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-black tracking-tight text-text">Form Templates</h1>
            <p class="text-xs md:text-sm text-text-muted mt-1 font-medium italic">Design and manage standardized IPCR, DPCR, and OPCR templates.</p>
        </div>
        
        <a href="<?= site_url('templates/create') ?>" class="w-full sm:w-auto justify-center bg-accent hover:bg-accent-hover text-white text-sm font-bold py-3.5 sm:py-3 px-6 rounded-xl shadow-lg shadow-accent/20 transition-all active:scale-[0.98] flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            New Template
        </a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="px-6 py-4 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200 text-sm font-bold">
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="px-6 py-4 rounded-xl bg-red-50 text-red-600 border border-red-200 text-sm font-bold">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <div class="bg-transparent lg:bg-surface border-none lg:border border-surface-border rounded-none lg:rounded-2xl shadow-none lg:shadow-sm overflow-hidden flex flex-col">
        <div class="overflow-x-hidden w-full">
            
            <table class="w-full text-left border-collapse block lg:table">
                <thead class="hidden lg:table-header-group bg-zinc-50 dark:bg-zinc-800/90 backdrop-blur-md text-[10px] font-black uppercase tracking-widest text-text-muted border-b border-surface-border shadow-sm">
                    <tr>
                        <th class="px-6 py-4">Template Title</th>
                        <th class="px-6 py-4">Last Updated</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                
                <tbody class="block lg:table-row-group divide-y lg:divide-y-0 divide-transparent lg:divide-surface-border">
                    <?php if(empty($templates)): ?>
                        <tr class="block lg:table-row">
                            <td colspan="3" class="block lg:table-cell px-6 py-12 text-center text-sm font-bold text-text-muted italic bg-surface border border-surface-border rounded-xl lg:bg-transparent lg:border-none lg:rounded-none">
                                No templates found. Click "New Template" to build one.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($templates as $t): ?>                            
                            <tr class="block lg:table-row hover:bg-zinc-50/50 dark:hover:bg-zinc-800/20 transition-colors bg-surface border lg:border-none border-surface-border rounded-xl lg:rounded-none mb-3 lg:mb-0 p-4 lg:p-0 shadow-sm lg:shadow-none">
                                
                                <td class="block lg:table-cell px-0 lg:px-6 py-1 lg:py-4">
                                    <div class="flex flex-col min-w-0">
                                        <span class="text-sm font-bold text-text truncate mb-1 lg:mb-0"><?= esc($t['title']) ?></span>
                                        <span class="lg:hidden text-[10px] font-bold text-text-muted uppercase tracking-widest">
                                            Updated: <?= date('M d, Y - h:i A', strtotime($t['updated_at'])) ?>
                                        </span>
                                    </div>
                                </td>
                                
                                <td class="hidden lg:table-cell px-6 py-4">
                                    <span class="text-xs font-bold text-text-muted"><?= date('M d, Y - h:i A', strtotime($t['updated_at'])) ?></span>
                                </td>
                                
                                <td class="flex items-center justify-end px-0 lg:px-6 pt-3 pb-0 lg:py-4 text-right border-t border-surface-border lg:border-none mt-3 lg:mt-0 gap-2">
                                    
                                    <a href="<?= site_url('templates/edit/' . $t['id']) ?>" class="flex-1 lg:flex-none text-center text-[10px] font-black uppercase tracking-widest px-4 py-2.5 lg:px-3 lg:py-1.5 rounded-lg text-accent hover:bg-accent/10 transition-colors border border-accent/20 lg:border-transparent bg-accent/5 lg:bg-transparent">
                                        Edit
                                    </a>

                                    <?= form_open('templates/delete', ['class' => 'flex-1 lg:flex-none', 'onsubmit' => 'return confirm("Are you sure you want to delete this template?");']) ?>
                                        <input type="hidden" name="template_id" value="<?= $t['id'] ?>">
                                        <button type="submit" class="w-full text-[10px] font-black uppercase tracking-widest px-4 py-2.5 lg:px-3 lg:py-1.5 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors cursor-pointer border border-red-200 dark:border-red-500/20 lg:border-transparent bg-red-50 dark:bg-red-500/5 lg:bg-transparent">
                                            Delete
                                        </button>
                                    <?= form_close() ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            
        </div>
    </div>
</div>

<?= $this->endSection() ?>