<?php 
    use App\Enums\FolderStatus;
    
    $groupedGuides = $groupedGuides ?? [];
    $hasGuides = !empty($groupedGuides);
    $isArchived = !empty($isArchivedView) || !empty($activeFolder['deleted_at']);

    $folderModel = new \App\Models\DocumentFolderModel();
    $isLocked = $activeFolder ? $folderModel->isFolderLocked($activeFolder) : true;

    $displayName = session()->get('username');
    if (empty($displayName) || $displayName === 'Null username') {
        $fName = session()->get('first_name');
        $lName = session()->get('last_name');
        if ($fName || $lName) {
            $displayName = trim($fName . ' ' . ($lName ? substr($lName, 0, 1) . '.' : ''));
        } else {
            $displayName = 'User';
        }
    }
?>

<?php if (!$activeFolder): ?>
    <?php if (session()->get('role') === 'Admin'): ?>
        <button onclick="document.getElementById('btn-create-folder-modal').click()" class="flex-1 w-full border-2 border-dashed border-surface-border hover:border-emerald-500 rounded-2xl flex flex-col items-center justify-center text-center p-12 bg-surface/50 hover:bg-emerald-500/5 transition-all group cursor-pointer min-h-[400px]">
            <div class="inline-flex p-4 rounded-full bg-zinc-100 dark:bg-slate-800 text-text-muted group-hover:text-emerald-500 mb-4 shadow-sm transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-text group-hover:text-emerald-500 transition-colors mb-1">Create New Folder</h3>
            <p class="text-sm text-text-muted max-w-sm">Click here to start a new evaluation period and create the initial folder.</p>
        </button>
    <?php else: ?>
        <button onclick="toggleAppSidebar()" class="flex-1 w-full border-2 border-dashed border-surface-border hover:border-emerald-500 rounded-2xl flex flex-col items-center justify-center text-center p-12 bg-surface/50 hover:bg-emerald-500/5 transition-all group cursor-pointer lg:cursor-default min-h-[400px]">
            <div class="inline-flex p-4 rounded-full bg-zinc-100 dark:bg-slate-800 text-text-muted group-hover:text-emerald-500 mb-4 shadow-sm transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-text group-hover:text-emerald-500 transition-colors mb-1">Select a Folder</h3>
            <p class="text-sm text-text-muted max-w-sm">Choose an evaluation folder from the sidebar to view or manage its documents.</p>
        </button>
    <?php endif; ?>

