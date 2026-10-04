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
        <div class="flex flex-col flex-1 min-w-0 min-h-0 relative bg-surface lg:rounded-2xl border border-surface-border shadow-xl overflow-visible lg:overflow-hidden dark:bg-[#02160e] dark:border-[#0d4a32]">
            
            <!-- MOBILE ACTIVE EVALUATION CYCLE FOLDER DROPDOWN (PULL-DOWN MENU SPEC) -->
            <?= view('components/mobile_folder_dropdown', [
                'activeFolder'     => $activeFolder ?? null,
                'folders'          => $sidebarFolders ?? [],
                'selectedFolderId' => $activeFolder['id'] ?? null,
                'baseUrl'          => !empty($isArchivedView) ? 'folders/archived' : 'folders',
                'containerClass'   => 'p-3.5 border-b border-surface-border dark:border-[#0d4a32] shrink-0 bg-surface dark:bg-[#02170f]'
            ]) ?>

            <?php if (!empty($groupedGuides) && session()->get('role') !== 'Admin'): ?>
                <!-- TAB NAVIGATION (When superior guide exists) -->
                <div class="flex items-center gap-6 px-6 lg:px-8 border-b border-surface-border dark:border-[#0d4a32] shrink-0 overflow-x-auto custom-scrollbar pt-2 bg-surface dark:bg-[#02170f]">
                    <button id="tab-btn-mine" class="tab-btn-doc whitespace-nowrap pb-3 text-sm font-bold border-b-2 border-emerald-500 text-slate-900 dark:text-white transition-all cursor-pointer flex items-center gap-2" onclick="switchDocTab('mine')">
                        Official Paper
                        <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300 text-[10px] font-extrabold tab-badge transition-colors"><?= count($myDocs) ?></span>
                    </button>

                    <button id="tab-btn-team" class="tab-btn-doc whitespace-nowrap pb-3 text-sm font-medium border-b-2 border-transparent text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white transition-all cursor-pointer flex items-center gap-2" onclick="switchDocTab('team')">
                        Superior Reference Guide
                        <span class="px-2 py-0.5 rounded-full bg-slate-100 dark:bg-[#032316] text-slate-500 dark:text-[#94A3B8] text-[10px] font-extrabold tab-badge transition-colors"><?= array_sum(array_map(fn($g) => count($g['docs']), $groupedGuides)) ?></span>
                    </button>
                </div>
            <?php endif; ?>

            <!-- TAB CONTENTS AREA -->
            <div class="overflow-hidden flex flex-col flex-1 min-h-0 relative lg:h-auto bg-slate-50 dark:bg-[#02160e]">
                
                <!-- 1. MY SUBMISSIONS TAB -->
                <div id="tab-content-mine" class="tab-content-doc flex-1 flex flex-col min-h-0 overflow-y-auto custom-scrollbar p-3 sm:p-5 lg:p-7 pb-20 lg:pb-7 bg-transparent">
                    
                    <style>
                        /* BSU SPMS Official Paper Hub - Adaptive Light & Dark Styles */
                        .spms-hub-card {
                            background-color: #ffffff;
                            border: 1px solid #e2e8f0;
                            border-radius: 16px;
                            padding: 16px 14px;
                            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
                            position: relative;
                            overflow: hidden;
                            width: 100%;
                        }
                        .dark .spms-hub-card {
                            background-color: #032115 !important;
                            border: 1px solid #0d4a32 !important;
                            box-shadow: 0 16px 36px -10px rgba(0, 0, 0, 0.6) !important;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-card {
                                padding: 24px 28px !important;
                            }
                        }
                        @media (min-width: 1024px) {
                            .spms-hub-card {
                                padding: 32px 36px !important;
                            }
                        }

                        .spms-hub-header-wrap {
                            display: flex;
                            flex-direction: column;
                            gap: 4px;
                            margin-bottom: 20px;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-header-wrap {
                                flex-direction: row;
                                justify-content: space-between;
                                align-items: flex-start;
                                gap: 16px;
                                margin-bottom: 28px;
                            }
                        }

                        .spms-hub-header-main {
                            flex: 1;
                            min-width: 0;
                        }

                        .spms-hub-kicker-row {
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            width: 100%;
                            gap: 8px;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-kicker-row {
                                display: block;
                            }
                        }

                        .spms-hub-kicker {
                            color: #059669;
                            font-size: 10px;
                            font-weight: 800;
                            letter-spacing: 0.08em;
                            text-transform: uppercase;
                            margin-bottom: 0;
                            display: block;
                        }
                        .dark .spms-hub-kicker {
                            color: #34d399 !important;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-kicker {
                                font-size: 11px;
                                margin-bottom: 4px;
                            }
                        }

                        .spms-hub-title {
                            color: #0f172a;
                            font-size: 18px;
                            font-weight: 800;
                            letter-spacing: -0.01em;
                            line-height: 1.25;
                            margin: 3px 0 0 0;
                        }
                        .dark .spms-hub-title {
                            color: #ffffff !important;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-title {
                                font-size: 24px;
                                margin-top: 6px;
                            }
                        }
                        @media (min-width: 1024px) {
                            .spms-hub-title {
                                font-size: 27px;
                            }
                        }

                        .spms-hub-subtitle {
                            color: #64748b;
                            font-size: 11px;
                            font-weight: 500;
                            margin-top: 2px;
                            margin-bottom: 0;
                        }
                        .dark .spms-hub-subtitle {
                            color: #5a8b73 !important;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-subtitle {
                                font-size: 13px;
                                margin-top: 6px;
                            }
                        }

                        .spms-hub-subtitle-val {
                            color: #0f172a;
                            font-weight: 700;
                        }
                        .dark .spms-hub-subtitle-val {
                            color: #ffffff !important;
                        }

                        .spms-hub-status-pill {
                            background-color: #ecfdf5;
                            border: 1px solid #a7f3d0;
                            color: #047857;
                            border-radius: 9999px;
                            padding: 3px 8px;
                            font-size: 9px;
                            font-weight: 800;
                            letter-spacing: 0.05em;
                            text-transform: uppercase;
                            display: inline-flex;
                            align-items: center;
                            gap: 5px;
                            white-space: nowrap;
                        }
                        .dark .spms-hub-status-pill {
                            background-color: #083321 !important;
                            border: 1px solid #115337 !important;
                            color: #34d399 !important;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-status-pill {
                                padding: 6px 14px;
                                font-size: 11px;
                                gap: 8px;
                            }
                        }

                        .spms-hub-status-pill-mobile {
                            display: inline-flex !important;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-status-pill-mobile {
                                display: none !important;
                            }
                        }

                        .spms-hub-status-pill-desktop {
                            display: none !important;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-status-pill-desktop {
                                display: block !important;
                            }
                        }

                        .spms-hub-status-dot {
                            width: 5px;
                            height: 5px;
                            border-radius: 9999px;
                            background-color: #10b981;
                            display: inline-block;
                        }
                        .dark .spms-hub-status-dot {
                            background-color: #34d399 !important;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-status-dot {
                                width: 7px;
                                height: 7px;
                            }
                        }

                        .spms-hub-tracker-label {
                            color: #64748b;
                            font-size: 10.5px;
                            font-weight: 800;
                            letter-spacing: 0.08em;
                            text-transform: uppercase;
                            margin-top: 18px;
                            margin-bottom: 12px;
                            display: block;
                        }
                        .dark .spms-hub-tracker-label {
                            color: #5a8b73 !important;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-tracker-label {
                                font-size: 11px;
                                margin-top: 28px;
                                margin-bottom: 16px;
                            }
                        }

                        .spms-hub-stepper-row {
                            display: flex;
                            align-items: center;
                            gap: 6px;
                            overflow-x: auto;
                            padding-bottom: 8px;
                            margin-bottom: 18px;
                            -webkit-overflow-scrolling: touch;
                            scrollbar-width: thin;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-stepper-row {
                                gap: 12px;
                                padding-bottom: 6px;
                                margin-bottom: 28px;
                            }
                        }

                        .spms-hub-stepper-arrow {
                            width: 28px;
                            height: 12px;
                            flex-shrink: 0;
                            margin: 0 4px;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-stepper-arrow {
                                width: 36px;
                                height: 12px;
                                margin: 0 6px;
                            }
                        }
                        @media (min-width: 1280px) {
                            .spms-hub-stepper-arrow {
                                width: 48px;
                                height: 12px;
                                margin: 0 8px;
                            }
                        }

                        .spms-hub-circle-active {
                            width: 26px;
                            height: 26px;
                            border-radius: 9999px;
                            background-color: #f59e0b;
                            border: none;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            flex-shrink: 0;
                        }

                        .spms-hub-circle-active-dot {
                            width: 8px;
                            height: 8px;
                            border-radius: 9999px;
                            background-color: #ffffff;
                        }
                        .dark .spms-hub-circle-active-dot {
                            background-color: #032115 !important;
                        }

                        .spms-hub-circle-inactive {
                            width: 26px;
                            height: 26px;
                            border-radius: 9999px;
                            border: 1.5px solid #cbd5e1;
                            background-color: #f8fafc;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            flex-shrink: 0;
                        }
                        .dark .spms-hub-circle-inactive {
                            border: 1.5px solid #145235 !important;
                            background-color: transparent !important;
                        }

                        .spms-hub-circle-completed {
                            width: 26px;
                            height: 26px;
                            border-radius: 9999px;
                            background-color: #10b981;
                            border: none;
                            color: #ffffff;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            flex-shrink: 0;
                        }

                        .spms-hub-step-text-active {
                            color: #0f172a;
                            font-size: 11.5px;
                            font-weight: 700;
                            white-space: nowrap;
                            margin-left: 6px;
                        }
                        .dark .spms-hub-step-text-active {
                            color: #ffffff !important;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-step-text-active {
                                font-size: 13px;
                                margin-left: 8px;
                            }
                        }

                        .spms-hub-step-text-inactive {
                            color: #94a3b8;
                            font-size: 11.5px;
                            font-weight: 500;
                            white-space: nowrap;
                            margin-left: 6px;
                        }
                        .dark .spms-hub-step-text-inactive {
                            color: #5a8b73 !important;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-step-text-inactive {
                                font-size: 13px;
                                margin-left: 8px;
                            }
                        }

                        .spms-hub-stat-grid {
                            display: flex;
                            flex-direction: column;
                            gap: 8px;
                            margin-bottom: 14px;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-stat-grid {
                                display: grid;
                                grid-template-columns: repeat(3, minmax(0, 1fr));
                                gap: 12px;
                                margin-bottom: 22px;
                            }
                        }
                        @media (min-width: 768px) {
                            .spms-hub-stat-grid {
                                gap: 16px;
                                margin-bottom: 28px;
                            }
                        }

                        .spms-hub-stat-tile {
                            background-color: #f8fafc;
                            border: 1px solid #e2e8f0;
                            border-radius: 12px;
                            padding: 11px 14px;
                            display: flex;
                            flex-direction: row;
                            justify-content: space-between;
                            align-items: center;
                            gap: 12px;
                            min-height: auto;
                            min-width: 0;
                            overflow: hidden;
                        }
                        .dark .spms-hub-stat-tile {
                            background-color: #062e1e !important;
                            border: 1px solid #0d4a32 !important;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-stat-tile {
                                flex-direction: column;
                                justify-content: space-between;
                                align-items: flex-start;
                                padding: 14px 14px;
                                border-radius: 14px;
                                min-height: 115px;
                                gap: 0;
                            }
                        }
                        @media (min-width: 1024px) {
                            .spms-hub-stat-tile {
                                padding: 16px 16px;
                                min-height: 125px;
                            }
                        }

                        .spms-hub-tile-label {
                            color: #64748b;
                            font-size: 10.5px;
                            font-weight: 600;
                            margin: 0;
                            white-space: nowrap;
                            overflow: hidden;
                            text-overflow: ellipsis;
                        }
                        .dark .spms-hub-tile-label {
                            color: #5a8b73 !important;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-tile-label {
                                font-size: 11px;
                            }
                        }

                        .spms-hub-tile-value {
                            color: #0f172a;
                            font-size: 12px;
                            font-weight: 700;
                            letter-spacing: -0.01em;
                            font-variant-numeric: tabular-nums;
                            margin-top: 2px;
                            margin-bottom: 0;
                            white-space: nowrap;
                            overflow: hidden;
                            text-overflow: ellipsis;
                        }
                        .dark .spms-hub-tile-value {
                            color: #ffffff !important;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-tile-value {
                                font-size: 12.5px;
                                margin-top: 4px;
                            }
                        }
                        @media (min-width: 1024px) {
                            .spms-hub-tile-value {
                                font-size: 13px;
                                margin-top: 5px;
                            }
                        }

                        .spms-hub-window-pill {
                            background-color: #ecfdf5;
                            border: 1px solid #a7f3d0;
                            color: #047857;
                            font-size: 9.5px;
                            font-weight: 800;
                            padding: 3px 8px;
                            border-radius: 9999px;
                            display: inline-flex;
                            align-items: center;
                            gap: 5px;
                            width: fit-content;
                            margin-top: 0;
                            white-space: nowrap;
                            flex-shrink: 0;
                        }
                        .dark .spms-hub-window-pill {
                            background-color: #083b27 !important;
                            border: 1px solid #10593b !important;
                            color: #34d399 !important;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-window-pill {
                                font-size: 11px;
                                padding: 4px 12px;
                                gap: 7px;
                                margin-top: 14px;
                            }
                        }

                        .spms-hub-tile-dash {
                            width: 20px;
                            height: 2px;
                            background-color: #cbd5e1;
                            border-radius: 9999px;
                            margin-top: 0;
                            flex-shrink: 0;
                        }
                        .dark .spms-hub-tile-dash {
                            background-color: #1e5a3e !important;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-tile-dash {
                                width: 34px;
                                height: 3px;
                                margin-top: 20px;
                            }
                        }

                        .spms-hub-status-text {
                            font-size: 9.5px;
                            font-weight: 700;
                            letter-spacing: -0.01em;
                            display: block;
                            white-space: nowrap;
                            overflow: hidden;
                            text-overflow: ellipsis;
                            margin-top: 0;
                            flex-shrink: 0;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-status-text {
                                font-size: 11px;
                                margin-top: 8px;
                            }
                        }

                        .spms-hub-tile-btn {
                            background-color: #ecfdf5;
                            border: 1px solid #a7f3d0;
                            color: #047857;
                            font-size: 11px;
                            font-weight: 600;
                            padding: 6px 12px;
                            border-radius: 8px;
                            cursor: pointer;
                            width: fit-content;
                            margin-top: 0;
                            text-decoration: none;
                            display: inline-block;
                            transition: all 0.15s ease;
                            white-space: nowrap;
                            flex-shrink: 0;
                        }
                        .spms-hub-tile-btn:hover {
                            background-color: #d1fae5;
                            color: #065f46;
                        }
                        .dark .spms-hub-tile-btn {
                            background-color: #083b27 !important;
                            border: 1px solid #11593b !important;
                            color: #82c8a6 !important;
                        }
                        .dark .spms-hub-tile-btn:hover {
                            background-color: #0c4d33 !important;
                            color: #ffffff !important;
                            border-color: #176a46 !important;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-tile-btn {
                                font-size: 12px;
                                padding: 6px 16px;
                                margin-top: 14px;
                            }
                        }

                        .spms-hub-actions-bar {
                            display: grid;
                            grid-template-columns: repeat(3, minmax(0, 1fr));
                            gap: 8px;
                            width: 100%;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-actions-bar {
                                display: flex;
                                flex-direction: row;
                                flex-wrap: wrap;
                                align-items: center;
                                gap: 6px;
                            }
                        }
                        @media (min-width: 1400px) {
                            .spms-hub-actions-bar {
                                gap: 10px;
                            }
                        }

                        .spms-hub-btn-primary {
                            grid-column: span 3;
                            width: 100%;
                            background-color: #f59e0b;
                            color: #000000;
                            font-size: 11px;
                            font-weight: 900;
                            letter-spacing: 0.04em;
                            text-transform: uppercase;
                            padding: 11px 16px;
                            border-radius: 9px;
                            border: none;
                            cursor: pointer;
                            text-decoration: none;
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            transition: all 0.15s ease;
                            white-space: nowrap;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-btn-primary {
                                grid-column: auto;
                                width: auto;
                                font-size: 10.5px;
                                padding: 10px 12px;
                                border-radius: 8px;
                            }
                        }
                        @media (min-width: 1400px) {
                            .spms-hub-btn-primary {
                                font-size: 11.5px;
                                padding: 12px 18px;
                                border-radius: 9px;
                            }
                        }
                        .spms-hub-btn-primary:hover {
                            background-color: #e08e06;
                            color: #000000;
                        }
                        .spms-hub-banner {
                            background-color: #ecfdf5;
                            border: 1px solid #a7f3d0;
                            color: #065f46;
                        }
                        .dark .spms-hub-banner {
                            background-color: #042a1b !important;
                            border: 1px solid #0d4a32 !important;
                            color: #d1fae5 !important;
                        }
                        .spms-hub-banner-icon {
                            background-color: #d1fae5;
                            border: 1px solid #a7f3d0;
                            color: #059669;
                        }
                        .dark .spms-hub-banner-icon {
                            background-color: #073824 !important;
                            border: 1px solid #116340 !important;
                            color: #34d399 !important;
                        }
                        .spms-hub-banner-title {
                            color: #065f46;
                        }
                        .dark .spms-hub-banner-title {
                            color: #34d399 !important;
                        }

                        .spms-hub-btn-secondary {
                            width: 100%;
                            background-color: #f1f5f9;
                            border: 1px solid #cbd5e1;
                            color: #1e293b;
                            font-size: 10.5px;
                            font-weight: 700;
                            padding: 8px 4px;
                            border-radius: 8px;
                            cursor: pointer;
                            text-decoration: none;
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            text-align: center;
                            transition: all 0.15s ease;
                            white-space: nowrap;
                            box-sizing: border-box;
                            line-height: 1.25;
                        }
                        .dark .spms-hub-btn-secondary {
                            background-color: #062e1e !important;
                            border: 1px solid #10593b !important;
                            color: #ffffff !important;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-btn-secondary {
                                width: auto;
                                font-size: 10.5px;
                                padding: 10px 10px;
                                border-radius: 8px;
                            }
                        }
                        @media (min-width: 1400px) {
                            .spms-hub-btn-secondary {
                                font-size: 11px;
                                padding: 11px 14px;
                                border-radius: 9px;
                            }
                        }
                        .spms-hub-btn-secondary:hover {
                            background-color: #e2e8f0;
                            border-color: #94a3b8;
                            color: #0f172a;
                        }
                        .dark .spms-hub-btn-secondary:hover {
                            background-color: #0c4d33 !important;
                            border-color: #176a46 !important;
                            color: #ffffff !important;
                        }

                        /* Standalone secondary button in actions bar: spans full width on mobile, auto width on desktop */
                        .spms-hub-actions-bar > .spms-hub-btn-secondary:last-child:nth-child(2),
                        .spms-hub-btn-secondary-full {
                            grid-column: span 3;
                            width: 100%;
                            padding: 10px 16px;
                            font-size: 11px;
                        }
                        @media (min-width: 640px) {
                            .spms-hub-actions-bar > .spms-hub-btn-secondary:last-child:nth-child(2),
                            .spms-hub-btn-secondary-full {
                                width: auto;
                                grid-column: auto;
                                padding: 10px 12px;
                                font-size: 10.5px;
                            }
                        }
                        @media (min-width: 1400px) {
                            .spms-hub-actions-bar > .spms-hub-btn-secondary:last-child:nth-child(2),
                            .spms-hub-btn-secondary-full {
                                padding: 11px 14px;
                                font-size: 11px;
                            }
                        }

                        @media (max-width: 1023px) {
                            #bottom-sheet.spms-bottom-sheet-collapsed {
                                transform: translateY(calc(100% - 46px)) !important;
                            }
                            #bottom-sheet.spms-bottom-sheet-expanded {
                                transform: translateY(0) !important;
                            }
                        }

                        /* SPMS Modal Design System */
                        .spms-modal-backdrop {
                            position: fixed;
                            top: 0;
                            left: 0;
                            right: 0;
                            bottom: 0;
                            z-index: 9999;
                            background-color: rgba(0, 0, 0, 0.6);
                            backdrop-filter: blur(6px);
                            -webkit-backdrop-filter: blur(6px);
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            padding: 16px;
                        }
                        .spms-modal-backdrop.hidden {
                            display: none !important;
                        }
                        .spms-modal-dialog {
                            background-color: #ffffff;
                            border: 1px solid #cbd5e1;
                            border-radius: 16px;
                            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
                            color: #0f172a;
                            width: 100%;
                            max-height: 90vh;
                            display: flex;
                            flex-direction: column;
                            overflow: hidden;
                            position: relative;
                        }
                        .dark .spms-modal-dialog {
                            background-color: #032115 !important;
                            border: 1px solid #0d4a32 !important;
                            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.85) !important;
                            color: #ffffff !important;
                        }
                        .spms-modal-header {
                            background-color: #f8fafc;
                            border-bottom: 1px solid #e2e8f0;
                            padding: 20px 24px;
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            flex-shrink: 0;
                        }
                        .dark .spms-modal-header {
                            background-color: #02170f !important;
                            border-bottom: 1px solid #0d4a32 !important;
                        }
                        .spms-modal-body {
                            background-color: #ffffff;
                            padding: 24px;
                            overflow-y: auto;
                            flex: 1 1 auto;
                        }
                        .dark .spms-modal-body {
                            background-color: #032115 !important;
                        }
                        .spms-modal-section {
                            background-color: #f8fafc;
                            border: 1px solid #e2e8f0;
                            border-radius: 12px;
                            padding: 16px;
                            margin-bottom: 16px;
                        }
                        .dark .spms-modal-section {
                            background-color: #062e1e !important;
                            border: 1px solid #0d4a32 !important;
                        }
                        .spms-modal-card {
                            background-color: #f1f5f9;
                            border: 1px solid #cbd5e1;
                            border-radius: 8px;
                            padding: 12px 14px;
                            color: #1e293b;
                        }
                        .dark .spms-modal-card {
                            background-color: #083b27 !important;
                            border: 1px solid #10593b !important;
                            color: #d1fae5 !important;
                        }
                        .spms-modal-footer {
                            background-color: #f8fafc;
                            border-top: 1px solid #e2e8f0;
                            padding: 16px 24px;
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            flex-shrink: 0;
                        }
                        .dark .spms-modal-footer {
                            background-color: #02170f !important;
                            border-top: 1px solid #0d4a32 !important;
                        }
                        .spms-modal-btn-close {
                            width: 32px;
                            height: 32px;
                            border-radius: 8px;
                            background-color: #f1f5f9;
                            border: 1px solid #cbd5e1;
                            color: #64748b;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            cursor: pointer;
                            transition: all 0.15s ease;
                        }
                        .spms-modal-btn-close:hover {
                            background-color: #e2e8f0;
                            color: #0f172a;
                        }
                        .dark .spms-modal-btn-close {
                            background-color: #083b27 !important;
                            border: 1px solid #11593b !important;
                            color: #94a3b8 !important;
                        }
                        .dark .spms-modal-btn-close:hover {
                            background-color: #0c4d33 !important;
                            color: #ffffff !important;
                            border-color: #176a46 !important;
                        }

                        /* Interactive Stepper Carousel Styles */
                        .spms-guide-tabs {
                            display: grid;
                            grid-template-columns: repeat(4, 1fr);
                            gap: 8px;
                            margin-bottom: 20px;
                        }
                        @media (max-width: 640px) {
                            .spms-guide-tabs {
                                grid-template-columns: repeat(2, 1fr);
                            }
                        }
                        .spms-guide-tab {
                            background-color: #f8fafc;
                            border: 1px solid #e2e8f0;
                            border-radius: 10px;
                            padding: 10px 12px;
                            cursor: pointer;
                            display: flex;
                            align-items: center;
                            gap: 8px;
                            transition: all 0.15s ease;
                            text-align: left;
                        }
                        .spms-guide-tab:hover {
                            background-color: #f1f5f9;
                            border-color: #cbd5e1;
                        }
                        .spms-guide-tab.active {
                            background-color: #ecfdf5;
                            border-color: #10b981;
                            box-shadow: 0 0 12px rgba(16, 185, 129, 0.2);
                        }
                        .dark .spms-guide-tab {
                            background-color: #062e1e !important;
                            border: 1px solid #0d4a32 !important;
                        }
                        .dark .spms-guide-tab:hover {
                            background-color: #083b27 !important;
                            border-color: #156643 !important;
                        }
                        .dark .spms-guide-tab.active {
                            background-color: #083b27 !important;
                            border-color: #f59e0b !important;
                            box-shadow: 0 0 12px rgba(245, 158, 11, 0.25) !important;
                        }
                        .spms-guide-tab-badge {
                            width: 22px;
                            height: 22px;
                            border-radius: 9999px;
                            background-color: #e2e8f0;
                            border: 1px solid #cbd5e1;
                            color: #475569;
                            font-size: 11px;
                            font-weight: 800;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            flex-shrink: 0;
                        }
                        .spms-guide-tab.active .spms-guide-tab-badge {
                            background-color: #10b981;
                            border-color: #10b981;
                            color: #ffffff;
                        }
                        .dark .spms-guide-tab-badge {
                            background-color: #032115 !important;
                            border: 1px solid #0d4a32 !important;
                            color: #82c8a6 !important;
                        }
                        .dark .spms-guide-tab.active .spms-guide-tab-badge {
                            background-color: #f59e0b !important;
                            border-color: #f59e0b !important;
                            color: #000000 !important;
                        }
                        .spms-guide-tab-title {
                            font-size: 11px;
                            font-weight: 700;
                            color: #64748b;
                            line-height: 1.2;
                        }
                        .spms-guide-tab.active .spms-guide-tab-title {
                            color: #047857;
                        }
                        .dark .spms-guide-tab-title {
                            color: #94a3b8 !important;
                        }
                        .dark .spms-guide-tab.active .spms-guide-tab-title {
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
                            background-color: #f8fafc;
                            border: 1px solid #e2e8f0;
                            border-radius: 12px;
                            padding: 18px;
                            margin-bottom: 16px;
                            position: relative;
                            overflow: hidden;
                        }
                        .dark .spms-guide-canvas {
                            background-color: #02170f !important;
                            border: 1px solid #0d4a32 !important;
                        }
                        .spms-guide-instruction-card {
                            background-color: #f8fafc;
                            border: 1px solid #e2e8f0;
                            border-radius: 12px;
                            padding: 16px 18px;
                        }
                        .dark .spms-guide-instruction-card {
                            background-color: #062e1e !important;
                            border: 1px solid #0d4a32 !important;
                        }
                        .spms-guide-instruction-card ul {
                            color: #334155;
                        }
                        .dark .spms-guide-instruction-card ul {
                            color: #cbd5e1 !important;
                        }
                        .spms-guide-instruction-card strong {
                            color: #0f172a;
                        }
                        .dark .spms-guide-instruction-card strong {
                            color: #ffffff !important;
                        }
                        .spms-guide-retention-banner {
                            background-color: #ecfdf5;
                            border: 1px solid #a7f3d0;
                            color: #065f46;
                            margin-top: 16px;
                            padding: 10px 14px;
                            border-radius: 10px;
                            display: flex;
                            align-items: center;
                            gap: 10px;
                            font-size: 11px;
                        }
                        .dark .spms-guide-retention-banner {
                            background-color: #062e1e !important;
                            border: 1px solid #0d4a32 !important;
                            color: #cbd5e1 !important;
                        }
                        .spms-guide-retention-banner strong {
                            color: #064e3b;
                        }
                        .dark .spms-guide-retention-banner strong {
                            color: #ffffff !important;
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
                            <div class="mb-3 sm:mb-5">
                                <div class="flex items-center justify-between gap-2 mb-1 sm:mb-2">
                                    <span class="spms-hub-kicker text-emerald-600 dark:text-[#34d399]">INSTITUTIONAL EVALUATION CYCLE</span>
                                    <div class="spms-hub-status-pill border-emerald-200 text-emerald-700 bg-emerald-50 dark:border-[#059669] dark:text-[#34d399] dark:bg-[#064e3b]">
                                        <span class="spms-hub-status-dot bg-emerald-500 dark:bg-[#34d399]"></span>
                                        <span>TARGET PHASE ACTIVE</span>
                                    </div>
                                </div>
                                <h2 class="spms-hub-title"><?= esc($activeFolder['title'] ?: 'Untitled Cycle') ?></h2>
                                <p class="spms-hub-subtitle">
                                    Cycle Coordinator: <span class="spms-hub-subtitle-val"><?= esc(session()->get('name') ?? 'System Administrator') ?></span> (Administrator)
                                </p>
                            </div>

                            <!-- TIMELINE INFORMATION BANNER -->
                            <div class="spms-hub-banner p-2.5 sm:p-4 rounded-xl mb-3 sm:mb-5 flex items-start gap-2.5 sm:gap-3.5 shadow-xs">
                                <div class="spms-hub-banner-icon w-7 h-7 sm:w-8 sm:h-8 rounded-lg flex items-center justify-center shrink-0 mt-0.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="text-[10px] sm:text-xs leading-relaxed">
                                    <span class="spms-hub-banner-title font-extrabold block mb-0.5 text-[11px] sm:text-sm">Target Setting Window is Active</span>
                                    As Administrator, you oversee the evaluation schedule and cascade targets downward. You have <strong>no personal evaluation paper to fill</strong>. Use the <strong>Cascade Management</strong> panel on the right to distribute this cycle to the <strong>Vice President (Executive Team)</strong> so OPCR commitments can be formulated.
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
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 dark:bg-[#34d399] inline-block"></span>
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
                                            <span class="spms-hub-status-text text-emerald-600 dark:text-[#34d399] font-medium">Distribution Active</span>
                                        <?php else: ?>
                                            <span class="spms-hub-status-text text-amber-600 dark:text-[#f59e0b] font-medium">Select Team on Right</span>
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
                                <button type="button" onclick="openUserGuideModal()" class="spms-hub-btn-secondary spms-hub-btn-secondary-full">
                                    SPMS Cascade Guide
                                </button>
                            </div>
                        </div>

                    <?php elseif (empty($myDocs)): ?>
                        <div class="border-2 border-dashed border-slate-200 dark:border-[#0c4a33] bg-slate-50/50 dark:bg-[#032316]/30 rounded-2xl w-full flex-1 flex flex-col items-center justify-center p-8 sm:p-12 my-2 min-h-[380px] text-center">
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
                            <div class="spms-hub-header-wrap">
                                <div class="spms-hub-header-main">
                                    <div class="spms-hub-kicker-row">
                                        <span class="spms-hub-kicker">OFFICIAL PERFORMANCE PAPER</span>
                                        <div class="spms-hub-status-pill spms-hub-status-pill-mobile">
                                            <span class="spms-hub-status-dot"></span>
                                            <span><?= esc($statusBadgeText) ?></span>
                                        </div>
                                    </div>
                                    <h2 class="spms-hub-title"><?= esc($officialPaperName) ?></h2>
                                    <p class="spms-hub-subtitle">
                                        Current Rating Period: <span class="spms-hub-subtitle-val"><?= esc($ratingPeriodText) ?></span>
                                    </p>
                                </div>
                                <div class="spms-hub-status-pill-desktop shrink-0">
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
                                        <svg class="spms-hub-stepper-arrow" viewBox="0 0 60 12" fill="none">
                                            <line x1="0" y1="6" x2="48" y2="6" stroke="#f59e0b" stroke-width="2" stroke-dasharray="3 3" />
                                            <polygon points="46,2 56,6 46,10" fill="#f59e0b" />
                                        </svg>
                                    <?php elseif ($currentStepIndex > 1): ?>
                                        <svg class="spms-hub-stepper-arrow" viewBox="0 0 60 12" fill="none">
                                            <line x1="0" y1="6" x2="48" y2="6" stroke="#10b981" stroke-width="2" />
                                            <polygon points="46,2 56,6 46,10" fill="#10b981" />
                                        </svg>
                                    <?php else: ?>
                                        <svg class="spms-hub-stepper-arrow text-slate-300 dark:text-[#145235]" viewBox="0 0 60 12" fill="none">
                                            <line x1="0" y1="6" x2="48" y2="6" stroke="currentColor" stroke-width="1.5" />
                                            <polygon points="46,2 56,6 46,10" fill="currentColor" />
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
                                        <svg class="spms-hub-stepper-arrow" viewBox="0 0 60 12" fill="none">
                                            <line x1="0" y1="6" x2="48" y2="6" stroke="#f59e0b" stroke-width="2" stroke-dasharray="3 3" />
                                            <polygon points="46,2 56,6 46,10" fill="#f59e0b" />
                                        </svg>
                                    <?php elseif ($currentStepIndex > 2): ?>
                                        <svg class="spms-hub-stepper-arrow" viewBox="0 0 60 12" fill="none">
                                            <line x1="0" y1="6" x2="48" y2="6" stroke="#10b981" stroke-width="2" />
                                            <polygon points="46,2 56,6 46,10" fill="#10b981" />
                                        </svg>
                                    <?php else: ?>
                                        <svg class="spms-hub-stepper-arrow text-slate-300 dark:text-[#145235]" viewBox="0 0 60 12" fill="none">
                                            <line x1="0" y1="6" x2="48" y2="6" stroke="currentColor" stroke-width="1.5" />
                                            <polygon points="46,2 56,6 46,10" fill="currentColor" />
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
                                        <svg class="spms-hub-stepper-arrow" viewBox="0 0 60 12" fill="none">
                                            <line x1="0" y1="6" x2="48" y2="6" stroke="#f59e0b" stroke-width="2" stroke-dasharray="3 3" />
                                            <polygon points="46,2 56,6 46,10" fill="#f59e0b" />
                                        </svg>
                                    <?php elseif ($currentStepIndex > 3): ?>
                                        <svg class="spms-hub-stepper-arrow" viewBox="0 0 60 12" fill="none">
                                            <line x1="0" y1="6" x2="48" y2="6" stroke="#10b981" stroke-width="2" />
                                            <polygon points="46,2 56,6 46,10" fill="#10b981" />
                                        </svg>
                                    <?php else: ?>
                                        <svg class="spms-hub-stepper-arrow text-slate-300 dark:text-[#145235]" viewBox="0 0 60 12" fill="none">
                                            <line x1="0" y1="6" x2="48" y2="6" stroke="currentColor" stroke-width="1.5" />
                                            <polygon points="46,2 56,6 46,10" fill="currentColor" />
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
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 dark:bg-[#34d399] inline-block"></span>
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
            $userPos = strtolower(session()->get('position') ?? '');
            $userDocType = strtolower(session()->get('doc_type') ?? '');
            $docType = strtolower($ownerDocType ?? 'ipcr');
            $isOpcrFolder = ($docType === 'opcr') 
                         || str_contains(strtoupper($activeFolder['title'] ?? ''), 'OPCR')
                         || str_contains($userPos, 'vice president')
                         || str_contains($userPos, 'vpaa')
                         || str_contains($userPos, 'president')
                         || ($userDocType === 'opcr');

            if (!$isOpcrFolder && !empty($documents)) {
                foreach ($documents as $d) {
                    if (stripos($d['title'] ?? '', 'opcr') !== false) {
                        $isOpcrFolder = true;
                        break;
                    }
                }
            }

            $isSupervisorOrChair = in_array(session()->get('role'), ['Supervisor', 'Admin']) 
                                || !empty($isOwnerChair) 
                                || str_contains($userPos, 'chair') 
                                || str_contains($userPos, 'head')
                                || str_contains($userPos, 'dean')
                                || $isOpcrFolder;
            $canCascade = $isUserAdmin || ($isSupervisorOrChair && ($activeFolder['user_id'] == session()->get('user_id'))); 
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
        <div id="bottom-sheet" class="custom-scrollbar fixed inset-x-0 bottom-0 z-50 shadow-2xl lg:shadow-xl rounded-t-3xl lg:rounded-2xl transition-transform duration-300 transform spms-bottom-sheet-collapsed lg:static lg:translate-y-0 lg:w-80 lg:shrink-0 flex flex-col p-4 sm:p-5 gap-4 overflow-hidden lg:overflow-y-auto max-h-[85vh] lg:max-h-none bg-surface dark:bg-[#02160e] border border-surface-border dark:border-[#0d4a32]">
            
            <div class="lg:hidden flex justify-center py-2 cursor-pointer touch-none" onclick="toggleBottomSheet()">
                <div class="w-12 h-1 bg-zinc-300 dark:bg-slate-600 rounded-full"></div>
            </div>

            <?php if ($canCascade): ?>
                <!-- CASCADE MANAGEMENT SECTION (For Admins & Supervisors) -->
                <div class="flex flex-col gap-3 flex-1 min-h-0">
                    <?php $cascadedTeamId = $activeFolder['routing_preset_id'] ?? null; ?>
                    <div class="flex items-center justify-between shrink-0">
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
                    <?php elseif ($cascadedTeamId || !empty($cascadedChildren)): ?>
                        <div class="relative w-full shrink-0">
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

                        <div class="flex items-center justify-between px-1 shrink-0">
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

                        <button onclick="triggerUncascade('<?= $activeFolder['id'] ?>')" class="w-full py-2.5 text-rose-600 dark:text-rose-400 hover:text-white border border-rose-300 dark:border-[#361a1f] bg-rose-50 dark:bg-[#1c1214] hover:bg-rose-600 dark:hover:bg-[#261619] rounded-xl transition-colors cursor-pointer flex justify-center items-center gap-1.5 font-bold text-xs uppercase tracking-wider shrink-0">
                            Revoke Cascade
                        </button>

                        <?php if (!empty($cascadedChildren)): ?>
                            <?php 
                                $subPageSize = 6;
                                $totalSubPages = ceil(count($cascadedChildren) / $subPageSize) ?: 1;
                            ?>
                            <div class="mt-1 flex flex-col gap-2 flex-1 min-h-0">
                                <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-wider text-text-muted shrink-0">
                                    <span>Cascaded Subordinates</span>
                                    <span id="subordinates-count-badge" class="px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-[#062e1e] text-emerald-700 dark:text-[#34d399] border border-emerald-200 dark:border-[#0d4a32] font-extrabold text-[9px]"><?= count($cascadedChildren) ?></span>
                                </div>

                                <div id="cascaded-subordinates-list" class="space-y-2 overflow-y-auto custom-scrollbar flex-1 min-h-0 pr-0.5">
                                    <?php foreach ($cascadedChildren as $cIndex => $child): ?>
                                        <?php 
                                            $isPending = ($child['status'] === \App\Enums\FolderStatus::PENDING_TARGET_APPROVAL->value);
                                            $isApproved = ($child['status'] === \App\Enums\FolderStatus::TARGET_APPROVED->value);
                                            $fInit = mb_substr($child['first_name'] ?? '', 0, 1);
                                            $lInit = mb_substr($child['last_name'] ?? '', 0, 1);
                                            $initials = strtoupper($fInit . $lInit) ?: 'U';
                                        ?>
                                        <div class="subordinate-card <?= ($cIndex >= $subPageSize) ? 'hidden' : '' ?> p-3 rounded-xl border border-slate-200 dark:border-[#1e382b] bg-slate-50 dark:bg-[#0c1510] hover:border-emerald-500/40 flex flex-col gap-1.5 text-xs transition-all duration-150" data-subordinate-index="<?= $cIndex ?>">
                                            <div class="flex items-center justify-between gap-1.5">
                                                <div class="flex items-center gap-2.5 min-w-0">
                                                    <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-[#062e1e] border border-emerald-200 dark:border-[#0d4a32] text-emerald-700 dark:text-[#34d399] font-black text-[11px] flex items-center justify-center shrink-0">
                                                        <?= esc($initials) ?>
                                                    </div>
                                                    <div class="min-w-0">
                                                        <span class="font-bold text-slate-800 dark:text-white block text-[11px] leading-tight truncate">
                                                            <?= esc($child['first_name'] . ' ' . $child['last_name']) ?>
                                                        </span>
                                                        <span class="text-[9px] text-slate-500 dark:text-slate-400 block truncate">
                                                            <?= esc($child['position'] ?: $child['email']) ?>
                                                        </span>
                                                    </div>
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
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div id="subordinates-pagination" class="shrink-0 pt-2 flex items-center justify-between text-xs <?= ($totalSubPages <= 1) ? 'hidden' : '' ?>">
                                    <span id="sub-page-info" class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                        1–<?= min($subPageSize, count($cascadedChildren)) ?> of <?= count($cascadedChildren) ?>
                                    </span>
                                    <div class="flex items-center gap-1.5">
                                        <button type="button" id="sub-prev-btn" onclick="changeSubPage(-1)" 
                                                class="p-1 px-2 rounded-lg border border-slate-200 dark:border-[#1e382b] bg-white dark:bg-[#0c1510] hover:bg-slate-100 dark:hover:bg-[#13271b] text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white disabled:opacity-30 disabled:pointer-events-none transition-colors cursor-pointer text-xs font-bold flex items-center gap-1"
                                                title="Previous page">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                                            </svg>
                                        </button>
                                        <span id="sub-page-indicator" class="text-[11px] font-extrabold text-emerald-600 dark:text-emerald-400 px-1">
                                            1 / <?= $totalSubPages ?>
                                        </span>
                                        <button type="button" id="sub-next-btn" onclick="changeSubPage(1)" 
                                                class="p-1 px-2 rounded-lg border border-slate-200 dark:border-[#1e382b] bg-white dark:bg-[#0c1510] hover:bg-slate-100 dark:hover:bg-[#13271b] text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white disabled:opacity-30 disabled:pointer-events-none transition-colors cursor-pointer text-xs font-bold flex items-center gap-1"
                                                title="Next page">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </button>
                                    </div>
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
                            <button onclick="triggerCascade('<?= $activeFolder['id'] ?>')" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white dark:bg-[#f59e0b] dark:hover:bg-[#d97706] dark:text-black rounded-xl shadow-md transition-all cursor-pointer flex justify-center items-center gap-1.5 font-black text-xs uppercase tracking-wider active:scale-98">
                                <?= $isOpcrFolder ? 'Cascade OPCR Commitments' : 'Cascade to Selected Team' ?>
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
                <div class="flex flex-col gap-3 border-t border-slate-200 dark:border-[#0d4a32]/60 pt-4 mt-auto shrink-0">
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
                                    class="w-full text-emerald-700 dark:text-[#34d399] hover:text-emerald-800 dark:hover:text-[#6ee7b7] bg-emerald-50 hover:bg-emerald-100 dark:bg-[#13271b] border border-emerald-200 dark:border-[#1e422f] py-2.5 rounded-xl font-bold text-xs flex items-center justify-center transition-all cursor-pointer shadow-2xs">
                                Edit
                            </button>

                            <button onclick='archiveFolder("<?= esc($activeFolder["id"]) ?>", "<?= esc(addslashes($activeFolder["title"])) ?>")'
                                    class="w-full text-rose-600 dark:text-rose-400 hover:text-rose-700 dark:hover:text-rose-300 bg-rose-50 hover:bg-rose-100 dark:bg-[#1c1214] border border-rose-200 dark:border-[#361a1f] py-2.5 rounded-xl font-bold text-xs flex items-center justify-center transition-all cursor-pointer shadow-2xs"
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
                        <?php endif; ?>
                    <?php elseif ($activeFolder['status'] === \App\Enums\FolderStatus::EVALUATED->value): ?>
                        <?php if ($activeFolder['user_id'] == session()->get('user_id')): ?>
                            <button onclick="unsubmitEvaluationFolder('<?= $activeFolder['id'] ?>', this)" 
                                    class="w-full py-2.5 px-3 bg-rose-50 hover:bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:hover:bg-rose-900/60 dark:text-rose-300 border border-rose-300 dark:border-rose-800 rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer shadow-2xs">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                </svg>
                                Revoke Self-Rating
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if (!empty($parentFolder) && !$isOpcrFolder): ?>
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


    <!-- MOV ATTACHMENTS MODAL -->
    <div id="movAttachmentsModal" class="spms-modal-backdrop hidden" onclick="if(event.target === this) closeAttachmentsModal()">
        <div class="spms-modal-dialog" style="max-width: 520px;">
            <!-- Header -->
            <div class="spms-modal-header">
                <div>
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-600 dark:text-[#34d399] block mb-0.5">SUPPORTING EVIDENCE</span>
                    <h3 class="text-lg font-extrabold text-slate-900 dark:text-white m-0">Document MOVs & Attachments</h3>
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
                                    <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-[#032115] text-emerald-600 dark:text-[#34d399] flex items-center justify-center shrink-0">
                                        <svg style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                    </div>
                                    <div style="display: flex; flex-direction: column; min-width: 0;">
                                        <span class="text-xs font-bold text-slate-900 dark:text-white truncate" title="<?= esc($att['file_name']) ?>"><?= esc($att['file_name']) ?></span>
                                        <span class="text-[10px] text-slate-500 dark:text-[#5a8b73]">
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
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-[#062e1e] border border-emerald-200 dark:border-[#0d4a32] text-emerald-600 dark:text-[#5a8b73] flex items-center justify-center mb-3">
                            <svg style="width: 24px; height: 24px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                        </div>
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white m-0 mb-1">No Files Attached Yet</h4>
                        <p class="text-xs text-slate-500 dark:text-[#5a8b73] max-w-[320px] m-0 leading-relaxed">
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
        function openAttachmentsModal() {
            document.getElementById('movAttachmentsModal')?.classList.remove('hidden');
        }
        function closeAttachmentsModal() {
            document.getElementById('movAttachmentsModal')?.classList.add('hidden');
        }
        function triggerPrintPdf(docUrl) {
            window.open(docUrl + '?print=1', '_blank');
        }

        document.addEventListener("DOMContentLoaded", function() {
            switchDocTab('mine');
        });

        // Bottom Sheet Logic
        let isSheetOpen = false;
        function toggleBottomSheet() {
            const sheet = document.getElementById('bottom-sheet');
            const overlay = document.getElementById('bottom-sheet-overlay');
            if (!sheet) return;
            isSheetOpen = !isSheetOpen;
            
            if (isSheetOpen) {
                sheet.classList.remove('spms-bottom-sheet-collapsed');
                sheet.classList.add('spms-bottom-sheet-expanded');
                if (overlay) overlay.classList.remove('hidden');
            } else {
                sheet.classList.add('spms-bottom-sheet-collapsed');
                sheet.classList.remove('spms-bottom-sheet-expanded');
                if (overlay) overlay.classList.add('hidden');
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

        let currentSubPage = 1;
        const subPageSize = 6;

        function renderSubordinatesPage(page) {
            const cards = document.querySelectorAll('.subordinate-card');
            if (!cards || cards.length === 0) return;

            const total = cards.length;
            const totalPages = Math.ceil(total / subPageSize) || 1;

            if (page < 1) page = 1;
            if (page > totalPages) page = totalPages;
            currentSubPage = page;

            const start = (page - 1) * subPageSize;
            const end = start + subPageSize;

            cards.forEach((card, idx) => {
                if (idx >= start && idx < end) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });

            const pageInfo = document.getElementById('sub-page-info');
            if (pageInfo) {
                const from = Math.min(start + 1, total);
                const to = Math.min(end, total);
                pageInfo.textContent = `${from}–${to} of ${total}`;
            }

            const indicator = document.getElementById('sub-page-indicator');
            if (indicator) {
                indicator.textContent = `${page} / ${totalPages}`;
            }

            const prevBtn = document.getElementById('sub-prev-btn');
            const nextBtn = document.getElementById('sub-next-btn');
            if (prevBtn) prevBtn.disabled = (page <= 1);
            if (nextBtn) nextBtn.disabled = (page >= totalPages);

            const pagination = document.getElementById('subordinates-pagination');
            if (pagination) {
                if (totalPages <= 1) {
                    pagination.classList.add('hidden');
                } else {
                    pagination.classList.remove('hidden');
                }
            }
        }

        function changeSubPage(delta) {
            renderSubordinatesPage(currentSubPage + delta);
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => renderSubordinatesPage(1));
        } else {
            renderSubordinatesPage(1);
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
                variant: 'danger'
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

        async function unsubmitEvaluationFolder(folderId, btn) {
            const ok = await window.appConfirm("Are you sure you want to revoke your self-rating submission? This will return your evaluation to drafting status so you can edit your ratings and accomplishments.", {
                title: 'Revoke Self-Rating',
                variant: 'undo',
                confirmText: 'Revoke Self-Rating',
                cancelText: 'Keep Submitted'
            });
            if (!ok) return;

            if (btn) btn.disabled = true;
            const formData = new FormData();
            formData.append('folder_id', folderId);

            apiPost('<?= site_url('folder/unsubmit') ?>', formData, {
                onSuccess: () => window.location.reload(),
                onError: async (errMsg) => {
                    await window.appAlert(errMsg || "An error occurred.");
                    if (btn) btn.disabled = false;
                }
            });
        }
    </script>
<?php endif; ?>