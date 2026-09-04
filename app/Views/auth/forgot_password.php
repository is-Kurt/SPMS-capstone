<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="min-h-screen flex flex-col bg-gradient-to-br from-[#06442b] via-[#053823] to-[#042819] relative overflow-hidden">
    <?= view('components/govph_masthead') ?>

    <main class="flex-1 flex flex-col justify-center px-6 py-12 lg:px-8 relative">
    
    <div class="absolute top-0 right-0 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="mx-auto w-full max-w-md border border-emerald-800/30 p-8 sm:p-10 rounded-3xl bg-white dark:bg-[#0b1b13] shadow-2xl relative z-10">
        
        <div class="sm:mx-auto sm:w-full sm:max-w-sm flex flex-col items-center mb-6">
            <a href="<?= site_url('/') ?>" class="mb-3 hover:scale-105 transition-transform" title="Back to Home">
                <img src="<?= base_url('assets/images/spms_logo.png') ?>" alt="SPMS Logo" class="w-14 h-14 rounded-full object-contain shadow-md" />
            </a>
            <h2 class="text-center text-2xl font-heading font-black tracking-tight text-slate-900 dark:text-white">Forgot Password</h2>
            <p class="text-center text-xs text-slate-500 dark:text-slate-400 mt-1">Enter your email address and we'll send you a 6-digit code to reset your password.</p>
        </div>

        <?= form_open('password/send', ['class' => 'space-y-4']) ?>
            <div>
                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Email address</label>
                <div class="mt-1.5">
                    <input id="email" type="email" name="email" value="<?= old('email') ?>" required
                           class="w-full bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 rounded-xl px-4 py-3 text-sm focus:border-[#064e3b] focus:ring-1 focus:ring-[#064e3b] focus:outline-none text-slate-900 dark:text-white transition-all" />
                </div>
                <div class="min-h-3 pl-1">
                    <p class="text-danger-500 text-[10px] font-bold mt-1 uppercase tracking-wider"><?= session('error') ?? validation_show_error('email') ?></p>
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full bg-[#064e3b] hover:bg-[#085a3a] text-white font-bold py-3.5 rounded-xl cursor-pointer transition-all text-xs uppercase tracking-wider shadow-md active:scale-[0.98]">
                    Send Reset Code
                </button>
            </div>
            
            <div class="text-center mt-5 pt-4 border-t border-slate-100 dark:border-slate-800/80 text-xs font-bold">
                <a href="<?= site_url('login') ?>" class="text-slate-500 hover:text-[#064e3b] dark:hover:text-emerald-400 transition-colors">&larr; Back to Login</a>
            </div>
        <?= form_close() ?>
    </div>
</main>
</div>
<?= $this->endSection() ?>