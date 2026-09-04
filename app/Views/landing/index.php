<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPMS - Strategic Performance Management System | Benguet State University</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= base_url('assets/images/spms_logo.png') ?>">
    <link rel="shortcut icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('assets/images/spms_logo.png') ?>">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/main/style.css') ?>">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-heading { font-family: 'Outfit', sans-serif; }
        .hero-pattern {
            background-image: radial-gradient(rgba(16, 185, 129, 0.15) 1px, transparent 1px);
            background-size: 28px 28px;
        }
        .form-slide {
            position: absolute;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            will-change: transform, opacity;
        }
        .form-slide.active {
            opacity: 1;
            transform: translateX(0) scale(1.03);
            z-index: 20;
            pointer-events: auto;
        }
        .form-slide.prev {
            opacity: 0.35;
            transform: translateX(-42%) scale(0.88);
            z-index: 10;
            cursor: pointer;
            pointer-events: auto;
        }
        .form-slide.next {
            opacity: 0.35;
            transform: translateX(42%) scale(0.88);
            z-index: 10;
            cursor: pointer;
            pointer-events: auto;
        }
        .form-slide.hidden-slide {
            opacity: 0;
            transform: translateX(0) scale(0.7);
            z-index: 0;
            pointer-events: none;
        }
        @media (max-width: 768px) {
            .form-slide.prev {
                transform: translateX(-100%) scale(0.85);
                opacity: 0;
                pointer-events: none;
            }
            .form-slide.next {
                transform: translateX(100%) scale(0.85);
                opacity: 0;
                pointer-events: none;
            }
        }

        @keyframes heroCardReveal {
            0% {
                opacity: 0;
                transform: scale(0.92) translateY(14px);
                box-shadow: 0 0 0 0 rgba(251, 191, 36, 0.7);
            }
            50% {
                box-shadow: 0 0 35px 10px rgba(251, 191, 36, 0.45);
            }
            100% {
                opacity: 1;
                transform: scale(1) translateY(0);
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            }
        }
        .animate-card-reveal {
            animation: heroCardReveal 0.45s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-800 antialiased selection:bg-[#064e3b] selection:text-white">

    <?= view('components/govph_masthead') ?>

    <!-- 1. HERO SECTION WITH SEAMLESS HEADER -->
    <div class="relative bg-gradient-to-br from-[#06442b] via-[#053823] to-[#042819] text-white overflow-hidden">
        
        <!-- Subtle Pattern Overlay -->
        <div class="absolute inset-0 hero-pattern opacity-40 pointer-events-none"></div>
        <div class="absolute top-0 right-0 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <!-- SEAMLESS NAVBAR -->
        <header class="relative z-50 border-b border-emerald-800/40 bg-[#06442b]/60 backdrop-blur-md">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-20">
                    
                    <!-- University Brand -->
                    <a href="<?= site_url('/') ?>" class="flex items-center gap-3.5 group">
                        <img src="<?= base_url('assets/images/spms_logo.png') ?>" alt="SPMS Logo" class="w-11 h-11 rounded-full object-contain shrink-0 group-hover:scale-105 transition-transform" />
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-heading font-black text-xl text-white tracking-tight">SPMS</span>
                                <span class="text-[9px] font-black uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 px-2 py-0.5 rounded-full">
                                    BSU
                                </span>
                            </div>
                            <p class="text-xs font-medium text-emerald-200/80">Benguet State University</p>
                        </div>
                    </a>

                    <!-- Nav Links -->
                    <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-emerald-100/90">
                        <a href="#overview" class="hover:text-white transition-colors py-1">Overview</a>
                        <a href="#forms" class="hover:text-white transition-colors py-1">Types of Forms</a>
                        <a href="#cycle" class="hover:text-white transition-colors py-1">4-Stage Cycle</a>
                        <a href="#guidelines" class="hover:text-white transition-colors py-1">Governance</a>
                    </nav>

                    <!-- Actions -->
                    <div class="flex items-center gap-3">
                        <?php if ($isLoggedIn): ?>
                            <a href="<?= site_url('folders') ?>" class="bg-amber-400 hover:bg-amber-300 text-emerald-950 font-bold text-xs uppercase tracking-wider px-5 py-2.5 rounded-xl shadow-md transition-all">
                                Go to Workspace
                            </a>
                        <?php else: ?>
                            <button id="nav-login-btn" type="button" onclick="toggleHeroLogin(true)" class="border border-emerald-400/40 hover:bg-white hover:text-emerald-950 text-white font-bold text-xs uppercase tracking-wider px-6 py-2.5 rounded-xl shadow-xs transition-all cursor-pointer">
                                Log in
                            </button>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </header>

        <!-- HERO CONTENT -->
        <section id="overview" class="relative z-10 py-16 lg:py-24 scroll-mt-20">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
                
                <!-- Left: Headline & Actions -->
                <div class="lg:col-span-7 flex flex-col items-start">
                    
                    <!-- Institutional Tag -->
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-900/60 border border-emerald-400/30 text-emerald-300 text-xs font-bold mb-6 shadow-sm">
                        <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                        <span>Benguet State University • Performance Cycle</span>
                    </div>

                    <h1 class="text-3xl sm:text-5xl lg:text-6xl font-heading font-black tracking-tight leading-[1.15] text-white mb-6">
                        Advancing Academic Excellence Through 
                        <span class="text-amber-300 underline decoration-amber-400/40 decoration-wavy">Transparent</span> Performance.
                    </h1>

                    <p class="text-base sm:text-lg text-emerald-100/90 font-normal leading-relaxed mb-8 max-w-xl">
                        A unified, paperless platform for BSU faculty and personnel to plan commitments, upload verifiable proofs, and calibrate performance ratings with integrity.
                    </p>

                    <div class="flex flex-wrap items-center gap-4 w-full sm:w-auto mb-10">
                        <?php if ($isLoggedIn): ?>
                            <a href="<?= site_url('folders') ?>" class="w-full sm:w-auto bg-amber-400 hover:bg-amber-300 text-emerald-950 font-bold text-xs uppercase tracking-wider px-8 py-4 rounded-xl shadow-lg transition-transform active:scale-95 text-center flex items-center justify-center">
                                <span>Access Performance Folders</span>
                            </a>
                        <?php else: ?>
                            <button type="button" onclick="toggleHeroLogin(true)" class="w-full sm:w-auto bg-amber-400 hover:bg-amber-300 text-emerald-950 font-bold text-xs uppercase tracking-wider px-8 py-4 rounded-xl shadow-lg transition-transform active:scale-95 text-center flex items-center justify-center cursor-pointer">
                                <span>Access Portal</span>
                            </button>
                        <?php endif; ?>

                        <a href="#cycle" class="w-full sm:w-auto border border-emerald-400/40 bg-emerald-950/40 hover:bg-emerald-900/40 text-emerald-100 font-bold text-xs uppercase tracking-wider px-6 py-4 rounded-xl transition-colors text-center flex items-center justify-center gap-2">
                            <span>Explore 4-Stage Cycle</span>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </a>
                    </div>

                    <!-- Trust indicators -->
                    <div class="flex items-center gap-6 pt-6 border-t border-emerald-800/60 text-xs text-emerald-200">
                        <div class="flex items-center gap-2">
                            <span class="text-amber-400 font-black text-sm">&bull;</span>
                            <span>100% Digital Submission</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-amber-400 font-black text-sm">&bull;</span>
                            <span>CSC Compliant</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-amber-400 font-black text-sm">&bull;</span>
                            <span>TWG Calibrated</span>
                        </div>
                    </div>

                </div>

                <!-- Right: High-Fidelity Portfolio Showcase & Interactive Login Card -->
                <div class="lg:col-span-5 relative">
                    
                    <!-- 1. Portfolio Showcase Card (Active by Default) -->
                    <div id="hero-portfolio-card" class="bg-white rounded-3xl p-6 sm:p-7 shadow-2xl text-slate-800 border-4 border-emerald-800/20 relative transition-all duration-300">
                        
                        <!-- Top Header -->
                        <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                            <div class="flex items-center gap-3">
                                <img src="<?= base_url('assets/images/spms_logo.png') ?>" alt="Logo" class="w-9 h-9 rounded-full object-contain shrink-0" />
                                <div>
                                    <h3 class="text-xs font-bold text-slate-900">Individual Performance Portfolio</h3>
                                    <p class="text-[10px] text-slate-500">IPCR Target Period: FY 2026</p>
                                </div>
                            </div>
                            <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded-full">
                                Verified
                            </span>
                        </div>

                        <!-- Highlights Breakdown -->
                        <div class="space-y-3 mb-5">
                            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100 text-xs">
                                <div>
                                    <span class="font-bold text-slate-800">Core Mandated Functions</span>
                                    <p class="text-[10px] text-slate-500">Instruction & Curriculum Delivery</p>
                                </div>
                                <span class="font-bold text-emerald-700 bg-white px-2 py-1 rounded-lg border border-slate-200">
                                    4.95 / 5.0
                                </span>
                            </div>

                            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100 text-xs">
                                <div>
                                    <span class="font-bold text-slate-800">Strategic Research Outputs</span>
                                    <p class="text-[10px] text-slate-500">Publications & Extension Works</p>
                                </div>
                                <span class="font-bold text-teal-700 bg-white px-2 py-1 rounded-lg border border-slate-200">
                                    4.88 / 5.0
                                </span>
                            </div>

                            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100 text-xs">
                                <div>
                                    <span class="font-bold text-slate-800">Support & Administrative</span>
                                    <p class="text-[10px] text-slate-500">Committee Assignments & Service</p>
                                </div>
                                <span class="font-bold text-amber-700 bg-white px-2 py-1 rounded-lg border border-slate-200">
                                    5.00 / 5.0
                                </span>
                            </div>
                        </div>

                        <!-- Bottom Final Score Bar -->
                        <div class="p-4 rounded-2xl bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-100 flex items-center justify-between">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Overall Final Rating</span>
                                <div class="text-xl font-heading font-black text-[#064e3b]">4.94 / 5.00</div>
                            </div>
                            <span class="text-xs font-black uppercase tracking-wider bg-emerald-600 text-white px-3 py-1.5 rounded-xl shadow-xs">
                                Outstanding
                            </span>
                        </div>

                        <?php if (!$isLoggedIn): ?>
                            <button type="button" onclick="toggleHeroLogin(true)" class="w-full mt-3 py-2 text-xs font-bold text-emerald-800 hover:text-emerald-950 bg-emerald-50 hover:bg-emerald-100/80 border border-emerald-200/80 rounded-xl transition-colors flex items-center justify-center cursor-pointer">
                                <span>Log in to your Performance Portfolio</span>
                            </button>
                        <?php endif; ?>

                    </div>

                    <!-- 2. Embedded Interactive Login Card -->
                    <div id="hero-login-card" class="hidden bg-white rounded-3xl p-6 sm:p-7 shadow-2xl text-slate-800 border-4 border-amber-400 ring-4 ring-amber-400/20 relative transition-all duration-300">
                        
                        <!-- Top Header -->
                        <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                            <div class="flex items-center gap-3">
                                <img src="<?= base_url('assets/images/spms_logo.png') ?>" alt="Logo" class="w-10 h-10 rounded-full object-contain shrink-0" />
                                <div>
                                    <h3 class="text-sm font-bold text-slate-900">SPMS Portal Access</h3>
                                    <p class="text-[11px] text-slate-500">Benguet State University</p>
                                </div>
                            </div>
                            <button type="button" onclick="toggleHeroLogin(false)" class="text-slate-400 hover:text-slate-700 text-xs font-bold px-2.5 py-1 rounded-lg hover:bg-slate-100 transition-colors cursor-pointer flex items-center gap-1" title="Back to Portfolio Showcase">
                                <span class="text-base leading-none">&times;</span>
                                <span class="text-[10px] uppercase font-bold tracking-wider">Close</span>
                            </button>
                        </div>
                        <?= form_open('login', ['id' => 'hero-login-form', 'class' => 'space-y-3']) ?>
                            <?php if (isset($_GET['logged_out']) && $_GET['logged_out'] == '1' && !session('errors.error') && !session('error')): ?>
                                <div class="p-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-[#064e3b] text-xs font-bold mb-3 flex items-center gap-2">
                                    <svg class="w-4 h-4 shrink-0 text-[#064e3b]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>You have signed out successfully.</span>
                                </div>
                            <?php endif; ?>

                            <?php if (session('errors.error') || session('error')): ?>
                                <div class="p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold">
                                    <?= esc(session('errors.error') ?? session('error')) ?>
                                </div>
                            <?php endif; ?>

                            <div>
                                <label for="hero-email" class="block text-[10px] font-bold uppercase tracking-wider text-slate-700">Email Address</label>
                                <div class="mt-1">
                                    <input id="hero-email" type="email" name="email" required placeholder="user@bsu.edu.ph"
                                           value="<?= esc(old('email', '')) ?>"
                                           class="w-full bg-slate-50 border-2 border-slate-200 focus:border-[#064e3b] rounded-xl px-3.5 py-2.5 text-xs focus:ring-2 focus:ring-[#064e3b]/20 focus:outline-none text-slate-900 transition-all placeholder:text-slate-400 font-medium" />
                                </div>
                                <?php if (validation_show_error('email')): ?>
                                    <p class="text-rose-600 text-[10px] font-bold mt-1"><?= validation_show_error('email') ?></p>
                                <?php endif; ?>
                            </div>

                            <div>
                                <div class="flex items-center justify-between">
                                    <label for="hero-password" class="block text-[10px] font-bold uppercase tracking-wider text-slate-700">Password</label>
                                    <a href="<?= site_url('password/forgot') ?>" class="text-[10px] font-bold text-[#064e3b] hover:underline">Forgot password?</a>
                                </div>
                                <div class="mt-1">
                                    <input id="hero-password" type="password" name="password" required placeholder="••••••••"
                                           class="w-full bg-slate-50 border-2 border-slate-200 focus:border-[#064e3b] rounded-xl px-3.5 py-2.5 text-xs focus:ring-2 focus:ring-[#064e3b]/20 focus:outline-none text-slate-900 transition-all placeholder:text-slate-400 font-medium" />
                                </div>
                                <?php if (validation_show_error('password')): ?>
                                    <p class="text-rose-600 text-[10px] font-bold mt-1"><?= validation_show_error('password') ?></p>
                                <?php endif; ?>
                            </div>

                            <div class="flex items-center justify-between pt-1">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input id="hero-remember-me" name="remember-me" type="checkbox" class="w-3.5 h-3.5 rounded border-slate-300 text-[#064e3b] focus:ring-[#064e3b]" />
                                    <span class="text-[11px] font-medium text-slate-600">Remember me</span>
                                </label>
                            </div>

                            <div class="pt-1.5">
                                <button type="submit" class="w-full bg-[#064e3b] hover:bg-[#085a3a] text-white font-bold py-3.5 rounded-xl cursor-pointer transition-all text-xs uppercase tracking-wider shadow-md active:scale-[0.98] flex items-center justify-center gap-2">
                                    <span>Log in</span>
                                </button>
                            </div>

                            <div class="pt-1 text-center">
                                <p class="text-[10px] text-slate-400">
                                    Benguet State University • SPMS
                                </p>
                            </div>
                        <?= form_close() ?>

                    </div>

                </div>

            </div>
        </div>
    </section>
    </div>


    <!-- 3. TYPES OF FORMS (Interactive Card Carousel) -->
    <section id="forms" class="py-24 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 scroll-mt-20">
        
        <div class="text-center max-w-2xl mx-auto mb-14">
            <span class="text-xs font-bold uppercase tracking-wider text-[#064e3b] bg-emerald-50 border border-emerald-200 px-3.5 py-1.5 rounded-full">
                TYPES OF FORMS
            </span>
            <h2 class="text-3xl sm:text-4xl font-heading font-black text-slate-900 tracking-tight mt-4 mb-3">
                BSU Performance Instruments
            </h2>
            <p class="text-sm text-slate-500">
                Official evaluation instruments designated for institutional offices, permanent personnel, and contract-of-service appointments.
            </p>
        </div>

        <!-- Carousel Container with Side Peeking Cards -->
        <div class="relative bg-slate-100/70 border border-slate-200/80 rounded-3xl p-6 sm:p-12 overflow-hidden">
            
            <!-- Left Arrow Button -->
            <button type="button" id="form-prev-btn" aria-label="Previous Form" class="absolute left-2 sm:left-6 top-1/2 -translate-y-1/2 z-30 w-11 h-11 sm:w-13 sm:h-13 rounded-full bg-[#064e3b] text-white shadow-xl flex items-center justify-center hover:bg-[#085a3a] active:scale-90 transition-all cursor-pointer">
                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
            </button>

            <!-- Right Arrow Button -->
            <button type="button" id="form-next-btn" aria-label="Next Form" class="absolute right-2 sm:right-6 top-1/2 -translate-y-1/2 z-30 w-11 h-11 sm:w-13 sm:h-13 rounded-full bg-[#064e3b] text-white shadow-xl flex items-center justify-center hover:bg-[#085a3a] active:scale-90 transition-all cursor-pointer">
                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </button>

            <!-- Cards Track / Wrapper -->
            <div class="relative min-h-[460px] sm:min-h-[420px] flex items-center justify-center px-8 sm:px-16">
                
                <!-- Card 0: OPCR -->
                <div class="form-slide transition-all duration-300 ease-out w-full max-w-xl mx-auto cursor-pointer" data-index="0">
                    <div class="bg-white border-t-4 border-t-blue-600 border-x border-b border-slate-200 rounded-2xl p-7 sm:p-9 shadow-md flex flex-col justify-between h-full">
                        <div>
                            <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                                <div>
                                    <span class="font-heading font-black text-3xl text-slate-900 tracking-tight">OPCR</span>
                                    <p class="text-xs font-semibold text-slate-500 mt-0.5">Office Performance Commitment and Review</p>
                                </div>
                                <span class="text-[10px] font-black uppercase tracking-wider bg-blue-50 text-blue-800 border border-blue-200 px-2.5 py-1 rounded-md">
                                    Institutional
                                </span>
                            </div>

                            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed mb-4">
                                The OPCR captures the overarching institutional targets and strategic deliverables for Benguet State University as a whole. Accomplished by executive leadership, it reflects the University Strategic Plan approved by the Board of Regents.
                            </p>

                            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 text-xs text-slate-500 leading-relaxed mb-6">
                                <span class="font-bold text-slate-700">Governance Linkage:</span> Serves as the apex benchmark from which all sector, college, and department DPCR deliverables are cascaded.
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-100 text-xs font-semibold text-blue-700">
                            Level: Executive Offices
                        </div>
                    </div>
                </div>

                <!-- Card 1: DPCR -->
                <div class="form-slide transition-all duration-300 ease-out w-full max-w-xl mx-auto cursor-pointer" data-index="1">
                    <div class="bg-white border-t-4 border-t-teal-600 border-x border-b border-slate-200 rounded-2xl p-7 sm:p-9 shadow-md flex flex-col justify-between h-full">
                        <div>
                            <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                                <div>
                                    <span class="font-heading font-black text-3xl text-slate-900 tracking-tight">DPCR</span>
                                    <p class="text-xs font-semibold text-slate-500 mt-0.5">Division / Department Performance Commitment</p>
                                </div>
                                <span class="text-[10px] font-black uppercase tracking-wider bg-teal-50 text-teal-800 border border-teal-200 px-2.5 py-1 rounded-md">
                                    Heads of Offices
                                </span>
                            </div>

                            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed mb-4">
                                The DPCR is accomplished by heads of offices and captures the targets and expected deliverables of an office for each rating period — whether at the Sector, College/Division, or Department/Office/Unit level.
                            </p>

                            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 text-xs text-slate-500 leading-relaxed mb-6">
                                <span class="font-bold text-slate-700">Governance Linkage:</span> It must be aligned with the University's Strategic Plan and the office's Operational Plan, and it serves as the primary reference for individual employees when preparing their own IPCRs or IPERFs.
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-100 text-xs font-semibold text-teal-700">
                            Level: Deans, Directors & Department Heads
                        </div>
                    </div>
                </div>

                <!-- Card 2: IPCR -->
                <div class="form-slide transition-all duration-300 ease-out w-full max-w-xl mx-auto cursor-pointer" data-index="2">
                    <div class="bg-white border-t-4 border-t-[#064e3b] border-x border-b border-slate-200 rounded-2xl p-7 sm:p-9 shadow-md flex flex-col justify-between h-full">
                        <div>
                            <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                                <div>
                                    <span class="font-heading font-black text-3xl text-slate-900 tracking-tight">IPCR</span>
                                    <p class="text-xs font-semibold text-slate-500 mt-0.5">Individual Performance Commitment and Review</p>
                                </div>
                                <span class="text-[10px] font-black uppercase tracking-wider bg-emerald-50 text-[#064e3b] border border-emerald-200 px-2.5 py-1 rounded-md">
                                    CSC Appointments
                                </span>
                            </div>

                            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed mb-4">
                                The IPCR is accomplished by University employees with CSC-validated appointments, including those under Permanent, Temporary, Coterminous, Contractual, Substitute, Provisional, and Casual status.
                            </p>

                            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 text-xs text-slate-500 leading-relaxed mb-6">
                                <span class="font-bold text-slate-700">Governance Linkage:</span> Outlines expected deliverables covering the main position functions while ensuring individual outputs contribute to the overall success of the office. The IPCR must be linked to the office's DPCR.
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-100 text-xs font-semibold text-emerald-700">
                            Level: Faculty & Regular Personnel
                        </div>
                    </div>
                </div>

                <!-- Card 3: IPERF -->
                <div class="form-slide transition-all duration-300 ease-out w-full max-w-xl mx-auto cursor-pointer" data-index="3">
                    <div class="bg-white border-t-4 border-t-amber-500 border-x border-b border-slate-200 rounded-2xl p-7 sm:p-9 shadow-md flex flex-col justify-between h-full">
                        <div>
                            <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                                <div>
                                    <span class="font-heading font-black text-3xl text-slate-900 tracking-tight">IPERF</span>
                                    <p class="text-xs font-semibold text-slate-500 mt-0.5">Individual Performance Evaluation & Review Form</p>
                                </div>
                                <span class="text-[10px] font-black uppercase tracking-wider bg-amber-50 text-amber-800 border border-amber-200 px-2.5 py-1 rounded-md">
                                    COS / JOP Status
                                </span>
                            </div>

                            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed mb-4">
                                The IPERF is accomplished by Contract of Service Personnel (COS/CSP), Job Order Personnel (JOP), and non-BSU Adjunct Faculty.
                            </p>

                            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 text-xs text-slate-500 leading-relaxed mb-6">
                                <span class="font-bold text-slate-700">Governance Linkage:</span> Unlike the IPCR, it is focused primarily on the core functions of the position occupied by the personnel, rather than on broader office-level commitments.
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-100 text-xs font-semibold text-amber-700">
                            Level: Contract of Service & Adjunct Faculty
                        </div>
                    </div>
                </div>

            </div>

            <!-- Form Indicator Pills / Jump Buttons -->
            <div class="flex items-center justify-center gap-2.5 mt-8 relative z-20">
                <button type="button" class="form-dot px-3 py-1 rounded-full text-xs font-bold transition-all cursor-pointer" data-target="0">OPCR</button>
                <button type="button" class="form-dot px-3 py-1 rounded-full text-xs font-bold transition-all cursor-pointer" data-target="1">DPCR</button>
                <button type="button" class="form-dot px-3 py-1 rounded-full text-xs font-bold transition-all cursor-pointer" data-target="2">IPCR</button>
                <button type="button" class="form-dot px-3 py-1 rounded-full text-xs font-bold transition-all cursor-pointer" data-target="3">IPERF</button>
            </div>

        </div>

    </section>

    <!-- 4. VISUAL 4-STAGE TIMELINE (Connected Steps) -->
    <section id="cycle" class="py-24 bg-[#053221] text-white scroll-mt-20 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="text-xs font-bold uppercase tracking-wider text-amber-300 bg-emerald-950/80 border border-amber-400/30 px-3.5 py-1.5 rounded-full">
                    CSC Performance Cycle
                </span>
                <h2 class="text-3xl sm:text-4xl font-heading font-black text-white tracking-tight mt-4 mb-3">
                    The 4-Stage Governance Process
                </h2>
                <p class="text-sm text-emerald-200">
                    Standardized national performance lifecycle prescribed by the Civil Service Commission.
                </p>
            </div>

            <!-- 4 Connected Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 relative">
                
                <!-- Stage 1 -->
                <div class="bg-[#08402b] border border-emerald-700/50 rounded-2xl p-6 shadow-md flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="w-8 h-8 rounded-lg bg-amber-400 text-emerald-950 font-black text-sm flex items-center justify-center">1</span>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-300">Phase 1</span>
                        </div>
                        <h4 class="text-lg font-heading font-bold text-white mb-2">Performance Planning</h4>
                        <p class="text-xs text-emerald-100/80 leading-relaxed font-normal">
                            OPCR, DPCR, and IPCR commitment targets with measurable success indicators set before period begins.
                        </p>
                    </div>
                </div>

                <!-- Stage 2 -->
                <div class="bg-[#08402b] border border-emerald-700/50 rounded-2xl p-6 shadow-md flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="w-8 h-8 rounded-lg bg-teal-400 text-emerald-950 font-black text-sm flex items-center justify-center">2</span>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-300">Phase 2</span>
                        </div>
                        <h4 class="text-lg font-heading font-bold text-white mb-2">Monitoring & Coaching</h4>
                        <p class="text-xs text-emerald-100/80 leading-relaxed font-normal">
                            Mid-term progress tracking, supervisor mentoring, and logging adjustments as conditions evolve.
                        </p>
                    </div>
                </div>

                <!-- Stage 3 -->
                <div class="bg-[#08402b] border border-emerald-700/50 rounded-2xl p-6 shadow-md flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="w-8 h-8 rounded-lg bg-emerald-400 text-emerald-950 font-black text-sm flex items-center justify-center">3</span>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-300">Phase 3</span>
                        </div>
                        <h4 class="text-lg font-heading font-bold text-white mb-2">Review & Evaluation</h4>
                        <p class="text-xs text-emerald-100/80 leading-relaxed font-normal">
                            Submission of accomplishment folders, proof verification, and Quality-Efficiency-Timeliness scoring.
                        </p>
                    </div>
                </div>

                <!-- Stage 4 -->
                <div class="bg-[#08402b] border border-emerald-700/50 rounded-2xl p-6 shadow-md flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="w-8 h-8 rounded-lg bg-amber-400 text-emerald-950 font-black text-sm flex items-center justify-center">4</span>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-300">Phase 4</span>
                        </div>
                        <h4 class="text-lg font-heading font-bold text-white mb-2">Reward & Development</h4>
                        <p class="text-xs text-emerald-100/80 leading-relaxed font-normal">
                            TWG and PMT calibration, performance bonus eligibility, and career development planning.
                        </p>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- 5. INSTITUTIONAL CALL TO ACTION -->
    <section id="guidelines" class="py-20 bg-slate-50 border-t border-slate-200 scroll-mt-20">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white border border-slate-200 rounded-3xl p-8 sm:p-12 shadow-sm flex flex-col md:flex-row items-center justify-between gap-8">
                <div class="flex items-start gap-5">
                    <img src="<?= base_url('assets/images/spms_logo.png') ?>" alt="Logo" class="w-14 h-14 rounded-full object-contain shrink-0 shadow-xs hidden sm:block" />
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-[#064e3b] bg-emerald-50 border border-emerald-200 px-3 py-1 rounded-full">
                            Official Access
                        </span>
                        <h3 class="text-2xl font-heading font-black text-slate-900 mt-3 mb-2">
                            Ready to Access Your Performance Folder?
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 max-w-lg leading-relaxed">
                            Log in using your authorized Benguet State University user account to start your target commitments or evaluate subordinates.
                        </p>
                    </div>
                </div>

                <div class="shrink-0 w-full sm:w-auto flex flex-col gap-2.5">
                    <?php if ($isLoggedIn): ?>
                        <a href="<?= site_url('folders') ?>" class="bg-[#064e3b] hover:bg-[#085a3a] text-white font-bold text-xs uppercase tracking-wider px-8 py-4 rounded-xl shadow-sm text-center transition-colors">
                            Go to Workspace
                        </a>
                    <?php else: ?>
                        <button type="button" onclick="toggleHeroLogin(true)" class="bg-[#064e3b] hover:bg-[#085a3a] text-white font-bold text-xs uppercase tracking-wider px-8 py-4 rounded-xl shadow-sm text-center transition-colors cursor-pointer">
                            Access SPMS Portal
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER (Inspired by reference mockup) -->
    <footer class="bg-[#02130b] border-t border-[#07301e] pt-16 pb-12 text-slate-400 antialiased">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-10 lg:gap-12">
                <!-- Col 1: Brand & Mission -->
                <div class="md:col-span-6 lg:col-span-5 flex flex-col">
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="w-7 h-7 rounded-lg bg-emerald-500/20 text-emerald-400 border border-emerald-400/30 flex items-center justify-center font-black text-xs shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <span class="font-heading font-black text-lg text-white tracking-tight">SPMS BSU</span>
                    </div>
                    <p class="text-xs leading-relaxed text-slate-400 max-w-sm">
                        Advancing institutional excellence and transparency across Benguet State University through accessible, standardized performance management, cascading, and strategic evaluation.
                    </p>
                </div>

                <!-- Col 2: About Us (Option B) -->
                <div class="md:col-span-3 lg:col-span-3 lg:col-start-7 flex flex-col">
                    <h4 class="text-xs font-bold text-white uppercase tracking-wider mb-4">About Us</h4>
                    <div class="flex flex-col space-y-2.5 text-xs text-slate-400">
                        <a href="https://www.bsu.edu.ph" target="_blank" rel="noopener" class="hover:text-emerald-400 transition-colors">Benguet State University</a>
                        <a href="#overview" class="hover:text-emerald-400 transition-colors">SPMS Overview & Mandate</a>
                        <a href="#guidelines" class="hover:text-emerald-400 transition-colors">Performance Management Team</a>
                        <a href="#guidelines" class="hover:text-emerald-400 transition-colors">CSC Guidelines & Policies</a>
                    </div>
                </div>

                <!-- Col 3: Support Links -->
                <div class="md:col-span-3 lg:col-span-3 flex flex-col">
                    <h4 class="text-xs font-bold text-white uppercase tracking-wider mb-4">Support</h4>
                    <div class="flex flex-col space-y-2.5 text-xs text-slate-400">
                        <a href="mailto:spms@bsu.edu.ph" class="hover:text-emerald-400 transition-colors">Contact PMT Support</a>
                        <button type="button" onclick="toggleHeroLogin(true)" class="text-left hover:text-emerald-400 transition-colors cursor-pointer">Faculty & Staff Portal</button>
                        <a href="https://www.csc.gov.ph" target="_blank" rel="noopener" class="hover:text-emerald-400 transition-colors">CSC Standards</a>
                        <a href="#guidelines" class="hover:text-emerald-400 transition-colors">Quality Assurance</a>
                    </div>
                </div>
            </div>

            <!-- Bottom Row: Copyright & Legal -->
            <div class="pt-8 mt-12 border-t border-[#07301e] flex flex-col sm:flex-row items-center justify-between gap-4 text-[11px] text-slate-500">
                <span>&copy; <?= date('Y') ?> Benguet State University SPMS. All rights reserved.</span>
                <div class="flex items-center gap-6">
                    <a href="#guidelines" class="hover:text-slate-300 transition-colors">Privacy Policy</a>
                    <a href="#guidelines" class="hover:text-slate-300 transition-colors">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- INTERACTIVE CAROUSEL SCRIPT -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const slides = document.querySelectorAll('.form-slide');
            const dots = document.querySelectorAll('.form-dot');
            const prevBtn = document.getElementById('form-prev-btn');
            const nextBtn = document.getElementById('form-next-btn');
            let currentIdx = 2; // Default to IPCR (index 2)

            function updateSlider(targetIdx) {
                currentIdx = (targetIdx + slides.length) % slides.length;

                slides.forEach((slide, i) => {
                    slide.classList.remove('active', 'prev', 'next', 'hidden-slide');

                    const diff = (i - currentIdx + slides.length) % slides.length;

                    if (diff === 0) {
                        slide.classList.add('active');
                    } else if (diff === 1) {
                        slide.classList.add('next');
                    } else if (diff === slides.length - 1) {
                        slide.classList.add('prev');
                    } else {
                        slide.classList.add('hidden-slide');
                    }
                });

                dots.forEach((dot, i) => {
                    if (i === currentIdx) {
                        dot.classList.add('bg-[#064e3b]', 'text-white', 'shadow-sm');
                        dot.classList.remove('bg-white', 'text-slate-600', 'border', 'border-slate-200');
                    } else {
                        dot.classList.remove('bg-[#064e3b]', 'text-white', 'shadow-sm');
                        dot.classList.add('bg-white', 'text-slate-600', 'border', 'border-slate-200');
                    }
                });
            }

            // Click side peeking cards directly to jump
            slides.forEach((slide) => {
                slide.addEventListener('click', function () {
                    const idx = parseInt(this.getAttribute('data-index'));
                    if (idx !== currentIdx) {
                        updateSlider(idx);
                    }
                });
            });

            // Arrow button navigation
            if (prevBtn) {
                prevBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    updateSlider(currentIdx - 1);
                });
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    updateSlider(currentIdx + 1);
                });
            }

            // Dot / pill jumps
            dots.forEach((dot) => {
                dot.addEventListener('click', function () {
                    const target = parseInt(this.getAttribute('data-target'));
                    updateSlider(target);
                });
            });

            // Initialize carousel
            updateSlider(currentIdx);
        });

        function toggleHeroLogin(showLogin) {
            const portfolioCard = document.getElementById('hero-portfolio-card');
            const loginCard = document.getElementById('hero-login-card');
            const navBtn = document.getElementById('nav-login-btn');
            if (!portfolioCard || !loginCard) return;

            if (showLogin) {
                portfolioCard.classList.add('hidden');
                loginCard.classList.remove('hidden');

                // Trigger smooth reveal animation
                loginCard.classList.remove('animate-card-reveal');
                void loginCard.offsetWidth; // Force CSS reflow
                loginCard.classList.add('animate-card-reveal');

                if (navBtn) {
                    navBtn.classList.remove('border-emerald-400/40', 'text-white', 'hover:bg-white', 'hover:text-emerald-950');
                    navBtn.classList.add('bg-amber-400', 'text-emerald-950', 'border-amber-400', 'shadow-lg');
                }

                loginCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                const emailInput = document.getElementById('hero-email');
                if (emailInput) {
                    setTimeout(() => {
                        emailInput.focus();
                    }, 250);
                }
            } else {
                loginCard.classList.add('hidden');
                portfolioCard.classList.remove('hidden');

                portfolioCard.classList.remove('animate-card-reveal');
                void portfolioCard.offsetWidth; // Force CSS reflow
                portfolioCard.classList.add('animate-card-reveal');

                if (navBtn) {
                    navBtn.classList.remove('bg-amber-400', 'text-emerald-950', 'border-amber-400', 'shadow-lg');
                    navBtn.classList.add('border-emerald-400/40', 'text-white', 'hover:bg-white', 'hover:text-emerald-950');
                }
            }
        }

        // Auto-open login card if user just logged out, navigated with ?login=1, or has validation errors
        document.addEventListener('DOMContentLoaded', function() {
            const params = new URLSearchParams(window.location.search);
            const hasAuthError = <?= (session('errors.error') || session('error') || validation_errors()) ? 'true' : 'false' ?>;
            const isLogout = params.get('logged_out') === '1';
            const isLoginReq = params.get('login') === '1' || window.location.hash === '#login';

            if (hasAuthError || isLogout || isLoginReq) {
                toggleHeroLogin(true);
            }

            // Remove logged_out from URL so future failed logins don't retain the logged_out state
            if (isLogout && window.history.replaceState) {
                params.delete('logged_out');
                const newQuery = params.toString() ? '?' + params.toString() : '';
                window.history.replaceState({}, document.title, window.location.pathname + newQuery + window.location.hash);
            }
        });
    </script>

    <script src="<?= base_url('assets/vendor/fingerprintjs/fp.min.js') ?>"></script>
    <script>
        if (window.FingerprintJS) {
            FingerprintJS.load()
                .then(fp => fp.get())
                .then(result => {
                    const form = document.getElementById('hero-login-form');
                    if (form) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'device_id';
                        input.value = result.visitorId;
                        form.appendChild(input);
                    }
                })
                .catch(() => {});
        }
    </script>

</body>
</html>
