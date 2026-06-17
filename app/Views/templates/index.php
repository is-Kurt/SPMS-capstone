<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?= view('components/header') ?>

<div class="p-8 max-w-7xl mx-auto flex flex-col gap-8 pb-20">
    
    <div class="flex items-center justify-between shrink-0">
        <div>
            <h1 class="text-3xl font-black tracking-tight text-text">Form Templates</h1>
            <p class="text-sm text-text-muted mt-1 font-medium italic">Design and manage standardized IPCR, DPCR, and OPCR templates.</p>
        </div>
        
        <a href="<?= site_url('templates/create') ?>" class="bg-accent hover:bg-accent-hover text-white text-sm font-bold py-3 px-6 rounded-xl shadow-lg shadow-accent/20 transition-all active:scale-[0.98] flex items-center gap-2">
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

    <div class="bg-surface border border-surface-border rounded-2xl shadow-sm overflow-hidden flex flex-col">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-zinc-50 dark:bg-zinc-800/90 backdrop-blur-md text-[10px] font-black uppercase tracking-widest text-text-muted border-b border-surface-border shadow-sm">
                    <tr>
                        <th class="px-6 py-4">Template Title</th>
                        <th class="px-6 py-4">Last Updated</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-border">
                    <?php if(empty($templates)): ?>
                        <tr>
                            <td colspan="3" class="px-6 py-12 text-center text-sm font-bold text-text-muted italic">
                                No templates found. Click "New Template" to build one.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($templates as $t): ?>                            
                            <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/20 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="text-sm font-bold text-text truncate"><?= esc($t['title']) ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-xs font-bold text-text-muted"><?= date('M d, Y - h:i A', strtotime($t['updated_at'])) ?></span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    
                                    <a href="<?= site_url('templates/edit/' . $t['id']) ?>" class="text-[10px] font-black uppercase tracking-widest px-3 py-1.5 rounded-lg text-accent hover:bg-accent/10 transition-colors mr-2">
                                        Edit
                                    </a>

                                    <?= form_open('templates/delete', ['class' => 'inline', 'onsubmit' => 'return confirm("Are you sure you want to delete this template?");']) ?>
                                        <input type="hidden" name="template_id" value="<?= $t['id'] ?>">
                                        <button type="submit" class="text-[10px] font-black uppercase tracking-widest px-3 py-1.5 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors cursor-pointer">
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