<?php else: ?>
    
    <div class="flex flex-col lg:flex-row flex-1 lg:absolute lg:inset-0 lg:min-h-[650px] bg-transparent lg:gap-6 lg:pb-6">
        
        <!-- CENTER / MAIN CONTENT CONTAINER -->
        <div class="flex flex-col flex-1 min-w-0 min-h-0 relative lg:rounded-2xl shadow-xl overflow-hidden" style="background-color: #02160e !important; border: 1px solid #0d4a32 !important;">
            
            <!-- MOBILE ONLY DRAWER TOGGLE BAR -->
            <div class="lg:hidden px-6 py-3.5 border-b border-[#0d4a32] shrink-0 flex items-center justify-between bg-[#02170f]">
                <button onclick="toggleAppSidebar()" class="flex items-center gap-2 text-xs font-bold text-emerald-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <span><?= esc($activeFolder['title']) ?></span>
                </button>
            </div>

            <?php if (!empty($groupedGuides) && session()->get('role') !== 'Admin'): ?>
                <!-- TAB NAVIGATION (When superior guide exists) -->
                <div class="flex items-center gap-6 px-6 lg:px-8 border-b border-[#0d4a32] shrink-0 overflow-x-auto custom-scrollbar pt-2 bg-[#02170f]">
                    <button id="tab-btn-mine" class="tab-btn-doc whitespace-nowrap pb-3 text-sm font-bold border-b-2 border-emerald-500 text-slate-900 dark:text-white transition-all cursor-pointer flex items-center gap-2" onclick="switchDocTab('mine')">
                        Official Paper
                        <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300 text-[10px] font-extrabold tab-badge transition-colors"><?= count($myDocs) ?></span>
                    </button>

                    <button id="tab-btn-team" class="tab-btn-doc whitespace-nowrap pb-3 text-sm font-medium border-b-2 border-transparent text-slate-400 hover:text-slate-700 dark:hover:text-white transition-all cursor-pointer flex items-center gap-2" onclick="switchDocTab('team')">
                        Superior Reference Guide
                        <span class="px-2 py-0.5 rounded-full bg-slate-100 dark:bg-[#032316] text-slate-500 dark:text-[#94A3B8] text-[10px] font-extrabold tab-badge transition-colors"><?= array_sum(array_map(fn($g) => count($g['docs']), $groupedGuides)) ?></span>
                    </button>
                </div>
            <?php endif; ?>

            <!-- TAB CONTENTS AREA -->
            <div class="overflow-hidden flex flex-col flex-1 min-h-0 relative h-[calc(100dvh-320px)] lg:h-auto pb-32 lg:pb-0" style="background-color: #02160e;">
                
                <!-- 1. MY SUBMISSIONS TAB -->
                <div id="tab-content-mine" class="tab-content-doc flex-1 flex flex-col min-h-0 overflow-y-auto custom-scrollbar" style="background-color: #02160e; padding: 24px 28px;">
                    
                    <style>
                        /* BSU SPMS Official Paper Hub - Exact Mockup Styles */
                        .spms-hub-card {
                            background-color: #032115 !important;
                            border: 1px solid #0d4a32 !important;
                            border-radius: 16px !important;
                            padding: 32px 36px !important;
                            box-shadow: 0 16px 36px -10px rgba(0, 0, 0, 0.6) !important;
                            position: relative !important;
                            overflow: hidden !important;
                            width: 100% !important;
                        }

                        .spms-hub-kicker {
                            color: #f59e0b !important;
                            font-size: 11px !important;
                            font-weight: 800 !important;
                            letter-spacing: 0.08em !important;
                            text-transform: uppercase !important;
                            margin-bottom: 8px !important;
                            display: block !important;
                        }

                        .spms-hub-title {
                            color: #ffffff !important;
                            font-size: 24px !important;
                            font-weight: 800 !important;
                            letter-spacing: -0.01em !important;
                            line-height: 1.25 !important;
                            margin: 0 !important;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-title {
                                font-size: 27px !important;
                            }
                        }

                        .spms-hub-subtitle {
                            color: #5a8b73 !important;
                            font-size: 13px !important;
                            font-weight: 500 !important;
                            margin-top: 8px !important;
                            margin-bottom: 0 !important;
                        }

                        .spms-hub-subtitle-val {
                            color: #ffffff !important;
                            font-weight: 700 !important;
                        }

                        .spms-hub-status-pill {
                            background-color: #083321 !important;
                            border: 1px solid #115337 !important;
                            color: #f59e0b !important;
                            border-radius: 9999px !important;
                            padding: 6px 14px !important;
                            font-size: 11px !important;
                            font-weight: 800 !important;
                            letter-spacing: 0.06em !important;
                            text-transform: uppercase !important;
                            display: inline-flex !important;
                            align-items: center !important;
                            gap: 8px !important;
                            white-space: nowrap !important;
                        }

                        .spms-hub-status-dot {
                            width: 7px !important;
                            height: 7px !important;
                            border-radius: 9999px !important;
                            background-color: #f59e0b !important;
                            display: inline-block !important;
                        }

                        .spms-hub-tracker-label {
                            color: #5a8b73 !important;
                            font-size: 11px !important;
                            font-weight: 800 !important;
                            letter-spacing: 0.08em !important;
                            text-transform: uppercase !important;
                            margin-top: 28px !important;
                            margin-bottom: 16px !important;
                            display: block !important;
                        }

                        .spms-hub-stepper-row {
                            display: flex !important;
                            align-items: center !important;
                            gap: 12px !important;
                            overflow-x: auto !important;
                            padding-bottom: 6px !important;
                            margin-bottom: 28px !important;
                        }

                        .spms-hub-circle-active {
                            width: 26px !important;
                            height: 26px !important;
                            border-radius: 9999px !important;
                            background-color: #f59e0b !important;
                            border: none !important;
                            display: flex !important;
                            align-items: center !important;
                            justify-content: center !important;
                            flex-shrink: 0 !important;
                        }

                        .spms-hub-circle-active-dot {
                            width: 8px !important;
                            height: 8px !important;
                            border-radius: 9999px !important;
                            background-color: #032115 !important;
                        }

                        .spms-hub-circle-inactive {
                            width: 26px !important;
                            height: 26px !important;
                            border-radius: 9999px !important;
                            border: 1.5px solid #145235 !important;
                            background-color: transparent !important;
                            display: flex !important;
                            align-items: center !important;
                            justify-content: center !important;
                            flex-shrink: 0 !important;
                        }

                        .spms-hub-circle-completed {
                            width: 26px !important;
                            height: 26px !important;
                            border-radius: 9999px !important;
                            background-color: #10b981 !important;
                            border: none !important;
                            color: #ffffff !important;
                            display: flex !important;
                            align-items: center !important;
                            justify-content: center !important;
                            flex-shrink: 0 !important;
                        }

                        .spms-hub-step-text-active {
                            color: #ffffff !important;
                            font-size: 13px !important;
                            font-weight: 700 !important;
                            white-space: nowrap !important;
                            margin-left: 8px !important;
                        }

                        .spms-hub-step-text-inactive {
                            color: #5a8b73 !important;
                            font-size: 13px !important;
                            font-weight: 500 !important;
                            white-space: nowrap !important;
                            margin-left: 8px !important;
                        }

                        .spms-hub-stat-grid {
                            display: grid !important;
                            grid-template-columns: repeat(1, minmax(0, 1fr)) !important;
                            gap: 16px !important;
                            margin-bottom: 28px !important;
                        }
                        @media (min-width: 768px) {
                            .spms-hub-stat-grid {
                                grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
                            }
                        }

                        .spms-hub-stat-tile {
                            background-color: #062e1e !important;
                            border: 1px solid #0d4a32 !important;
                            border-radius: 14px !important;
                            padding: 20px 24px !important;
                            display: flex !important;
                            flex-direction: column !important;
                            justify-content: space-between !important;
                            min-height: 136px !important;
                        }

                        .spms-hub-tile-label {
                            color: #5a8b73 !important;
                            font-size: 12px !important;
                            font-weight: 600 !important;
                            margin: 0 !important;
                        }

                        .spms-hub-tile-value {
                            color: #ffffff !important;
                            font-size: 16px !important;
                            font-weight: 700 !important;
                            margin-top: 6px !important;
                            margin-bottom: 0 !important;
                        }

                        .spms-hub-window-pill {
                            background-color: #083b27 !important;
                            border: 1px solid #10593b !important;
                            color: #34d399 !important;
                            font-size: 11px !important;
                            font-weight: 700 !important;
                            padding: 4px 12px !important;
                            border-radius: 9999px !important;
                            display: inline-flex !important;
                            align-items: center !important;
                            gap: 7px !important;
                            width: fit-content !important;
                            margin-top: 14px !important;
                        }

                        .spms-hub-tile-dash {
                            width: 34px !important;
                            height: 3px !important;
                            background-color: #1e5a3e !important;
                            border-radius: 9999px !important;
                            margin-top: 20px !important;
                        }

                        .spms-hub-tile-btn {
                            background-color: #083b27 !important;
                            border: 1px solid #11593b !important;
                            color: #82c8a6 !important;
                            font-size: 12px !important;
                            font-weight: 600 !important;
                            padding: 6px 16px !important;
                            border-radius: 8px !important;
                            cursor: pointer !important;
                            width: fit-content !important;
                            margin-top: 14px !important;
                            text-decoration: none !important;
                            display: inline-block !important;
                            transition: all 0.15s ease !important;
                        }
                        .spms-hub-tile-btn:hover {
                            background-color: #0c4d33 !important;
                            color: #ffffff !important;
                            border-color: #176a46 !important;
                        }

                        .spms-hub-actions-bar {
                            display: flex !important;
                            align-items: center !important;
                            gap: 12px !important;
                            flex-wrap: wrap !important;
                        }

                        .spms-hub-btn-primary {
                            background-color: #f59e0b !important;
                            color: #000000 !important;
                            font-size: 12px !important;
                            font-weight: 900 !important;
                            letter-spacing: 0.05em !important;
                            text-transform: uppercase !important;
                            padding: 14px 26px !important;
                            border-radius: 12px !important;
                            border: none !important;
                            cursor: pointer !important;
                            text-decoration: none !important;
                            display: inline-flex !important;
                            align-items: center !important;
                            justify-content: center !important;
                            transition: all 0.15s ease !important;
                        }
                        .spms-hub-btn-primary:hover {
                            background-color: #e08e06 !important;
                            color: #000000 !important;
                        }

                        .spms-hub-btn-secondary {
                            background-color: #083b27 !important;
                            border: 1px solid #11593b !important;
                            color: #ffffff !important;
                            font-size: 12px !important;
                            font-weight: 700 !important;
                            padding: 13px 22px !important;
                            border-radius: 12px !important;
                            cursor: pointer !important;
                            text-decoration: none !important;
                            display: inline-flex !important;
                            align-items: center !important;
                            justify-content: center !important;
                            transition: all 0.15s ease !important;
                        }
                        .spms-hub-btn-secondary:hover {
                            background-color: #0c4d33 !important;
                            border-color: #176a46 !important;
                            color: #ffffff !important;
                        }

                        /* SPMS Modal Design System */
                        .spms-modal-backdrop {
                            position: fixed !important;
                            top: 0 !important;
                            left: 0 !important;
                            right: 0 !important;
                            bottom: 0 !important;
                            z-index: 9999 !important;
                            background-color: rgba(0, 0, 0, 0.75) !important;
                            backdrop-filter: blur(6px) !important;
                            -webkit-backdrop-filter: blur(6px) !important;
                            display: flex !important;
                            align-items: center !important;
                            justify-content: center !important;
                            padding: 16px !important;
                        }
                        .spms-modal-backdrop.hidden {
                            display: none !important;
                        }
                        .spms-modal-dialog {
                            background-color: #032115 !important;
                            border: 1px solid #0d4a32 !important;
                            border-radius: 16px !important;
                            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.85) !important;
                            color: #ffffff !important;
                            width: 100%;
                            max-height: 90vh !important;
                            display: flex !important;
                            flex-direction: column !important;
                            overflow: hidden !important;
                            position: relative !important;
                        }
                        .spms-modal-header {
                            background-color: #02170f !important;
                            border-bottom: 1px solid #0d4a32 !important;
                            padding: 20px 24px !important;
                            display: flex !important;
                            align-items: center !important;
                            justify-content: space-between !important;
                            flex-shrink: 0 !important;
                        }
                        .spms-modal-body {
                            background-color: #032115 !important;
                            padding: 24px !important;
                            overflow-y: auto !important;
                            flex: 1 1 auto !important;
                        }
                        .spms-modal-section {
                            background-color: #062e1e !important;
                            border: 1px solid #0d4a32 !important;
                            border-radius: 12px !important;
                            padding: 16px !important;
                            margin-bottom: 16px !important;
                        }
                        .spms-modal-card {
                            background-color: #083b27 !important;
                            border: 1px solid #10593b !important;
                            border-radius: 8px !important;
                            padding: 12px 14px !important;
                            color: #d1fae5 !important;
                        }
                        .spms-modal-footer {
                            background-color: #02170f !important;
                            border-top: 1px solid #0d4a32 !important;
                            padding: 16px 24px !important;
                            display: flex !important;
                            align-items: center !important;
                            justify-content: space-between !important;
                            flex-shrink: 0 !important;
                        }
                        .spms-modal-btn-close {
                            width: 32px !important;
                            height: 32px !important;
                            border-radius: 8px !important;
                            background-color: #083b27 !important;
                            border: 1px solid #11593b !important;
                            color: #94a3b8 !important;
                            display: flex !important;
                            align-items: center !important;
                            justify-content: center !important;
                            cursor: pointer !important;
                            transition: all 0.15s ease !important;
                        }
                        .spms-modal-btn-close:hover {
                            background-color: #0c4d33 !important;
                            color: #ffffff !important;
                            border-color: #176a46 !important;
                        }

                        /* Interactive Stepper Carousel Styles */
                        .spms-guide-tabs {
                            display: grid !important;
                            grid-template-columns: repeat(4, 1fr) !important;
                            gap: 8px !important;
                            margin-bottom: 20px !important;
                        }
                        @media (max-width: 640px) {
                            .spms-guide-tabs {
                                grid-template-columns: repeat(2, 1fr) !important;
                            }
                        }
                        .spms-guide-tab {
                            background-color: #062e1e !important;
                            border: 1px solid #0d4a32 !important;
                            border-radius: 10px !important;
                            padding: 10px 12px !important;
                            cursor: pointer !important;
                            display: flex !important;
                            align-items: center !important;
                            gap: 8px !important;
                            transition: all 0.15s ease !important;
                            text-align: left !important;
                        }
                        .spms-guide-tab:hover {
                            background-color: #083b27 !important;
                            border-color: #156643 !important;
                        }
                        .spms-guide-tab.active {
                            background-color: #083b27 !important;
                            border-color: #f59e0b !important;
                            box-shadow: 0 0 12px rgba(245, 158, 11, 0.25) !important;
                        }
                        .spms-guide-tab-badge {
                            width: 22px !important;
                            height: 22px !important;
                            border-radius: 9999px !important;
                            background-color: #032115 !important;
                            border: 1px solid #0d4a32 !important;
                            color: #82c8a6 !important;
                            font-size: 11px !important;
                            font-weight: 800 !important;
                            display: flex !important;
                            align-items: center !important;
                            justify-content: center !important;
                            flex-shrink: 0 !important;
                        }
                        .spms-guide-tab.active .spms-guide-tab-badge {
                            background-color: #f59e0b !important;
                            border-color: #f59e0b !important;
                            color: #000000 !important;
                        }
                        .spms-guide-tab-title {
                            font-size: 11px !important;
                            font-weight: 700 !important;
                            color: #94a3b8 !important;
                            line-height: 1.2 !important;
                        }
                        .spms-guide-tab.active .spms-guide-tab-title {
                            color: #ffffff !important;
                        }
                        .spms-guide-slide {
                            display: none;
                        }
                        .spms-guide-slide.active {
                            display: block;
                            animation: spmsFadeSlideIn 0.2s ease-out;
                        }
                        @keyframes spmsFadeSlideIn {
                            from { opacity: 0; transform: translateY(6px); }
                            to { opacity: 1; transform: translateY(0); }
                        }
                        .spms-guide-canvas {
                            background-color: #02170f !important;
                            border: 1px solid #0d4a32 !important;
                            border-radius: 12px !important;
                            padding: 18px !important;
                            margin-bottom: 16px !important;
                            position: relative !important;
                            overflow: hidden !important;
                        }
                        .spms-guide-instruction-card {
                            background-color: #062e1e !important;
                            border: 1px solid #0d4a32 !important;
                            border-radius: 12px !important;
                            padding: 16px 18px !important;
                        }
                    </style>

                    <?php 
                        $isUserAdmin = (session()->get('role') === 'Admin');
                        $isAdminRootCycle = $isUserAdmin && ($activeFolder['user_id'] == session()->get('user_id'));
                    ?>

                    <?php if ($isAdminRootCycle): ?>
                        <!-- ADMIN INSTITUTIONAL CYCLE MONITORING HUB CARD -->
                        <div class="spms-hub-card">
                            <!-- HEADER -->
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 24px;">
                                <div>
                                    <span class="spms-hub-kicker" style="color: #34d399 !important;">INSTITUTIONAL EVALUATION CYCLE</span>
                                    <h2 class="spms-hub-title"><?= esc($activeFolder['title'] ?: 'Untitled Cycle') ?></h2>
                                    <p class="spms-hub-subtitle">
                                        Cycle Coordinator: <span class="spms-hub-subtitle-val"><?= esc(session()->get('name') ?? 'System Administrator') ?></span> (Administrator)
                                    </p>
                                </div>
                                <div style="flex-shrink: 0;">
                                    <div class="spms-hub-status-pill" style="border-color: #059669 !important; color: #34d399 !important; background-color: #064e3b !important;">
                                        <span class="spms-hub-status-dot" style="background-color: #34d399 !important;"></span>
                                        <span>TARGET PHASE ACTIVE</span>
                                    </div>
                                </div>
                            </div>

                            <!-- TIMELINE INFORMATION BANNER -->
                            <div class="p-4 rounded-xl border border-emerald-700/60 bg-emerald-950/50 text-emerald-100 mb-6 flex items-start gap-3.5 shadow-sm">
                                <div class="w-9 h-9 rounded-lg bg-emerald-900/80 border border-emerald-600/50 flex items-center justify-center shrink-0 text-emerald-400 mt-0.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="text-xs leading-relaxed">
                                    <span class="font-extrabold text-emerald-300 block mb-0.5 text-sm">Target Setting Window is Active</span>
                                    As Administrator, you oversee the evaluation schedule and cascade targets downward. You have <strong>no personal evaluation paper to fill</strong>. Use the <strong>Cascade Management</strong> panel on the right to distribute this cycle to the <strong>Vice President (Executive Team)</strong> so institutional OPCR commitments can be formulated.
                                </div>
                            </div>

                            <!-- 3 STAT TILES -->
                            <div class="spms-hub-stat-grid">
                                <!-- 1. Target Period -->
                                <div class="spms-hub-stat-tile">
                                    <div>
                                        <div class="spms-hub-tile-label">Target Setting Period</div>
                                        <div class="spms-hub-tile-value">
                                            <?= !empty($activeFolder['ipcr_target_start']) && !empty($activeFolder['ipcr_target_end']) ? date('M j', strtotime($activeFolder['ipcr_target_start'])) . ' – ' . date('M j, Y', strtotime($activeFolder['ipcr_target_end'])) : 'Dates Not Configured' ?>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="spms-hub-window-pill">
                                            <span style="width: 6px; height: 6px; border-radius: 9999px; background-color: #34d399; display: inline-block;"></span>
                                            <span>TARGET PHASE</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- 2. Evaluation Period -->
                                <div class="spms-hub-stat-tile">
                                    <div>
                                        <div class="spms-hub-tile-label">Evaluation Period</div>
                                        <div class="spms-hub-tile-value">
                                            <?= !empty($activeFolder['ipcr_eval_start']) && !empty($activeFolder['ipcr_eval_end']) ? date('M j', strtotime($activeFolder['ipcr_eval_start'])) . ' – ' . date('M j, Y', strtotime($activeFolder['ipcr_eval_end'])) : 'Scheduled Following Targets' ?>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="spms-hub-tile-dash"></div>
                                    </div>
                                </div>

                                <!-- 3. Cascade Status -->
                                <?php $cascadedTeamId = $activeFolder['routing_preset_id'] ?? null; ?>
                                <div class="spms-hub-stat-tile">
                                    <div>
                                        <div class="spms-hub-tile-label">Cascade Status</div>
                                        <div class="spms-hub-tile-value">
                                            <?= $cascadedTeamId ? 'Cascaded to Subordinates' : 'Awaiting Cascade' ?>
                                        </div>
                                    </div>
                                    <div>
                                        <?php if ($cascadedTeamId): ?>
                                            <span class="text-[11px] font-extrabold text-emerald-400">Distribution Active</span>
                                        <?php else: ?>
                                            <span class="text-[11px] font-extrabold text-amber-400">Select Team on Right</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- ACTIONS -->
                            <div class="spms-hub-actions-bar">
                                <button type="button" onclick='openEditFolderModal(<?= json_encode([
                                    "id"    => $activeFolder["id"],
                                    "title" => $activeFolder["title"],
                                    "ipcr_target_start" => $activeFolder["ipcr_target_start"] ?? "",
                                    "ipcr_target_end"   => $activeFolder["ipcr_target_end"] ?? "",
                                    "ipcr_eval_start"   => $activeFolder["ipcr_eval_start"] ?? "",
                                    "ipcr_eval_end"     => $activeFolder["ipcr_eval_end"] ?? "",
                                    "cdpcr_target_start" => $activeFolder["cdpcr_target_start"] ?? "",
                                    "cdpcr_target_end"   => $activeFolder["cdpcr_target_end"] ?? "",
                                    "cdpcr_eval_start"   => $activeFolder["cdpcr_eval_start"] ?? "",
                                    "cdpcr_eval_end"     => $activeFolder["cdpcr_eval_end"] ?? "",
                                    "dpcr_target_start" => $activeFolder["dpcr_target_start"] ?? "",
                                    "dpcr_target_end"   => $activeFolder["dpcr_target_end"] ?? "",
                                    "dpcr_eval_start"   => $activeFolder["dpcr_eval_start"] ?? "",
                                    "dpcr_eval_end"     => $activeFolder["dpcr_eval_end"] ?? "",
                                    "opcr_target_start" => $activeFolder["opcr_target_start"] ?? "",
                                    "opcr_target_end"   => $activeFolder["opcr_target_end"] ?? "",
                                    "opcr_eval_start"   => $activeFolder["opcr_eval_start"] ?? "",
                                    "opcr_eval_end"     => $activeFolder["opcr_eval_end"] ?? "",
                                    "iperf_target_start" => $activeFolder["iperf_target_start"] ?? "",
                                    "iperf_target_end"   => $activeFolder["iperf_target_end"] ?? "",
                                    "iperf_eval_start"   => $activeFolder["iperf_eval_start"] ?? "",
                                    "iperf_eval_end"     => $activeFolder["iperf_eval_end"] ?? ""
                                ]) ?>)' class="spms-hub-btn-primary">
                                    Edit Cycle Dates
                                </button>
                                <button type="button" onclick="openUserGuideModal()" class="spms-hub-btn-secondary">
                                    SPMS Cascade Guide
                                </button>
                            </div>
                        </div>

                    <?php elseif (empty($myDocs)): ?>
                        <div class="border-2 border-dashed border-[#c2d4c4] dark:border-[#0c4a33] bg-[#f4f8f4]/50 dark:bg-[#032316]/30 rounded-2xl w-full flex-1 flex flex-col items-center justify-center p-8 sm:p-12 my-2 min-h-[380px] text-center">
                            <div class="w-12 h-12 rounded-xl bg-[#d5e4d7] dark:bg-[#0c4a33] text-[#064e3b] dark:text-emerald-400 flex items-center justify-center mb-3.5 shadow-2xs">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                            </div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1">No Document Available</h3>
                            <p class="text-xs text-[#527961] dark:text-[#94A3B8] max-w-sm mb-2 leading-relaxed">
                                No official performance paper is currently assigned to this folder.
                            </p>
                        </div>
                    <?php else: ?>
                        <?php 
                            $primaryDoc = null;
                            foreach ($myDocs as $d) {
                                if (!empty($d['is_target'])) { $primaryDoc = $d; break; }
                            }
                            if (!$primaryDoc) $primaryDoc = reset($myDocs);

                            $attachmentCount = 0;
                            $attachmentsList = [];
                            if (!empty($primaryDoc['id'])) {
                                $attModel = new \App\Models\DocumentAttachmentModel();
                                $attachmentsList = $attModel->where('document_id', $primaryDoc['id'])
                                                           ->where('deleted_at IS NULL')
                                                           ->findAll();
                                $attachmentCount = count($attachmentsList);
                            }

                            $pTitleUpper = strtoupper($primaryDoc['title'] ?? '');
                            if (str_contains($pTitleUpper, 'OPCR') || str_contains($pTitleUpper, 'OFFICE')) {
                                $effectiveDocType = 'OPCR';
                                $officialPaperName = 'Office Performance Commitment and Review (OPCR)';
                            } elseif (str_contains($pTitleUpper, 'DPCR') || str_contains($pTitleUpper, 'DIVISION') || str_contains($pTitleUpper, 'DEPARTMENT')) {
                                if (!empty($isOwnerDean)) {
                                    $effectiveDocType = 'CDPCR';
                                    $officialPaperName = 'DPCR Dean (Collegiate Performance Commitment and Review)';
                                } else {
                                    $effectiveDocType = 'DPCR';
                                    $officialPaperName = 'DPCR Department Chair (Departmental Performance Commitment and Review)';
                                }
                            } elseif (str_contains($pTitleUpper, 'IPERF') || str_contains($pTitleUpper, 'NON-TEACHING')) {
                                $effectiveDocType = 'IPERF';
                                $officialPaperName = 'Individual Performance Evaluation Review Form (IPERF)';
                            } elseif (str_contains($pTitleUpper, 'IPCR')) {
                                $effectiveDocType = 'IPCR';
                                $officialPaperName = 'Individual Performance Commitment and Review (IPCR)';
                            } else {
                                $effectiveDocType = strtoupper($ownerDocType ?? 'IPCR');
                                $officialPaperName = match($effectiveDocType) {
                                    'OPCR'  => 'Office Performance Commitment and Review (OPCR)',
                                    'CDPCR' => 'DPCR Dean (Collegiate Performance Commitment and Review)',
                                    'DPCR'  => 'DPCR Department Chair (Departmental Performance Commitment and Review)',
                                    'IPERF' => 'Individual Performance Evaluation Review Form (IPERF)',
                                    default => 'Individual Performance Commitment and Review (IPCR)'
                                };
                            }

                            $rawStatus = $activeFolder['status'] ?? 'draft_target';
                            $statusBadgeText = match($rawStatus) {
                                'approved', 'twg_approved' => 'APPROVED',
                                'twg_disapproved' => 'TWG DISAPPROVED',
                                'submitted', 'to evaluate' => 'SUBMITTED',
                                'evaluated' => 'EVALUATED',
                                'pending_target_approval' => 'TARGET SUBMITTED',
                                'target_approved' => 'TARGET APPROVED',
                                'target_returned', 'target_unapproved' => 'TARGET REVISION',
                                'reevaluate' => 'REVISION',
                                'unevaluated' => 'UNEVALUATED',
                                'draft' => 'DRAFT EVALUATION',
                                default => 'DRAFT TARGET'
                            };

                            // Stepper active index (1: Target Setting, 2: Target Approved, 3: Evaluation, 4: Final Rating)
                            if (in_array($rawStatus, ['approved', 'twg_approved', 'twg_disapproved'])) {
                                $currentStepIndex = 4;
                            } elseif (in_array($rawStatus, ['draft', 'submitted', 'to evaluate', 'evaluated', 'reevaluate', 'unevaluated'])) {
                                $currentStepIndex = 3;
                            } elseif ($rawStatus === 'target_approved') {
                                $currentStepIndex = 2;
                            } else {
                                $currentStepIndex = 1;
                            }

                            $docTypeKey = strtolower($effectiveDocType);
                            if ($docTypeKey === 'cdpcr' && empty($activeFolder['cdpcr_target_start']) && empty($activeFolder['cdpcr_target_end']) && empty($activeFolder['cdpcr_eval_start']) && empty($activeFolder['cdpcr_eval_end'])) {
                                $docTypeKey = 'dpcr';
                            }
                            $tStartDate = !empty($activeFolder[$docTypeKey . '_target_start']) ? strtotime($activeFolder[$docTypeKey . '_target_start']) : null;
                            $tEndDate   = !empty($activeFolder[$docTypeKey . '_target_end'])   ? strtotime($activeFolder[$docTypeKey . '_target_end'])   : null;
                            $eStartDate = !empty($activeFolder[$docTypeKey . '_eval_start'])   ? strtotime($activeFolder[$docTypeKey . '_eval_start'])   : null;
                            $eEndDate   = !empty($activeFolder[$docTypeKey . '_eval_end'])     ? strtotime($activeFolder[$docTypeKey . '_eval_end'])     : null;

                            $isEvaluationPhase = ($currentStepIndex >= 3);
                            $activeStart = $isEvaluationPhase ? $eStartDate : $tStartDate;
                            $activeEnd   = $isEvaluationPhase ? $eEndDate : $tEndDate;
                            $phasePrefix = $isEvaluationPhase ? 'Eval: ' : 'Target: ';

                            if ($activeStart && $activeEnd) {
                                if (date('m Y', $activeStart) === date('m Y', $activeEnd)) {
                                    $windowDateStr = $phasePrefix . date('M j', $activeStart) . ' – ' . date('j', $activeEnd);
                                } else {
                                    $windowDateStr = $phasePrefix . date('M j', $activeStart) . ' – ' . date('M j', $activeEnd);
                                }
                            } elseif ($activeStart || $activeEnd) {
                                $windowDateStr = $phasePrefix . date('M j, Y', $activeStart ?: $activeEnd);
                            } else {
                                $windowDateStr = $phasePrefix . 'Sept 1 – 15';
                            }

                            $now = time();
                            if (!$activeEnd) {
                                $windowPillText = 'ACTIVE • 2 days left';
                            } elseif ($now <= $activeEnd) {
                                $daysLeft = ceil(($activeEnd - $now) / 86400);
                                if ($daysLeft > 1) {
                                    $windowPillText = "ACTIVE • {$daysLeft} days left";
                                } elseif ($daysLeft == 1) {
                                    $windowPillText = "ACTIVE • 1 day left";
                                } else {
                                    $windowPillText = "ACTIVE • Due Today";
                                }
                            } elseif ($now < $activeStart) {
                                $daysToStart = ceil(($activeStart - $now) / 86400);
                                $windowPillText = "ACTIVE • Starts in {$daysToStart}d";
                            } else {
                                $windowPillText = ($currentStepIndex === 4) ? 'COMPLETED' : 'CLOSED';
                            }

                            $finalRatingVal = $activeFolder['final_rating'] ?? null;
                            if (!empty($finalRatingVal) && (float)$finalRatingVal > 0) {
                                $num = number_format((float)$finalRatingVal, 2);
                                $adjective = match(true) {
                                    $finalRatingVal >= 4.50 => 'Outstanding',
                                    $finalRatingVal >= 3.50 => 'Very Satisfactory',
                                    $finalRatingVal >= 2.50 => 'Satisfactory',
                                    $finalRatingVal >= 1.50 => 'Unsatisfactory',
                                    default => 'Poor'
                                };
                                $finalRatingDisplay = "{$num} • {$adjective}";
                            } else {
                                $finalRatingDisplay = 'Pending Evaluation';
                            }

                            $ratingPeriodText = ($activeFolder['title'] && $activeFolder['title'] !== 'Untitled Evaluation') 
                                ? $activeFolder['title'] 
                                : '1st Semester, AY 2026–2027';
                        ?>

                        <!-- EXECUTIVE PERFORMANCE PAPER HUB CARD -->
                        <div class="spms-hub-card">
                            <!-- HEADER -->
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 28px;">
                                <div>
                                    <span class="spms-hub-kicker">OFFICIAL PERFORMANCE PAPER</span>
                                    <h2 class="spms-hub-title"><?= esc($officialPaperName) ?></h2>
                                    <p class="spms-hub-subtitle">
                                        Current Rating Period: <span class="spms-hub-subtitle-val"><?= esc($ratingPeriodText) ?></span>
                                    </p>
                                </div>
                                <div style="flex-shrink: 0;">
                                    <div class="spms-hub-status-pill">
                                        <span class="spms-hub-status-dot"></span>
                                        <span><?= esc($statusBadgeText) ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- CYCLE PROGRESS TRACKER -->
                            <div>
                                <div class="spms-hub-tracker-label">CYCLE PROGRESS TRACKER</div>
                                <div class="spms-hub-stepper-row">
                                    
                                    <!-- Step 1: Target Setting -->
                                    <div style="display: flex; align-items: center; flex-shrink: 0;">
                                        <?php if ($currentStepIndex === 1): ?>
                                            <div class="spms-hub-circle-active">
                                                <div class="spms-hub-circle-active-dot"></div>
                                            </div>
                                            <span class="spms-hub-step-text-active">Target Setting</span>
                                        <?php elseif ($currentStepIndex > 1): ?>
                                            <div class="spms-hub-circle-completed">
                                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            </div>
                                            <span class="spms-hub-step-text-active">Target Setting</span>
                                        <?php else: ?>
                                            <div class="spms-hub-circle-inactive"></div>
                                            <span class="spms-hub-step-text-inactive">Target Setting</span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Arrow 1 -> 2 -->
                                    <?php if ($currentStepIndex === 1): ?>
                                        <svg width="60" height="12" viewBox="0 0 60 12" fill="none" style="flex-shrink: 0; margin: 0 8px;">
                                            <line x1="0" y1="6" x2="48" y2="6" stroke="#f59e0b" stroke-width="2" stroke-dasharray="3 3" />
                                            <polygon points="46,2 56,6 46,10" fill="#f59e0b" />
                                        </svg>
                                    <?php elseif ($currentStepIndex > 1): ?>
                                        <svg width="60" height="12" viewBox="0 0 60 12" fill="none" style="flex-shrink: 0; margin: 0 8px;">
                                            <line x1="0" y1="6" x2="48" y2="6" stroke="#10b981" stroke-width="2" />
                                            <polygon points="46,2 56,6 46,10" fill="#10b981" />
                                        </svg>
                                    <?php else: ?>
                                        <svg width="60" height="12" viewBox="0 0 60 12" fill="none" style="flex-shrink: 0; margin: 0 8px;">
                                            <line x1="0" y1="6" x2="48" y2="6" stroke="#145235" stroke-width="1.5" />
                                            <polygon points="46,2 56,6 46,10" fill="#145235" />
                                        </svg>
                                    <?php endif; ?>

                                    <!-- Step 2: Target Approved -->
                                    <div style="display: flex; align-items: center; flex-shrink: 0;">
                                        <?php if ($currentStepIndex === 2): ?>
                                            <div class="spms-hub-circle-active">
                                                <div class="spms-hub-circle-active-dot"></div>
                                            </div>
                                            <span class="spms-hub-step-text-active">Target Approved</span>
                                        <?php elseif ($currentStepIndex > 2): ?>
                                            <div class="spms-hub-circle-completed">
                                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            </div>
                                            <span class="spms-hub-step-text-active">Target Approved</span>
                                        <?php else: ?>
                                            <div class="spms-hub-circle-inactive"></div>
                                            <span class="spms-hub-step-text-inactive">Target Approved</span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Arrow 2 -> 3 -->
                                    <?php if ($currentStepIndex === 2): ?>
                                        <svg width="60" height="12" viewBox="0 0 60 12" fill="none" style="flex-shrink: 0; margin: 0 8px;">
                                            <line x1="0" y1="6" x2="48" y2="6" stroke="#f59e0b" stroke-width="2" stroke-dasharray="3 3" />
                                            <polygon points="46,2 56,6 46,10" fill="#f59e0b" />
                                        </svg>
                                    <?php elseif ($currentStepIndex > 2): ?>
                                        <svg width="60" height="12" viewBox="0 0 60 12" fill="none" style="flex-shrink: 0; margin: 0 8px;">
                                            <line x1="0" y1="6" x2="48" y2="6" stroke="#10b981" stroke-width="2" />
                                            <polygon points="46,2 56,6 46,10" fill="#10b981" />
                                        </svg>
                                    <?php else: ?>
                                        <svg width="60" height="12" viewBox="0 0 60 12" fill="none" style="flex-shrink: 0; margin: 0 8px;">
                                            <line x1="0" y1="6" x2="48" y2="6" stroke="#145235" stroke-width="1.5" />
                                            <polygon points="46,2 56,6 46,10" fill="#145235" />
                                        </svg>
                                    <?php endif; ?>

                                    <!-- Step 3: Evaluation -->
                                    <div style="display: flex; align-items: center; flex-shrink: 0;">
                                        <?php if ($currentStepIndex === 3): ?>
                                            <div class="spms-hub-circle-active">
                                                <div class="spms-hub-circle-active-dot"></div>
                                            </div>
                                            <span class="spms-hub-step-text-active">Evaluation</span>
                                        <?php elseif ($currentStepIndex > 3): ?>
                                            <div class="spms-hub-circle-completed">
                                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            </div>
                                            <span class="spms-hub-step-text-active">Evaluation</span>
                                        <?php else: ?>
                                            <div class="spms-hub-circle-inactive"></div>
                                            <span class="spms-hub-step-text-inactive">Evaluation</span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Arrow 3 -> 4 -->
                                    <?php if ($currentStepIndex === 3): ?>
                                        <svg width="60" height="12" viewBox="0 0 60 12" fill="none" style="flex-shrink: 0; margin: 0 8px;">
                                            <line x1="0" y1="6" x2="48" y2="6" stroke="#f59e0b" stroke-width="2" stroke-dasharray="3 3" />
                                            <polygon points="46,2 56,6 46,10" fill="#f59e0b" />
                                        </svg>
                                    <?php elseif ($currentStepIndex > 3): ?>
                                        <svg width="60" height="12" viewBox="0 0 60 12" fill="none" style="flex-shrink: 0; margin: 0 8px;">
                                            <line x1="0" y1="6" x2="48" y2="6" stroke="#10b981" stroke-width="2" />
                                            <polygon points="46,2 56,6 46,10" fill="#10b981" />
                                        </svg>
                                    <?php else: ?>
                                        <svg width="60" height="12" viewBox="0 0 60 12" fill="none" style="flex-shrink: 0; margin: 0 8px;">
                                            <line x1="0" y1="6" x2="48" y2="6" stroke="#145235" stroke-width="1.5" />
                                            <polygon points="46,2 56,6 46,10" fill="#145235" />
                                        </svg>
                                    <?php endif; ?>

                                    <!-- Step 4: Final Rating -->
                                    <div style="display: flex; align-items: center; flex-shrink: 0;">
                                        <?php if ($currentStepIndex === 4): ?>
                                            <div class="spms-hub-circle-active">
                                                <div class="spms-hub-circle-active-dot"></div>
                                            </div>
                                            <span class="spms-hub-step-text-active">Final Rating</span>
                                        <?php else: ?>
                                            <div class="spms-hub-circle-inactive"></div>
                                            <span class="spms-hub-step-text-inactive">Final Rating</span>
                                        <?php endif; ?>
                                    </div>

                                </div>
                            </div>

                            <!-- 3 STAT TILES -->
                            <div class="spms-hub-stat-grid">
                                <!-- 1. Current Window -->
                                <div class="spms-hub-stat-tile">
                                    <div>
                                        <div class="spms-hub-tile-label">Current Window</div>
                                        <div class="spms-hub-tile-value"><?= esc($windowDateStr) ?></div>
                                    </div>
                                    <div>
                                        <div class="spms-hub-window-pill">
                                            <span style="width: 6px; height: 6px; border-radius: 9999px; background-color: #34d399; display: inline-block;"></span>
                                            <span><?= esc($windowPillText) ?></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- 2. Final Rating -->
                                <div class="spms-hub-stat-tile">
                                    <div>
                                        <div class="spms-hub-tile-label">Final Rating</div>
                                        <div class="spms-hub-tile-value"><?= esc($finalRatingDisplay) ?></div>
                                    </div>
                                    <div>
                                        <div class="spms-hub-tile-dash"></div>
                                    </div>
                                </div>

                                <!-- 3. Evidence / MOVs -->
                                <div class="spms-hub-stat-tile">
                                    <div>
                                        <div class="spms-hub-tile-label">Evidence / MOVs</div>
                                        <div class="spms-hub-tile-value"><?= $attachmentCount ?> Files Attached</div>
                                    </div>
                                    <div>
                                        <button type="button" onclick="openAttachmentsModal()" class="spms-hub-tile-btn">
                                            View Attachments
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- 4 ACTION BUTTONS (Clean text, exact mockup match) -->
                            <div class="spms-hub-actions-bar">
                                <a href="<?= site_url('document/' . $primaryDoc['id']) ?>" class="spms-hub-btn-primary">
                                    OPEN & EDIT PAPER
                                </a>
                                <a href="<?= site_url('document/' . $primaryDoc['id'] . '/export-excel') ?>" class="spms-hub-btn-secondary">
                                    Export Excel
                                </a>
                                <button type="button" onclick="triggerPrintPdf('<?= site_url('document/' . $primaryDoc['id']) ?>')" class="spms-hub-btn-secondary">
                                    Print / PDF
                                </button>
                                <button type="button" onclick="openUserGuideModal()" class="spms-hub-btn-secondary">
                                    User Guide
                                </button>
                            </div>
                        </div>

                    <?php endif; ?>
                </div>

                <!-- 2. TEAM SUBMISSIONS TAB -->
                <div id="tab-content-team" class="tab-content-doc hidden flex-1 flex-col min-h-0 bg-surface overflow-y-auto custom-scrollbar px-6 lg:px-8 py-4">
                    <?php if (empty($groupedGuides)): ?>
                        <div class="p-12 text-center">
                            <p class="text-sm text-text-muted font-medium italic">No team submissions or guide documents found for this period.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($groupedGuides as $gIdx => $group): ?>
                            <div class="mb-4">
                                <h4 class="text-xs font-black uppercase tracking-wider text-text-muted mb-2">Guide: <?= esc($group['superior']['name']) ?> (<?= esc($group['superior']['role']) ?>)</h4>
                                <?php foreach ($group['docs'] as $doc): ?>
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 px-5 py-3.5 items-center bg-surface border border-surface-border rounded-xl mb-2.5">
                                        <div class="col-span-1 md:col-span-6 flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 border border-blue-200 dark:bg-blue-950/60 dark:border-blue-800/40 dark:text-blue-400 flex items-center justify-center shrink-0">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                            </div>
                                            <div class="flex flex-col min-w-0">
                                                <a href="<?= site_url('document/' . $doc['id']) ?>" class="font-bold text-xs text-text hover:text-emerald-500 truncate">
                                                    <?= esc($doc['title']) ?>
                                                </a>
                                                <span class="text-[10px] text-text-muted">Superior Basis / Guide</span>
                                            </div>
                                        </div>
                                        <div class="col-span-1 md:col-span-4 text-xs text-text-muted">
                                            <?= esc($group['superior']['role']) ?>
                                        </div>
                                        <div class="col-span-1 md:col-span-2 flex justify-end">
                                            <a href="<?= site_url('document/' . $doc['id']) ?>" class="text-[10px] font-bold uppercase tracking-wider text-cyan-600 dark:text-cyan-400 hover:underline px-3 py-1 bg-cyan-50 dark:bg-cyan-950/50 border border-cyan-200 dark:border-cyan-800/40 rounded-lg">View</a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </div>
        </div>

        <?php 
            $isUserAdmin = (session()->get('role') === 'Admin');
            $canCascade = $isUserAdmin || (session()->get('role') === 'Supervisor' && ($activeFolder['user_id'] == session()->get('user_id'))); 
            $docType = strtolower($ownerDocType ?? 'ipcr');
            if (($docType === 'dpcr' || $docType === 'cdpcr') && !empty($isOwnerDean)) {
                $docType = (!empty($activeFolder['cdpcr_target_start']) || !empty($activeFolder['cdpcr_target_end']) || !empty($activeFolder['cdpcr_eval_start']) || !empty($activeFolder['cdpcr_eval_end'])) ? 'cdpcr' : 'dpcr';
            }
            $tStartDate = !empty($activeFolder[$docType . '_target_start']) ? date('M d, Y', strtotime($activeFolder[$docType . '_target_start'])) : null;
            $tEndDate   = !empty($activeFolder[$docType . '_target_end'])   ? date('M d, Y', strtotime($activeFolder[$docType . '_target_end']))   : null;
            $eStartDate = !empty($activeFolder[$docType . '_eval_start'])   ? date('M d, Y', strtotime($activeFolder[$docType . '_eval_start']))   : null;
            $eEndDate   = !empty($activeFolder[$docType . '_eval_end'])     ? date('M d, Y', strtotime($activeFolder[$docType . '_eval_end']))     : null;

            $targetDateStr = ($tStartDate && $tEndDate) ? ($tStartDate . ' – ' . $tEndDate) : ($tStartDate ?? $tEndDate ?? 'Start – End');
            $evalDateStr   = ($eStartDate && $eEndDate) ? ($eStartDate . ' – ' . $eEndDate) : ($eStartDate ?? $eEndDate ?? 'Start – End');
        ?>

        <!-- RIGHT SIDEBAR (CASCADE DISTRIBUTION FOR ADMINS / FOLDER DETAILS FOR USERS) -->
        <div id="bottom-sheet" class="lg:overflow-y-auto custom-scrollbar fixed inset-x-0 bottom-0 z-50 shadow-2xl lg:shadow-sm rounded-t-3xl lg:rounded-2xl transition-transform duration-300 transform translate-y-[calc(100%-95px)] lg:static lg:translate-y-0 lg:w-80 lg:shrink-0 flex flex-col p-5 gap-5" style="background-color: #02160e !important; border: 1px solid #0d4a32 !important;">
            
            <div class="lg:hidden flex justify-center py-2 cursor-pointer touch-none" onclick="toggleBottomSheet()">
                <div class="w-12 h-1 bg-zinc-300 dark:bg-slate-600 rounded-full"></div>
            </div>

            <?php if ($canCascade): ?>
                <!-- CASCADE MANAGEMENT SECTION (For Admins & Supervisors) -->
                <div class="flex flex-col gap-3">
                    <?php $cascadedTeamId = $activeFolder['routing_preset_id'] ?? null; ?>
                    <div class="flex items-center justify-between">
                        <h4 class="text-[10px] font-black uppercase text-text-muted tracking-wider">Cascade Management</h4>
                        <?php if ($cascadedTeamId): ?>
                            <span class="text-[10px] font-bold text-emerald-600 dark:text-[#34d399] bg-emerald-50 dark:bg-[#102a1e] px-2 py-0.5 rounded-md border border-emerald-200 dark:border-[#1b4330]">Cascaded</span>
                        <?php endif; ?>
                    </div>

                    <?php 
                        $isUserAdmin = (session()->get('role') === 'Admin');
                        // Supervisors can cascade immediately once formulating commitments in target phase
                        $inTargetPhase = in_array($activeFolder['status'], [
                            \App\Enums\FolderStatus::DRAFT_TARGET->value,
                            \App\Enums\FolderStatus::PENDING_TARGET_APPROVAL->value,
                            \App\Enums\FolderStatus::TARGET_APPROVED->value,
                            \App\Enums\FolderStatus::DRAFT->value
                        ]);
                        $canActuallyCascade = $isUserAdmin || $inTargetPhase;
                        $hasPresets = !empty($presets);
                    ?>

                    <?php if (!$canActuallyCascade && !$cascadedTeamId): ?>
                        <div class="p-3 rounded-xl border border-amber-300 dark:border-amber-800/60 bg-amber-50 dark:bg-amber-950/40 text-amber-900 dark:text-amber-200 flex flex-col gap-1.5">
                            <div class="flex items-center gap-1.5 font-extrabold text-[11px] uppercase tracking-wider text-amber-800 dark:text-amber-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                <span>Cascade Closed</span>
                            </div>
                            <p class="text-[10px] leading-tight text-amber-700 dark:text-amber-400">
                                The target setting phase has ended for this cycle. Subordinates can no longer be cascaded.
                            </p>
                        </div>
                    <?php elseif ($cascadedTeamId): ?>
                        <div class="relative w-full">
                            <select id="team-cascade-select" disabled class="w-full bg-zinc-50 dark:bg-[#0c1510] text-xs font-bold text-text outline-none pl-3.5 pr-8 py-2.5 rounded-xl appearance-none border border-surface-border opacity-60 cursor-not-allowed">
                                <?php foreach($presets as $preset): ?>
                                    <option value="<?= $preset['id'] ?>" <?= ($cascadedTeamId == $preset['id']) ? 'selected' : '' ?> class="bg-surface text-text">
                                        <?= esc($preset['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-text-muted">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                        </div>

                        <div class="flex items-center justify-between px-1">
                            <span class="text-[10px] text-text-muted font-medium">Team Roster</span>
                            <a href="<?= site_url('teams?team_id=' . $cascadedTeamId) ?>" class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 hover:underline inline-flex items-center gap-1">
                                + Manage Members in Teams
                            </a>
                        </div>

                        <?php $pendingCount = (int)($pendingTeamMembersCount ?? 0); ?>
                        <?php if ($pendingCount > 0 && $canActuallyCascade): ?>
                            <!-- SYNC NOTICE: MISSING MEMBERS DETECTED -->
                            <div class="p-3 rounded-xl border border-emerald-300 dark:border-emerald-800/60 bg-emerald-50 dark:bg-emerald-950/40 flex flex-col gap-2 shadow-2xs">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-1.5 font-bold text-xs text-emerald-800 dark:text-emerald-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                        </svg>
                                        <span>New Members (+<?= $pendingCount ?>)</span>
                                    </div>
                                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-emerald-200 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-200">
                                        Sync Available
                                    </span>
                                </div>
                                <p class="text-[10px] leading-relaxed text-emerald-700 dark:text-emerald-400 font-medium">
                                    <?= $pendingCount ?> member<?= $pendingCount === 1 ? ' was' : 's were' ?> added to this team. Sync to provision their target folders without affecting existing cascaded members.
                                </p>
                                <button id="btn-sync-cascade" onclick="triggerSyncCascade('<?= $activeFolder['id'] ?>')" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-xs shadow-xs transition-all active:scale-98 cursor-pointer flex items-center justify-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    <span>Sync Team (+<?= $pendingCount ?> New)</span>
                                </button>
                            </div>
                        <?php endif; ?>

                        <button onclick="triggerUncascade('<?= $activeFolder['id'] ?>')" class="w-full py-2.5 text-rose-600 dark:text-rose-400 hover:text-white border border-rose-300 dark:border-[#361a1f] bg-rose-50 dark:bg-[#1c1214] hover:bg-rose-600 dark:hover:bg-[#261619] rounded-xl transition-colors cursor-pointer flex justify-center items-center gap-1.5 font-bold text-xs uppercase tracking-wider">
                            Revoke Cascade
                        </button>

                        <?php if (!empty($cascadedChildren)): ?>
                            <div class="mt-2 flex flex-col gap-2">
                                <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-wider text-text-muted">
                                    <span>Cascaded Subordinates</span>
                                    <span><?= count($cascadedChildren) ?></span>
                                </div>
                                <div class="max-h-56 overflow-y-auto space-y-1.5 custom-scrollbar pr-0.5">
                                    <?php foreach ($cascadedChildren as $child): ?>
                                        <?php 
                                            $isPending = ($child['status'] === \App\Enums\FolderStatus::PENDING_TARGET_APPROVAL->value);
                                            $isApproved = ($child['status'] === \App\Enums\FolderStatus::TARGET_APPROVED->value);
                                        ?>
                                        <div class="p-2.5 rounded-xl border border-slate-200 dark:border-[#1e382b] bg-slate-50 dark:bg-[#0c1510] flex flex-col gap-1.5 text-xs">
                                            <div class="flex items-start justify-between gap-1">
                                                <div>
                                                    <span class="font-bold text-slate-800 dark:text-white block text-[11px] leading-tight">
                                                        <?= esc($child['first_name'] . ' ' . $child['last_name']) ?>
                                                    </span>
                                                    <span class="text-[9px] text-slate-500 dark:text-slate-400">
                                                        <?= esc($child['position'] ?: $child['email']) ?>
                                                    </span>
                                                </div>
                                                <div class="flex items-center gap-1.5 shrink-0">
                                                    <?php if ($isPending): ?>
                                                        <span class="text-[9px] font-extrabold px-1.5 py-0.5 rounded bg-amber-100 dark:bg-amber-950/80 text-amber-700 dark:text-amber-300 border border-amber-300 shrink-0">
                                                            Awaiting Approval
                                                        </span>
                                                    <?php elseif ($isApproved): ?>
                                                        <span class="text-[9px] font-extrabold px-1.5 py-0.5 rounded bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 border border-emerald-300 shrink-0">
                                                            Target Approved
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400 shrink-0">
                                                            Drafting
                                                        </span>
                                                    <?php endif; ?>

                                                    <?php if ($canActuallyCascade): ?>
                                                        <button type="button"
                                                                onclick="triggerRemoveCascadedSubordinate('<?= $child['id'] ?>', '<?= esc(addslashes($child['first_name'] . ' ' . $child['last_name'])) ?>', '<?= $activeFolder['id'] ?>')"
                                                                title="Remove from this cycle"
                                                                class="p-1 rounded-md text-zinc-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors cursor-pointer shrink-0">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <?php if ($isPending): ?>
                                                <a href="<?= site_url('ratings/show/' . $child['id']) ?>" 
                                                   class="w-full py-1.5 px-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-[10px] text-center flex items-center justify-center gap-1 shadow-xs transition-colors">
                                                    <span>Review Targets</span>
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php elseif ($hasPresets): ?>
                        <div class="flex flex-col gap-2">
                            <label for="team-cascade-select" class="text-[10px] font-bold text-text-muted uppercase tracking-wider">
                                Select Distribution Team
                            </label>
                            <div class="relative w-full">
                                <select id="team-cascade-select" <?= ($isLocked || !$canActuallyCascade) ? 'disabled' : '' ?> class="w-full bg-zinc-50 dark:bg-[#0c1510] text-xs font-bold text-text outline-none pl-3.5 pr-8 py-2.5 rounded-xl appearance-none border border-surface-border <?= ($isLocked || !$canActuallyCascade) ? 'opacity-60 cursor-not-allowed' : 'cursor-pointer focus:border-emerald-500/50' ?>">
                                    <option value="" disabled selected class="bg-surface text-text-muted">-- Select a Team to Cascade --</option>
                                    <?php foreach($presets as $preset): ?>
                                        <?php $mCount = (int)($preset['member_count'] ?? 0); ?>
                                        <option value="<?= $preset['id'] ?>" class="bg-surface text-text">
                                            <?= esc($preset['name']) ?> (<?= $mCount ?> <?= ($mCount === 1 ? 'member' : 'members') ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-text-muted">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <a href="<?= site_url('teams') ?>" class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 hover:underline inline-flex items-center gap-1">
                                    + Manage / Create Teams
                                </a>
                            </div>
                        </div>

                        <?php if (!$isLocked && $canActuallyCascade): ?>
                            <button onclick="triggerCascade('<?= $activeFolder['id'] ?>')" class="w-full py-3 bg-[#064e3b] hover:bg-[#085a3a] text-white dark:bg-[#f59e0b] dark:hover:bg-[#d97706] dark:text-black rounded-xl shadow-md transition-all cursor-pointer flex justify-center items-center gap-1.5 font-black text-xs uppercase tracking-wider active:scale-98">
                                Cascade to Selected Team
                            </button>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="p-4 text-center rounded-xl border border-dashed border-surface-border bg-surface/50 flex flex-col items-center gap-2">
                            <div class="w-9 h-9 rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div class="text-xs font-bold text-text">No Teams Created Yet</div>
                            <p class="text-[10px] text-text-muted leading-tight">Create a distribution team with members in Teams before you can cascade targets.</p>
                            <a href="<?= site_url('teams') ?>" class="mt-1 px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-bold text-xs transition-colors shadow-xs inline-flex items-center gap-1.5">
                                <span>Go to Teams Builder</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- FOLDER MANAGEMENT BUTTONS (Admin Only) -->
                <?php if (session()->get('role') === 'Admin'): ?>
                <div class="flex flex-col gap-3 border-t border-surface-border pt-4 mt-auto">
                    <h4 class="text-[10px] font-black uppercase text-text-muted tracking-wider">Folder Management</h4>
                    
                    <?php if (!empty($activeFolder['deleted_at'])): ?>
                        <?php 
                            $archivedTimestamp = strtotime($activeFolder['deleted_at']);
                            $fiveYearsAgo = strtotime('-5 years');
                            $isEligibleForDisposal = ($archivedTimestamp <= $fiveYearsAgo);
                            $yearsElapsed = max(0, min(5, floor((time() - $archivedTimestamp) / (365.25 * 86400))));
                        ?>
                        <div class="flex flex-col gap-2.5">
                            <button onclick='unarchiveFolder("<?= esc($activeFolder["id"]) ?>", "<?= esc(addslashes($activeFolder["title"])) ?>")'
                                    class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-2.5 rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                </svg>
                                Restore Folder
                            </button>

                            <?php if ($isEligibleForDisposal): ?>
                                <button class="w-full btn-delete-modal bg-white hover:bg-rose-50 text-rose-600 border border-rose-200 dark:bg-[#1c1214] dark:hover:bg-[#261619] dark:text-rose-400 dark:border-[#361a1f] py-2.5 rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer shadow-2xs"
                                    data-id="<?= $activeFolder['id'] ?>" data-desc="<?= esc($activeFolder['title']) ?>" data-url="<?= site_url('folder') ?>" data-title="Dispose Record (5-Year Retention Lapsed)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Permanent Delete (NAP / CSC Compliant)
                                </button>
                            <?php else: ?>
                                <div class="p-3 rounded-xl border border-emerald-200 dark:border-emerald-800/40 bg-emerald-50/50 dark:bg-emerald-950/20 text-emerald-900 dark:text-emerald-200 flex flex-col gap-1 text-center">
                                    <span class="text-[10px] font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-400">
                                        CSC 5-Year Retention Protected
                                    </span>
                                    <p class="text-[10px] text-slate-500 dark:text-slate-400 leading-tight">
                                        Under CSC & National Archives policies, performance evaluations are retained for 5 years for audit and promotions. Permanent disposal unlocks after 5 years.
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-2 gap-2">
                            <button onclick='openEditFolderModal(<?= json_encode([
                                "id"    => $activeFolder["id"],
                                "title" => $activeFolder["title"],
                                "ipcr_target_start" => $activeFolder["ipcr_target_start"] ?? "",
                                "ipcr_target_end"   => $activeFolder["ipcr_target_end"] ?? "",
                                "ipcr_eval_start"   => $activeFolder["ipcr_eval_start"] ?? "",
                                "ipcr_eval_end"     => $activeFolder["ipcr_eval_end"] ?? "",
                                "cdpcr_target_start" => $activeFolder["cdpcr_target_start"] ?? "",
                                "cdpcr_target_end"   => $activeFolder["cdpcr_target_end"] ?? "",
                                "cdpcr_eval_start"   => $activeFolder["cdpcr_eval_start"] ?? "",
                                "cdpcr_eval_end"     => $activeFolder["cdpcr_eval_end"] ?? "",
                                "dpcr_target_start" => $activeFolder["dpcr_target_start"] ?? "",
                                "dpcr_target_end"   => $activeFolder["dpcr_target_end"] ?? "",
                                "dpcr_eval_start"   => $activeFolder["dpcr_eval_start"] ?? "",
                                "dpcr_eval_end"     => $activeFolder["dpcr_eval_end"] ?? "",
                                "opcr_target_start" => $activeFolder["opcr_target_start"] ?? "",
                                "opcr_target_end"   => $activeFolder["opcr_target_end"] ?? "",
                                "opcr_eval_start"   => $activeFolder["opcr_eval_start"] ?? "",
                                "opcr_eval_end"     => $activeFolder["opcr_eval_end"] ?? "",
                                "iperf_target_start" => $activeFolder["iperf_target_start"] ?? "",
                                "iperf_target_end"  => $activeFolder["iperf_target_end"] ?? "",
                                "iperf_eval_start"  => $activeFolder["iperf_eval_start"] ?? "",
                                "iperf_eval_end"    => $activeFolder["iperf_eval_end"] ?? ""
                            ]) ?>)'
                                    class="w-full bg-white hover:bg-slate-50 text-slate-800 border border-slate-300 dark:bg-[#13271b] dark:hover:bg-[#1b3b29] dark:text-[#34d399] dark:border-[#1e422f] py-2.5 rounded-xl font-bold text-xs flex items-center justify-center transition-all cursor-pointer shadow-2xs">
                                Edit
                            </button>

                            <button onclick='archiveFolder("<?= esc($activeFolder["id"]) ?>", "<?= esc(addslashes($activeFolder["title"])) ?>")'
                                    class="w-full bg-white hover:bg-slate-100 text-amber-700 dark:text-[#b45309] border border-slate-200 dark:border-slate-300 py-2.5 rounded-xl font-bold text-xs flex items-center justify-center transition-all cursor-pointer shadow-2xs"
                                    title="Close and archive this evaluation cycle (freezes all scores)">
                                Close & Archive
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

            <?php else: ?>
                <!-- 2. FOLDER OVERVIEW & DETAILS SECTION (For Normal Users in exchange for Cascade Distribution) -->
                <?php
                    $now = date('Y-m-d H:i:s');
                    $tEnd = $activeFolder[$docType . '_target_end'] ?? null;
                    $isPastTarget = ($tEnd && $now > $tEnd);
                    $currentPhaseLabel = $isPastTarget ? 'Eval Phase' : 'Target Phase';
                    $phaseDotColor = $isPastTarget ? 'bg-sky-500' : 'bg-emerald-500';

                    $rawStatus = $activeFolder['status'] ?? 'draft';
                    $statusBadgeText = match($rawStatus) {
                        'approved', 'twg_approved' => 'Approved',
                        'twg_disapproved' => 'TWG Disapproved',
                        'to evaluate', 'submitted' => 'Submitted',
                        'evaluated' => 'Evaluated',
                        'draft_target' => 'Target Phase',
                        'pending_target_approval' => 'Target Submitted',
                        'target_approved' => 'Target Approved',
                        'target_returned', 'target_unapproved' => 'Target Revision',
                        'reevaluate' => 'Revision',
                        'unevaluated' => 'Unevaluated',
                        default => 'Draft'
                    };

                    // Derive official form type from the primary document inside the folder
                    $primaryDoc = null;
                    if (!empty($myDocs)) {
                        foreach ($myDocs as $d) {
                            if (!empty($d['is_target'])) { $primaryDoc = $d; break; }
                        }
                        if (!$primaryDoc) $primaryDoc = reset($myDocs);
                    }

                    $pTitleUpper = strtoupper($primaryDoc['title'] ?? '');
                    if (str_contains($pTitleUpper, 'OPCR') || str_contains($pTitleUpper, 'OFFICE')) {
                        $effectiveDocType = 'OPCR';
                    } elseif (str_contains($pTitleUpper, 'DPCR') || str_contains($pTitleUpper, 'DIVISION') || str_contains($pTitleUpper, 'DEPARTMENT')) {
                        $effectiveDocType = 'DPCR';
                    } elseif (str_contains($pTitleUpper, 'IPERF') || str_contains($pTitleUpper, 'NON-TEACHING')) {
                        $effectiveDocType = 'IPERF';
                    } elseif (str_contains($pTitleUpper, 'IPCR')) {
                        $effectiveDocType = 'IPCR';
                    } else {
                        $effectiveDocType = $ownerDocType ?? ($subFolderOwner['doc_type'] ?? 'IPCR');
                    }

                    $formTypeName = strtoupper($effectiveDocType ?: 'IPCR');
                    $formTypeDesc = match($formTypeName) {
                        'OPCR' => 'Office Performance',
                        'DPCR' => 'Department Performance',
                        default => 'Individual Performance'
                    };
                    $isOpcrFolder = ($formTypeName === 'OPCR');
                ?>

                <!-- Submission Summary Card -->
                <div class="flex flex-col gap-3">
                    <div class="flex items-center justify-between">
                        <h4 class="text-[10px] font-black uppercase text-slate-400 dark:text-[#8ea396] tracking-wider">Submission Summary</h4>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200 dark:bg-[#0c1510] dark:border-[#1a2b22] dark:text-slate-300">
                            <span class="w-1.5 h-1.5 rounded-full <?= $phaseDotColor ?> animate-pulse"></span>
                            <?= esc($currentPhaseLabel) ?>
                        </span>
                    </div>

                    <!-- 2 Compact Metric Tiles -->
                    <div class="grid grid-cols-2 gap-2.5">
                        <div class="bg-slate-50 dark:bg-[#0c1510] border border-slate-200 dark:border-[#1a2b22] rounded-xl p-3 flex flex-col">
                            <span class="text-[10px] font-medium text-slate-500 dark:text-[#8ea396]">Total Files</span>
                            <span class="text-lg font-black text-slate-900 dark:text-white mt-0.5"><?= count($myDocs) ?></span>
                        </div>
                        <div class="bg-slate-50 dark:bg-[#0c1510] border border-slate-200 dark:border-[#1a2b22] rounded-xl p-3 flex flex-col">
                            <span class="text-[10px] font-medium text-slate-500 dark:text-[#8ea396]">Folder Status</span>
                            <span class="text-xs font-bold text-slate-800 dark:text-white mt-1.5 truncate"><?= esc($statusBadgeText) ?></span>
                        </div>
                    </div>

                    <!-- Form Commitment Tile -->
                    <div class="bg-slate-50 dark:bg-[#0c1510] border border-slate-200 dark:border-[#1a2b22] rounded-xl px-3 py-2.5 flex items-center justify-between">
                        <span class="text-[10px] font-medium text-slate-500 dark:text-[#8ea396]">Commitment</span>
                        <span class="text-[11px] font-bold text-slate-800 dark:text-white truncate pl-2"><?= esc($formTypeName) ?> • <?= esc($formTypeDesc) ?></span>
                    </div>


                    <?php if ($activeFolder['status'] === \App\Enums\FolderStatus::PENDING_TARGET_APPROVAL->value): ?>
                        <?php if ($activeFolder['user_id'] == session()->get('user_id')): ?>
                            <button onclick="unsubmitTargetFolder('<?= $activeFolder['id'] ?>', this)" 
                                    class="w-full py-2.5 px-3 bg-rose-50 hover:bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:hover:bg-rose-900/60 dark:text-rose-300 border border-rose-300 dark:border-rose-800 rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer shadow-2xs">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                </svg>
                                Revoke Target Submission
                            </button>
                        <?php elseif (session()->get('role') === 'Admin'): ?>
                            <?php $isOpcrFolder = ($formTypeName === 'OPCR'); ?>
                            <div class="flex flex-col gap-2">
                                <?php if ($isOpcrFolder): ?>
                                    <button onclick="approveTargetFromFolder('<?= $activeFolder['id'] ?>', true, this)"
                                            class="w-full py-2.5 px-3 bg-gradient-to-r from-emerald-600 to-[#064e3b] hover:from-emerald-700 hover:to-[#085a3a] text-white rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer shadow-md active:scale-[0.98]">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                        Approve & Release to Deans
                                    </button>
                                <?php endif; ?>
                                <button onclick="approveTargetFromFolder('<?= $activeFolder['id'] ?>', false, this)"
                                        class="w-full py-2.5 px-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer shadow-2xs active:scale-[0.98]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Approve Target
                                </button>
                            </div>
                        <?php endif; ?>
                    <?php elseif ($activeFolder['status'] === \App\Enums\FolderStatus::TARGET_APPROVED->value && session()->get('role') === 'Admin' && ($isOpcrFolder ?? false)): ?>
                        <div class="flex flex-col gap-2">
                            <button onclick="approveTargetFromFolder('<?= $activeFolder['id'] ?>', true, this)"
                                    class="w-full py-2.5 px-3 bg-gradient-to-r from-emerald-600 to-[#064e3b] hover:from-emerald-700 hover:to-[#085a3a] text-white rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer shadow-md active:scale-[0.98]"
                                    title="Distribute this approved OPCR to all College Deans as their target basis">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                Release OPCR to Deans
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($parentFolder)): ?>
                        <!-- Superior Basis Cascade Status Tile (Strict SPMS Mode) -->
                        <div class="bg-slate-50 dark:bg-[#0c1510] border border-slate-200 dark:border-[#1a2b22] rounded-xl px-3 py-2.5 flex flex-col gap-1.5">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-medium text-slate-500 dark:text-[#8ea396]">Superior Basis</span>
                                <?php 
                                    $isParentDocOpcr = !empty($isParentDocOpcr) || str_contains(strtoupper($parentFolder['title'] ?? ''), 'OPCR');
                                ?>
                                <?php if ($isParentTargetApproved ?? false): ?>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-extrabold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-[#102a1e] px-1.5 py-0.5 rounded border border-emerald-200 dark:border-[#1b4330]">
                                        <?= $isParentDocOpcr ? 'Institutional Basis' : 'Approved' ?>
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-extrabold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/60 px-1.5 py-0.5 rounded border border-amber-200 dark:border-amber-800">
                                        Pending Approval
                                    </span>
                                <?php endif; ?>
                            </div>
                            <span class="text-[11px] font-bold text-slate-800 dark:text-white truncate" title="<?= esc($parentFolder['title']) ?>">
                                <?= esc($parentFolder['title']) ?>
                            </span>
                            <?php if (!($isParentTargetApproved ?? false)): ?>
                                <p class="text-[9px] text-amber-600 dark:text-amber-400 leading-tight italic">
                                    Target submission locked until superior targets are approved.
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Folder Details Block (Matching user's reference mockup) -->
                <div class="flex flex-col gap-3 pt-5 border-t border-slate-200 dark:border-[#1a2b22] mt-auto">
                    <h4 class="text-[10px] font-black uppercase text-slate-400 dark:text-[#8ea396] tracking-wider">Folder Details</h4>
                    
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center justify-between text-xs py-0.5">
                            <span class="text-slate-500 dark:text-[#8ea396] font-medium">Target Date</span>
                            <span class="text-slate-800 dark:text-white font-semibold text-right truncate pl-2"><?= esc($targetDateStr) ?></span>
                        </div>

                        <div class="flex items-center justify-between text-xs py-0.5">
                            <span class="text-slate-500 dark:text-[#94A3B8] font-medium">Evaluation Date</span>
                            <span class="text-slate-800 dark:text-white font-semibold text-right truncate pl-2"><?= esc($evalDateStr) ?></span>
                        </div>

                        <div class="flex items-center justify-between text-xs py-0.5">
                            <span class="text-slate-500 dark:text-[#94A3B8] font-medium">Period</span>
                            <span class="text-slate-800 dark:text-white font-semibold text-right truncate pl-2">Q3 <?= date('Y', strtotime($activeFolder['created_at'] ?? 'now')) ?></span>
                        </div>

                        <div class="flex items-center justify-between text-xs py-0.5">
                            <span class="text-slate-500 dark:text-[#94A3B8] font-medium">Owner</span>
                            <span class="text-slate-800 dark:text-white font-semibold text-right truncate pl-2 max-w-[150px]"><?= esc($displayName) ?></span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>
        
        <div id="bottom-sheet-overlay" onclick="toggleBottomSheet()" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden"></div>

    </div>

    <!-- USER GUIDE MODAL (INTERACTIVE STEPPER CAROUSEL) -->
    <div id="userGuideModal" class="spms-modal-backdrop hidden" onclick="if(event.target === this) closeUserGuideModal()">
        <div class="spms-modal-dialog" style="max-width: 780px;">
            <!-- Header -->
            <div class="spms-modal-header">
                <div>
                    <span style="font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #f59e0b; display: block; margin-bottom: 2px;">BSU SPMS VISUAL GUIDE</span>
                    <h3 style="font-size: 18px; font-weight: 800; color: #ffffff; margin: 0;">How to Complete Your Performance Paper</h3>
                </div>
                <button type="button" onclick="closeUserGuideModal()" class="spms-modal-btn-close" title="Close Guide">
                    <svg style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Body -->
            <div class="spms-modal-body custom-scrollbar">
                
                <!-- 4 Interactive Segmented Tabs -->
                <div class="spms-guide-tabs">
                    <div id="guide-tab-1" class="spms-guide-tab active" onclick="showGuideStep(1)">
                        <div class="spms-guide-tab-badge">1</div>
                        <div class="spms-guide-tab-title">Draft Targets</div>
                    </div>
                    <div id="guide-tab-2" class="spms-guide-tab" onclick="showGuideStep(2)">
                        <div class="spms-guide-tab-badge">2</div>
                        <div class="spms-guide-tab-title">Submit Review</div>
                    </div>
                    <div id="guide-tab-3" class="spms-guide-tab" onclick="showGuideStep(3)">
                        <div class="spms-guide-tab-badge">3</div>
                        <div class="spms-guide-tab-title">Attach MOVs</div>
                    </div>
                    <div id="guide-tab-4" class="spms-guide-tab" onclick="showGuideStep(4)">
                        <div class="spms-guide-tab-badge">4</div>
                        <div class="spms-guide-tab-title">Export & Print</div>
                    </div>
                </div>

                <!-- SLIDE 1: DRAFT TARGETS -->
                <div id="guide-slide-1" class="spms-guide-slide active">
                    <!-- Visual Mockup Canvas -->
                    <div class="spms-guide-canvas">
                        <!-- Mini Window Chrome -->
                        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #0d4a32; padding-bottom: 10px; margin-bottom: 12px;">
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <span style="width: 10px; height: 10px; border-radius: 9999px; background-color: #ef4444; display: inline-block;"></span>
                                <span style="width: 10px; height: 10px; border-radius: 9999px; background-color: #f59e0b; display: inline-block;"></span>
                                <span style="width: 10px; height: 10px; border-radius: 9999px; background-color: #10b981; display: inline-block;"></span>
                                <span style="font-size: 11px; font-weight: 700; color: #94a3b8; margin-left: 8px;">BSU SPMS • Interactive Performance Grid</span>
                            </div>
                            <span style="font-size: 10px; font-weight: 700; color: #34d399; background-color: #083b27; padding: 2px 8px; border-radius: 9999px; border: 1px solid #10593b;">
                                ● Auto-Save Active
                            </span>
                        </div>
                        <!-- Mini Spreadsheet Grid -->
                        <div style="border: 1px solid #0d4a32; border-radius: 8px; overflow: hidden; background-color: #032115; font-size: 11px;">
                            <div style="display: grid; grid-template-columns: 1.5fr 2fr 1.5fr; background-color: #062e1e; border-bottom: 1px solid #0d4a32; padding: 8px 12px; font-weight: 800; color: #82c8a6; font-size: 10px; text-transform: uppercase;">
                                <div>Major Final Output (MFO)</div>
                                <div>Success Indicators (Q, E, T)</div>
                                <div>Target Commitment</div>
                            </div>
                            <div style="display: grid; grid-template-columns: 1.5fr 2fr 1.5fr; padding: 10px 12px; border-bottom: 1px solid #0d4a32; color: #e2e8f0; align-items: center;">
                                <div style="font-weight: 600;">Higher Education Services</div>
                                <div style="color: #94a3b8; font-size: 10px;">100% of course syllabi submitted on time</div>
                                <div style="border: 1.5px solid #f59e0b; background-color: #083b27; padding: 4px 8px; border-radius: 6px; color: #ffffff; font-weight: 700; display: flex; align-items: center; justify-content: space-between;">
                                    <span>100% achieved</span>
                                    <span style="color: #f59e0b; font-weight: 900; animation: blink 1s infinite;">|</span>
                                </div>
                            </div>
                            <div style="display: grid; grid-template-columns: 1.5fr 2fr 1.5fr; padding: 10px 12px; color: #64748b; align-items: center;">
                                <div>Research & Innovation</div>
                                <div style="font-size: 10px;">Target research publications completed</div>
                                <div style="color: #5a8b73;">2 papers published</div>
                            </div>
                        </div>
                        <!-- Floating Action Pointer -->
                        <div style="display: flex; justify-content: flex-end; margin-top: 10px;">
                            <div style="background-color: #f59e0b; color: #000000; font-size: 10px; font-weight: 900; padding: 6px 14px; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);">
                                <svg style="width: 12px; height: 12px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                                <span>Save Changes</span>
                            </div>
                        </div>
                    </div>

                    <!-- Step Description Card -->
                    <div class="spms-guide-instruction-card">
                        <h4 style="font-size: 13px; font-weight: 800; color: #f59e0b; margin: 0 0 10px 0; display: flex; align-items: center; gap: 8px;">
                            <span>Step 1: Open & Draft Your Commitments</span>
                        </h4>
                        <ul style="margin: 0; padding-left: 18px; color: #cbd5e1; font-size: 11px; line-height: 1.7; display: flex; flex-direction: column; gap: 6px;">
                            <li><strong style="color: #ffffff;">Action:</strong> Click the bold golden <span style="color: #f59e0b; font-weight: 700;">OPEN & EDIT PAPER</span> button on your dashboard.</li>
                            <li><strong style="color: #ffffff;">Editing:</strong> Click directly into any table cell to enter your Major Final Outputs (MFOs), targets, and success indicators.</li>
                            <li><strong style="color: #ffffff;">Autosave:</strong> Every target and rating is saved directly to your official university performance record.</li>
                        </ul>
                    </div>
                </div>

                <!-- SLIDE 2: SUBMIT FOR REVIEW -->
                <div id="guide-slide-2" class="spms-guide-slide">
                    <!-- Visual Mockup Canvas -->
                    <div class="spms-guide-canvas">
                        <div style="display: grid; grid-template-columns: 1fr auto 1fr; gap: 12px; align-items: center;">
                            <!-- Node 1: Ratee Folder -->
                            <div style="background-color: #032115; border: 1px solid #0d4a32; border-radius: 10px; padding: 14px; text-align: center;">
                                <span style="font-size: 10px; font-weight: 700; color: #f59e0b; display: block; margin-bottom: 4px;">YOUR COMMITMENTS</span>
                                <div style="font-size: 12px; font-weight: 800; color: #ffffff; margin-bottom: 8px;">Target Setting Complete</div>
                                <span style="font-size: 9px; font-weight: 800; background-color: #083b27; color: #34d399; padding: 4px 10px; border-radius: 9999px; border: 1px solid #10593b; display: inline-block;">
                                    Click "Submit Targets"
                                </span>
                            </div>
                            <!-- Arrow Connector -->
                            <div style="display: flex; flex-direction: column; align-items: center;">
                                <svg width="48" height="16" viewBox="0 0 48 16" fill="none">
                                    <line x1="0" y1="8" x2="38" y2="8" stroke="#f59e0b" stroke-width="2" stroke-dasharray="3 3" />
                                    <polygon points="36,4 46,8 36,12" fill="#f59e0b" />
                                </svg>
                                <span style="font-size: 9px; color: #f59e0b; font-weight: 700; margin-top: 4px;">Instant Routing</span>
                            </div>
                            <!-- Node 2: Supervisor Approval -->
                            <div style="background-color: #032115; border: 1px solid #0d4a32; border-radius: 10px; padding: 14px; text-align: center;">
                                <span style="font-size: 10px; font-weight: 700; color: #34d399; display: block; margin-bottom: 4px;">SUPERVISOR / EVALUATOR</span>
                                <div style="font-size: 12px; font-weight: 800; color: #ffffff; margin-bottom: 8px;">Review & Validation</div>
                                <span style="font-size: 9px; font-weight: 800; background-color: #083b27; color: #34d399; padding: 4px 10px; border-radius: 9999px; border: 1px solid #10593b; display: inline-block;">
                                    • TARGET APPROVED ✓
                                </span>
                            </div>
                        </div>
                        <!-- Cascading Rule Banner -->
                        <div style="margin-top: 14px; padding: 10px 14px; border-radius: 8px; background-color: #062e1e; border: 1px solid #0d4a32; display: flex; align-items: center; gap: 10px;">
                            <span style="font-size: 16px;">🌿</span>
                            <div style="font-size: 11px; color: #cbd5e1;">
                                <strong style="color: #34d399;">Institutional Cascading Rule:</strong> Approved superior OPCR commitments cascade downward to provide the mandatory reference basis for subordinates' DPCR/IPCR papers.
                            </div>
                        </div>
                    </div>

                    <!-- Step Description Card -->
                    <div class="spms-guide-instruction-card">
                        <h4 style="font-size: 13px; font-weight: 800; color: #f59e0b; margin: 0 0 10px 0; display: flex; align-items: center; gap: 8px;">
                            <span>Step 2: Submit Targets for Superior Approval</span>
                        </h4>
                        <ul style="margin: 0; padding-left: 18px; color: #cbd5e1; font-size: 11px; line-height: 1.7; display: flex; flex-direction: column; gap: 6px;">
                            <li><strong style="color: #ffffff;">Submission:</strong> Click <span style="color: #34d399; font-weight: 700;">Submit Targets</span> in your paper toolbar once all initial targets are entered.</li>
                            <li><strong style="color: #ffffff;">Notification:</strong> Your designated supervisor (Dean, Chair, Director, or VPAA) receives an instant notification to review and validate your targets.</li>
                            <li><strong style="color: #ffffff;">Locking:</strong> Once approved, the target commitments lock in and the cycle advances to the Evaluation phase.</li>
                        </ul>
                    </div>
                </div>

                <!-- SLIDE 3: ATTACH MOVS -->
                <div id="guide-slide-3" class="spms-guide-slide">
                    <!-- Visual Mockup Canvas -->
                    <div class="spms-guide-canvas">
                        <!-- Mini Row with Paperclip -->
                        <div style="background-color: #032115; border: 1px solid #0d4a32; border-radius: 8px; padding: 10px 14px; display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                            <div style="font-size: 11px; font-weight: 700; color: #ffffff;">
                                Syllabi & Curriculum Targets (AY 2026–2027)
                            </div>
                            <div style="background-color: #083b27; border: 1px solid #10593b; color: #34d399; font-size: 10px; font-weight: 800; padding: 5px 12px; border-radius: 6px; display: flex; align-items: center; gap: 6px;">
                                <svg style="width: 12px; height: 12px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                <span>Attach MOVs (2 Files)</span>
                            </div>
                        </div>
                        <!-- Mini Attached Files List -->
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <div style="background-color: #062e1e; border: 1px solid #0d4a32; border-radius: 8px; padding: 8px 12px; display: flex; align-items: center; justify-content: space-between;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="font-size: 14px;">📄</span>
                                    <div>
                                        <div style="font-size: 11px; font-weight: 700; color: #ffffff;">Approved_Curriculum_Syllabi.pdf</div>
                                        <div style="font-size: 9px; color: #5a8b73;">1.4 MB • Uploaded Sept 15, 2026</div>
                                    </div>
                                </div>
                                <span style="font-size: 9px; font-weight: 800; color: #34d399; background-color: #083b27; padding: 2px 8px; border-radius: 4px;">✓ Verified MOV</span>
                            </div>
                            <div style="background-color: #062e1e; border: 1px solid #0d4a32; border-radius: 8px; padding: 8px 12px; display: flex; align-items: center; justify-content: space-between;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="font-size: 14px;">📄</span>
                                    <div>
                                        <div style="font-size: 11px; font-weight: 700; color: #ffffff;">Dean_Department_Endorsement.pdf</div>
                                        <div style="font-size: 9px; color: #5a8b73;">820 KB • Uploaded Sept 15, 2026</div>
                                    </div>
                                </div>
                                <span style="font-size: 9px; font-weight: 800; color: #34d399; background-color: #083b27; padding: 2px 8px; border-radius: 4px;">✓ Verified MOV</span>
                            </div>
                        </div>
                    </div>

                    <!-- Step Description Card -->
                    <div class="spms-guide-instruction-card">
                        <h4 style="font-size: 13px; font-weight: 800; color: #34d399; margin: 0 0 10px 0; display: flex; align-items: center; gap: 8px;">
                            <span>Step 3: Attach Supporting Evidence & MOVs</span>
                        </h4>
                        <ul style="margin: 0; padding-left: 18px; color: #cbd5e1; font-size: 11px; line-height: 1.7; display: flex; flex-direction: column; gap: 6px;">
                            <li><strong style="color: #ffffff;">Attachment Trigger:</strong> In your paper, click the <span style="color: #34d399; font-weight: 700;">paperclip icon (📎)</span> on any target commitment row.</li>
                            <li><strong style="color: #ffffff;">Accepted Files:</strong> Upload official memos, attendance logs, published articles, certificates, or student evaluations.</li>
                            <li><strong style="color: #ffffff;">Audit Proof:</strong> Evaluators, TWG, and PMT calibrate your final ratings by reviewing these attached files.</li>
                        </ul>
                    </div>
                </div>

                <!-- SLIDE 4: EXPORT & PRINT -->
                <div id="guide-slide-4" class="spms-guide-slide">
                    <!-- Visual Mockup Canvas -->
                    <div class="spms-guide-canvas">
                        <!-- CSC Document Header Mockup -->
                        <div style="background-color: #032115; border: 1px solid #0d4a32; border-radius: 8px; padding: 14px; text-align: center; margin-bottom: 12px;">
                            <span style="font-size: 9px; font-weight: 800; letter-spacing: 0.1em; color: #f59e0b; text-transform: uppercase;">REPUBLIC OF THE PHILIPPINES • CIVIL SERVICE COMMISSION</span>
                            <div style="font-size: 13px; font-weight: 900; color: #ffffff; margin: 4px 0;">BENGUET STATE UNIVERSITY SPMS FORM</div>
                            <span style="font-size: 10px; color: #5a8b73;">Official Institutional Rating Summary with Signature Blocks</span>
                        </div>
                        <!-- Two Action Buttons Preview -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                            <div style="background-color: #083b27; border: 1px solid #11593b; border-radius: 8px; padding: 12px; text-align: center;">
                                <div style="font-size: 16px; margin-bottom: 4px;">📊</div>
                                <div style="font-size: 11px; font-weight: 800; color: #ffffff;">Export Excel (.xlsx)</div>
                                <div style="font-size: 9px; color: #82c8a6; margin-top: 2px;">Formula-ready CSC template</div>
                            </div>
                            <div style="background-color: #083b27; border: 1px solid #11593b; border-radius: 8px; padding: 12px; text-align: center;">
                                <div style="font-size: 16px; margin-bottom: 4px;">🖨️</div>
                                <div style="font-size: 11px; font-weight: 800; color: #ffffff;">Print / PDF (.pdf)</div>
                                <div style="font-size: 9px; color: #82c8a6; margin-top: 2px;">Formatted for hardcopy routing</div>
                            </div>
                        </div>
                        <!-- Signature Blocks Mockup -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; text-align: center; border-top: 1px dashed #0d4a32; padding-top: 10px; font-size: 9px; color: #5a8b73;">
                            <div>Ratee Signature</div>
                            <div>Immediate Supervisor</div>
                            <div>Head of Agency Approval</div>
                        </div>
                    </div>

                    <!-- Step Description Card -->
                    <div class="spms-guide-instruction-card">
                        <h4 style="font-size: 13px; font-weight: 800; color: #f59e0b; margin: 0 0 10px 0; display: flex; align-items: center; gap: 8px;">
                            <span>Step 4: Official CSC Excel Export & Printing</span>
                        </h4>
                        <ul style="margin: 0; padding-left: 18px; color: #cbd5e1; font-size: 11px; line-height: 1.7; display: flex; flex-direction: column; gap: 6px;">
                            <li><strong style="color: #ffffff;">Export Excel:</strong> Automatically compiles and exports your performance commitments into an official CSC-standard spreadsheet.</li>
                            <li><strong style="color: #ffffff;">Print / PDF:</strong> Launches the high-resolution print view formatted specifically for institutional routing and physical signing.</li>
                            <li><strong style="color: #ffffff;">Submission:</strong> Submit your signed copies to the PMT / HRMO for institutional accreditation and CSC compliance.</li>
                        </ul>
                    </div>
                </div>

                <!-- Mandatory CSC 5-Year Retention Compliance Note (Visible on all slides) -->
                <div style="margin-top: 16px; padding: 10px 14px; border-radius: 10px; background-color: #062e1e; border: 1px solid #0d4a32; display: flex; align-items: center; gap: 10px; font-size: 11px;">
                    <span style="font-size: 14px;">🛡️</span>
                    <div style="color: #cbd5e1; line-height: 1.4;">
                        <strong style="color: #ffffff;">CSC 5-Year Record Retention:</strong> Pursuant to CSC & National Archives of the Philippines (NAP) policies, all submitted performance commitments and MOVs are preserved for five (5) years for institutional audit and accreditation.
                    </div>
                </div>

            </div>

            <!-- Footer Navigation Controls -->
            <div class="spms-modal-footer">
                <div id="guide-step-indicator" style="font-size: 11px; font-weight: 800; color: #5a8b73;">
                    Step 1 of 4: Draft Targets
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <button id="guide-btn-prev" type="button" onclick="prevGuideStep()" class="spms-hub-btn-secondary" style="padding: 9px 18px !important; font-size: 11px !important; display: none;">
                        ← Previous
                    </button>
                    <button id="guide-btn-next" type="button" onclick="nextGuideStep()" class="spms-hub-btn-primary" style="padding: 10px 22px !important; font-size: 11px !important;">
                        Next Step →
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MOV ATTACHMENTS MODAL -->
    <div id="movAttachmentsModal" class="spms-modal-backdrop hidden" onclick="if(event.target === this) closeAttachmentsModal()">
        <div class="spms-modal-dialog" style="max-width: 520px;">
            <!-- Header -->
            <div class="spms-modal-header">
                <div>
                    <span style="font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #34d399; display: block; margin-bottom: 2px;">SUPPORTING EVIDENCE</span>
                    <h3 style="font-size: 18px; font-weight: 800; color: #ffffff; margin: 0;">Document MOVs & Attachments</h3>
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
                                    <div style="width: 32px; height: 32px; border-radius: 8px; background-color: #032115; color: #34d399; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <svg style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                    </div>
                                    <div style="display: flex; flex-direction: column; min-width: 0;">
                                        <span style="font-size: 12px; font-weight: 700; color: #ffffff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= esc($att['file_name']) ?>"><?= esc($att['file_name']) ?></span>
                                        <span style="font-size: 10px; color: #5a8b73;">
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
                        <div style="width: 48px; height: 48px; border-radius: 14px; background-color: #062e1e; border: 1px solid #0d4a32; color: #5a8b73; display: flex; align-items: center; justify-content: center; margin-bottom: 12px;">
                            <svg style="width: 24px; height: 24px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                        </div>
                        <h4 style="font-size: 14px; font-weight: 700; color: #ffffff; margin: 0 0 4px 0;">No Files Attached Yet</h4>
                        <p style="font-size: 12px; color: #5a8b73; max-width: 320px; margin: 0; line-height: 1.5;">
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

    <script>
        let currentGuideStep = 1;
        const totalGuideSteps = 4;
        const guideStepTitles = ['', 'Draft Targets', 'Submit Targets', 'Attach Evidence (MOVs)', 'Export & Print'];

        function showGuideStep(step) {
            currentGuideStep = step;
            for (let i = 1; i <= totalGuideSteps; i++) {
                const tab = document.getElementById('guide-tab-' + i);
                const slide = document.getElementById('guide-slide-' + i);
                if (tab) {
                    if (i === step) tab.classList.add('active');
                    else tab.classList.remove('active');
                }
                if (slide) {
                    if (i === step) slide.classList.add('active');
                    else slide.classList.remove('active');
                }
            }
            const prevBtn = document.getElementById('guide-btn-prev');
            const nextBtn = document.getElementById('guide-btn-next');
            const indicator = document.getElementById('guide-step-indicator');

            if (prevBtn) {
                prevBtn.style.display = (step === 1) ? 'none' : 'inline-flex';
            }
            if (nextBtn) {
                if (step === totalGuideSteps) {
                    nextBtn.innerText = 'Got It, Start Working ✓';
                    nextBtn.onclick = closeUserGuideModal;
                } else {
                    nextBtn.innerText = 'Next Step →';
                    nextBtn.onclick = nextGuideStep;
                }
            }
            if (indicator) {
                indicator.innerText = 'Step ' + step + ' of ' + totalGuideSteps + ': ' + guideStepTitles[step];
            }
        }

        function nextGuideStep() {
            if (currentGuideStep < totalGuideSteps) {
                showGuideStep(currentGuideStep + 1);
            }
        }

        function prevGuideStep() {
            if (currentGuideStep > 1) {
                showGuideStep(currentGuideStep - 1);
            }
        }

        function openUserGuideModal() {
            showGuideStep(1);
            document.getElementById('userGuideModal')?.classList.remove('hidden');
        }
        function closeUserGuideModal() {
            document.getElementById('userGuideModal')?.classList.add('hidden');
        }
        function openAttachmentsModal() {
            document.getElementById('movAttachmentsModal')?.classList.remove('hidden');
        }
        function closeAttachmentsModal() {
            document.getElementById('movAttachmentsModal')?.classList.add('hidden');
        }
        function triggerPrintPdf(docUrl) {
            window.open(docUrl + '?print=1', '_blank');
        }

        document.addEventListener('keydown', function(e) {
            const modal = document.getElementById('userGuideModal');
            if (modal && !modal.classList.contains('hidden')) {
                if (e.key === 'ArrowRight') nextGuideStep();
                else if (e.key === 'ArrowLeft') prevGuideStep();
                else if (e.key === 'Escape') closeUserGuideModal();
            }
        });
        document.addEventListener("DOMContentLoaded", function() {
            switchDocTab('mine');
        });

        // Bottom Sheet Logic
        let isSheetOpen = false;
        function toggleBottomSheet() {
            const sheet = document.getElementById('bottom-sheet');
            const overlay = document.getElementById('bottom-sheet-overlay');
            if (!sheet || !overlay) return;
            isSheetOpen = !isSheetOpen;
            
            if (isSheetOpen) {
                sheet.classList.remove('translate-y-[calc(100%-95px)]');
                sheet.classList.add('translate-y-0');
                overlay.classList.remove('hidden');
            } else {
                sheet.classList.add('translate-y-[calc(100%-95px)]');
                sheet.classList.remove('translate-y-0');
                overlay.classList.add('hidden');
            }
        }

        // Real-time Search and Filtering
        function filterDocuments() {
            const searchInput = document.getElementById('doc-search-input');
            const statusSelect = document.getElementById('doc-filter-status');
            if (!searchInput && !statusSelect) return;

            const query = (searchInput?.value || '').toLowerCase().trim();
            const statusFilter = statusSelect?.value || 'all';

            const rows = document.querySelectorAll('#tab-content-mine .doc-row-item');
            let visibleCount = 0;
            rows.forEach(row => {
                const title = row.getAttribute('data-title') || '';
                const status = row.getAttribute('data-status') || '';

                const matchesQuery = !query || title.includes(query) || status.includes(query);
                const matchesStatus = statusFilter === 'all' || status.toLowerCase().includes(statusFilter.toLowerCase());

                if (matchesQuery && matchesStatus) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            const emptyMsg = document.getElementById('search-empty-doc');
            if (emptyMsg) {
                emptyMsg.style.display = (visibleCount === 0 && rows.length > 0) ? 'block' : 'none';
            }
        }

        // Action Menu toggle
        let activeMenuId = null;
        function toggleActionMenu(menuId) {
            const menu = document.getElementById(menuId);
            if (!menu) return;
            if (activeMenuId && activeMenuId !== menuId) {
                document.getElementById(activeMenuId)?.classList.add('hidden');
            }
            menu.classList.toggle('hidden');
            activeMenuId = menu.classList.contains('hidden') ? null : menuId;
        }

        document.addEventListener('click', function(e) {
            if (activeMenuId && !e.target.closest('.group\\/actions')) {
                document.getElementById(activeMenuId)?.classList.add('hidden');
                activeMenuId = null;
            }
        });

        async function submitTargetFolder(folderId, btn) {
            const ok = await window.appConfirm("Submit these targets for approval? You won't be able to edit them until returned.", {
                title: 'Submit Targets',
                confirmText: 'Submit Targets',
                cancelText: 'Keep Editing',
                variant: 'primary'
            });
            if (!ok) return;

            if (btn) {
                btn.innerText = 'Submitting...';
                btn.classList.add('opacity-50', 'cursor-not-allowed');
            }
            const formData = new FormData();
            formData.append('folder_id', folderId);
            apiPost('<?= site_url('folder/submit_target') ?>', formData, {
                onSuccess: () => window.location.reload(),
                onError: async (errMsg) => {
                    await window.appAlert(errMsg || "An error occurred.");
                    window.location.reload();
                }
            });
        }

        function setTargetDocument(docId, folderId, targetState) {
            const formData = new FormData();
            formData.append('doc_id', docId);
            formData.append('folder_id', folderId);
            formData.append('is_target', targetState);
            apiPost('<?= site_url('document/target') ?>', formData, {
                onSuccess: () => window.location.reload()
            });
        }

        let sending = false;

        function triggerCascade(folderId) {
            if (sending) return;

            const selectEl = document.getElementById('team-cascade-select');
            const teamId = selectEl ? selectEl.value : '';
            if (!teamId) {
                window.appAlert("Please select a team to cascade to.");
                return;
            }

            sending = true;
            
            const formData = new FormData();
            formData.append('folder_id', folderId);
            formData.append('team_id', teamId);

            apiPost('<?= site_url('folder/cascade-team') ?>', formData, {
                onSuccess: () => window.location.reload(),
                onError: async (errMsg) => {
                    await window.appAlert(errMsg || "An error occurred.");
                    sending = false;
                    window.location.reload();
                }
            });
        }

        async function triggerUncascade(folderId) {
            if (sending) return;

            const ok = await window.appConfirm("Are you sure you want to revoke the cascade for this evaluation cycle? All cascaded subordinate folders and their assigned draft evaluation forms will be permanently removed.", {
                title: 'Revoke Cascade',
                confirmText: 'Revoke Cascade',
                cancelText: 'Keep Cascaded',
                variant: 'danger'
            });
            if (!ok) return;

            sending = true;

            const formData = new FormData();
            formData.append('folder_id', folderId);

            apiPost('<?= site_url('folder/uncascade-team') ?>', formData, {
                onSuccess: () => window.location.reload(),
                onError: async (errMsg) => {
                    await window.appAlert(errMsg || "An error occurred.");
                    sending = false;
                    window.location.reload();
                }
            });
        }

        function triggerSyncCascade(folderId) {
            if (sending) return;
            const btn = document.getElementById('btn-sync-cascade');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-2 h-3.5 w-3.5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Syncing Team...</span>
                `;
            }
            sending = true;

            const formData = new FormData();
            formData.append('folder_id', folderId);

            apiPost('<?= site_url('folder/sync-team-cascade') ?>', formData, {
                onSuccess: (res) => {
                    window.location.reload();
                },
                onError: async (errMsg) => {
                    await window.appAlert(errMsg || "An error occurred while syncing team members.");
                    sending = false;
                    window.location.reload();
                }
            });
        }

        function triggerRemoveCascadedSubordinate(childFolderId, subordinateName, parentFolderId) {
            if (sending) return;
            window.appConfirm({
                title: 'Remove Subordinate from Cycle',
                message: `Are you sure you want to remove ${subordinateName} from this evaluation cycle? This will delete their target folder and evaluation paper for this cycle.`,
                confirmText: 'Remove Subordinate',
                variant: 'danger'
            }).then(ok => {
                if (!ok) return;
                sending = true;

                const formData = new FormData();
                formData.append('child_folder_id', childFolderId);
                formData.append('parent_folder_id', parentFolderId);

                apiPost('<?= site_url('folder/remove-subordinate-cascade') ?>', formData, {
                    onSuccess: (res) => {
                        window.location.reload();
                    },
                    onError: async (errMsg) => {
                        await window.appAlert(errMsg || "An error occurred while removing subordinate.");
                        sending = false;
                    }
                });
            });
        }

        function archiveFolder(folderId, folderTitle) {
            if (sending) return;
            window.appConfirm({
                title: 'Close & Archive Evaluation Cycle',
                message: `Are you sure you want to close and archive "${folderTitle}"? This will archive the cycle and all subordinate ratee folders, freezing all scores and ratings against further edits.`,
                confirmText: 'Close & Archive Cycle',
                variant: 'warning'
            }).then(ok => {
                if (!ok) return;
                sending = true;

                const formData = new FormData();
                formData.append('folder_id', folderId);

                apiPost('<?= site_url('folder/archive') ?>', formData, {
                    onSuccess: () => {
                        window.location.href = '<?= site_url('folders') ?>';
                    },
                    onError: async (errMsg) => {
                        await window.appAlert(errMsg || "Failed to archive folder.");
                        sending = false;
                    }
                });
            });
        }

        function unarchiveFolder(folderId, folderTitle) {
            if (sending) return;
            window.appConfirm({
                title: 'Restore Folder',
                message: `Restore "${folderTitle}" to your active folders?`,
                confirmText: 'Restore Folder',
                variant: 'info'
            }).then(ok => {
                if (!ok) return;
                sending = true;

                const formData = new FormData();
                formData.append('folder_id', folderId);

                apiPost('<?= site_url('folder/unarchive') ?>', formData, {
                    onSuccess: () => {
                        window.location.href = '<?= site_url('folders') ?>/' + folderId;
                    },
                    onError: async (errMsg) => {
                        await window.appAlert(errMsg || "Failed to restore folder.");
                        sending = false;
                    }
                });
            });
        }

        function switchDocTab(tabId) {
            document.querySelectorAll('.tab-content-doc').forEach(el => {
                el.classList.add('hidden');
                el.classList.remove('flex');
            });
            
            document.querySelectorAll('.tab-btn-doc').forEach(btn => {
                btn.classList.remove('border-emerald-500', 'text-emerald-600', 'dark:text-emerald-400');
                btn.classList.add('border-transparent', 'text-text-muted');
                const badge = btn.querySelector('.tab-badge');
                if(badge) {
                    badge.classList.remove('bg-emerald-500/10', 'text-emerald-600', 'dark:text-emerald-400');
                    badge.classList.add('bg-zinc-100', 'dark:bg-slate-800', 'text-text-muted');
                }
            });

            const target = document.getElementById('tab-content-' + tabId);
            if (target) {
                target.classList.remove('hidden');
                target.classList.add('flex');
            }

            const btnElement = document.getElementById('tab-btn-' + tabId);
            if (btnElement) {
                btnElement.classList.remove('border-transparent', 'text-text-muted');
                btnElement.classList.add('border-emerald-500', 'text-emerald-600', 'dark:text-emerald-400');
                const activeBadge = btnElement.querySelector('.tab-badge');
                if(activeBadge) {
                    activeBadge.classList.remove('bg-zinc-100', 'dark:bg-slate-800', 'text-text-muted');
                    activeBadge.classList.add('bg-emerald-500/10', 'text-emerald-600', 'dark:text-emerald-400');
                }
            }
        }

        async function unsubmitTargetFolder(folderId, btn) {
            const ok = await window.appConfirm("Revoke target submission and return this folder to draft?", {
                title: 'Revoke Submission',
                variant: 'undo',
                confirmText: 'Revoke Submission',
                cancelText: 'Keep Submitted'
            });
            if (!ok) return;

            if (btn) btn.disabled = true;
            const formData = new FormData();
            formData.append('folder_id', folderId);

            apiPost('<?= site_url('folder/unsubmit_target') ?>', formData, {
                onSuccess: () => window.location.reload(),
                onError: async (errMsg) => {
                    await window.appAlert(errMsg || "An error occurred.");
                    if (btn) btn.disabled = false;
                }
            });
        }

        async function approveTargetFromFolder(folderId, releaseToDeans, btn) {
            const msg = releaseToDeans 
                ? "Approve this OPCR and automatically release it to all College Deans as their target basis?"
                : "Approve these targets?";
            const ok = await window.appConfirm(msg, { 
                title: 'Approve Targets',
                confirmText: releaseToDeans ? 'Approve & Release' : 'Approve',
                cancelText: 'Cancel',
                variant: 'success'
            });
            if (!ok) return;

            if (btn) btn.disabled = true;
            const formData = new FormData();
            formData.append('folder_id', folderId);
            if (releaseToDeans) {
                formData.append('release_to_deans', '1');
            }

            apiPost('<?= site_url('folder/approve_target') ?>', formData, {
                onSuccess: async (res) => {
                    if (res && res.message) {
                        await window.appAlert(res.message);
                    }
                    window.location.reload();
                },
                onError: async (errMsg) => {
                    await window.appAlert(errMsg || "An error occurred.");
                    if (btn) btn.disabled = false;
                }
            });
        }
    </script>
<?php endif; ?>