<?php
/**
 * Default function weights per doc type (System Data > Doc Type Rules). Prefilled when a new form of that
 * type is created; each row must total 100% (CSC MC No. 6, s. 2012). IPERF has no split.
 */
$cscDefaults = ['OPCR' => [70, 20, 10], 'DPCR' => [70, 15, 15], 'IPCR' => [70, 15, 15]];
$inputStyle = 'padding-top: 0.375rem; padding-bottom: 0.375rem;';
$inputClass = 'w-16 bg-zinc-50 dark:bg-zinc-800 border border-surface-border rounded-lg px-2 text-sm font-bold text-text text-center focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none transition-all';
$th = 'px-3 py-2.5 text-[10px] font-black uppercase tracking-widest text-text-muted';
?>
<form id="form-default-weights" onsubmit="saveDefaultWeights(event)" class="rounded-xl border border-surface-border overflow-hidden shrink-0">
    <div class="px-4 py-3 bg-zinc-50 dark:bg-zinc-800/30 border-b border-surface-border flex items-center justify-between gap-3">
        <span class="text-[10px] font-black uppercase tracking-widest text-text-muted">Default weights per doc type</span>
        <div class="flex items-center gap-4">
            <button type="button" onclick="resetToCscDefaults()" class="text-[10px] font-black uppercase tracking-widest text-text-muted hover:text-text cursor-pointer">Reset to CSC</button>
            <button type="submit" id="btn-save-weights" class="bg-accent hover:bg-accent-hover text-accent-text text-xs font-bold py-2 px-4 rounded-lg transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">Save Weights</button>
        </div>
    </div>
    <div class="overflow-x-auto custom-scrollbar">
        <table class="w-full text-left border-collapse">
            <thead class="border-b border-surface-border">
                <tr><th class="<?= $th ?>" style="padding-left: 1rem;">Doc type</th><th class="<?= $th ?>">Core %</th><th class="<?= $th ?>">Strategic %</th><th class="<?= $th ?>">Support %</th><th class="<?= $th ?>" style="padding-right: 1rem; text-align: right;">Total</th></tr>
            </thead>
            <tbody class="divide-y divide-surface-border text-sm">
                <?php foreach ($cscDefaults as $type => [$dCore, $dStrat, $dSupp]):
                    $w = $defaultWeights[$type] ?? [];
                    $vals = ['core' => (float) ($w['core_weight'] ?? $dCore), 'strategic' => (float) ($w['strategic_weight'] ?? $dStrat), 'support' => (float) ($w['support_weight'] ?? $dSupp)];
                    $total = round(array_sum($vals), 2);
                    $lower = strtolower($type);
                ?>
                <tr>
                    <td class="px-3 py-2 font-bold text-text" style="padding-left: 1rem;"><?= $type ?></td>
                    <?php foreach (['core' => 'Core', 'strategic' => 'Strategic', 'support' => 'Support'] as $key => $label): ?>
                        <td class="px-3 py-2">
                            <input type="number" step="1" min="0" max="100" required aria-label="<?= $type ?> <?= $label ?> %" name="weights[<?= $type ?>][<?= $key ?>_weight]" id="input-<?= $lower ?>-<?= $key ?>"
                                   value="<?= $vals[$key] + 0 ?>" data-type="<?= $type ?>" data-category="<?= $key ?>" oninput="calculateCardTotal('<?= $type ?>')" class="<?= $inputClass ?>" style="<?= $inputStyle ?>">
                        </td>
                    <?php endforeach; ?>
                    <td class="px-3 py-2" style="padding-right: 1rem; text-align: right;"><span id="badge-total-<?= $type ?>" class="text-xs font-black <?= $total == 100 ? 'text-success-600' : 'text-danger-500' ?>"><span id="text-total-<?= $type ?>"><?= $total + 0 ?></span>%</span></td>
                </tr>
                <?php endforeach; ?>
                <tr>
                    <td class="px-3 py-2 font-bold text-text" style="padding-left: 1rem;">IPERF</td>
                    <td colspan="4" class="px-3 py-2 text-xs font-medium text-text-muted" style="padding-right: 1rem;">No split: rated on its expected outputs as a whole.</td>
                </tr>
            </tbody>
        </table>
    </div>
    <p id="weights-status-message" class="weights-msg px-4 py-2.5 border-t border-surface-border text-[11px] font-bold"></p>
</form>
