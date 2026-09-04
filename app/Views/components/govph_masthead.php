<!-- Official Philippine Government & BSU Masthead Bar (AO 39 / GWTS / RA 10535 Compliance) -->
<div class="relative z-[120] text-xs py-1 px-3 sm:px-6 lg:px-8 select-none print-hide"
     style="background-color: #0F2B1D; color: #ffffff; border-bottom: 1px solid #163e2a; height: 36px; box-sizing: border-box; overflow: hidden;">
    <div class="max-w-7xl mx-auto flex items-center justify-between gap-3" style="height: 100%;">
        <!-- Left: GOVPH + Divider + Seals + University & System Titles -->
        <div class="flex items-center gap-2.5 sm:gap-3.5 min-w-0" style="height: 100%;">
            <!-- GOVPH Link -->
            <a href="https://www.gov.ph" target="_blank" rel="noopener noreferrer" 
               class="font-extrabold tracking-wider uppercase underline underline-offset-2 shrink-0 transition-colors hover:text-amber-300"
               style="font-size: 11px; color: #ffffff;"
               title="Official Gazette of the Republic of the Philippines">
                GOVPH
            </a>

            <!-- Vertical Divider -->
            <span style="color: #2b5a3f; font-size: 13px; line-height: 1;" class="shrink-0 select-none">|</span>

            <!-- Official Seals: Bagong Pilipinas & Benguet State University (MC 24, s. 2023 Compliance) -->
            <div class="flex items-center gap-1.5 shrink-0">
                <!-- Bagong Pilipinas Official Logo -->
                <a href="https://www.gov.ph" target="_blank" rel="noopener noreferrer" title="Bagong Pilipinas - Republic of the Philippines" class="flex items-center">
                    <img src="<?= base_url('assets/images/bagong_pilipinas.png') ?>" alt="Bagong Pilipinas Logo" 
                         style="width: 22px; height: 22px; min-width: 22px; min-height: 22px; max-width: 22px; max-height: 22px; object-fit: contain; display: block;" />
                </a>
                <!-- BSU Official University Seal -->
                <a href="http://www.bsu.edu.ph" target="_blank" rel="noopener noreferrer" title="Benguet State University" class="flex items-center">
                    <img src="<?= base_url('assets/images/bsu_seal.png') ?>" alt="Benguet State University Seal" 
                         style="width: 22px; height: 22px; min-width: 22px; min-height: 22px; max-width: 22px; max-height: 22px; border-radius: 9999px; object-fit: contain; display: block;" />
                </a>
            </div>

            <!-- Title & Subline (Matching Official BSU Portal Standard) -->
            <div class="flex flex-col min-w-0 justify-center" style="line-height: 1.15;">
                <span class="font-extrabold tracking-tight uppercase truncate" style="font-size: 11px; color: #ffffff;">
                    BENGUET STATE UNIVERSITY
                </span>
                <span class="font-bold tracking-wider uppercase truncate" style="font-size: 9px; color: #D69C08;">
                    <?= esc($mastheadSubline ?? 'STRATEGIC PERFORMANCE MANAGEMENT SYSTEM') ?>
                </span>
            </div>
        </div>

        <!-- Right Side: Philippine Standard Time (PST) Clock (RA 10535 Standard) -->
        <div class="hidden md:flex items-center gap-2 font-medium shrink-0" style="font-size: 11px; color: rgba(167, 243, 208, 0.8);">
            <span style="color: rgba(52, 211, 153, 0.9); font-weight: 700;">Philippine Standard Time:</span>
            <span id="govph-pst-clock" class="font-mono" style="font-size: 11px; color: #ffffff;"></span>
        </div>
    </div>
</div>

<script>
    (function() {
        function updatePstClock() {
            const clockEl = document.getElementById('govph-pst-clock');
            if (!clockEl) return;
            try {
                const now = new Date();
                const options = {
                    timeZone: 'Asia/Manila',
                    weekday: 'short',
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: 'numeric',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: true
                };
                clockEl.textContent = new Intl.DateTimeFormat('en-US', options).format(now);
            } catch (e) {
                clockEl.textContent = new Date().toLocaleTimeString();
            }
        }
        updatePstClock();
        setInterval(updatePstClock, 1000);
    })();
</script>
