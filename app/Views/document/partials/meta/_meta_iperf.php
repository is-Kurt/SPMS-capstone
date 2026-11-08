                <!-- IPERF Metadata Matrix -->
                <table class="spms-meta-matrix" style="width: 100%; border-collapse: collapse; border: 1px solid #000000; margin-bottom: 18px; font-size: 11px;">
                    <tr style="border-bottom: 1px solid #000000;">
                        <td style="width: 18%; padding: 6px 8px; font-weight: bold; border-right: 1px solid #000000; background: #fafafa; color: #0f172a;">Name of Employee:</td>
                        <td style="width: 42%; padding: 6px 8px; border-right: 1px solid #000000;">
                            <input type="text" id="ratee-name" value="" placeholder="indicate full name (First Name Middle Initial Last Name, Extension; e.g., Juan D. Cruz III)" class="spms-meta-input" style="width: 100%; font-weight: bold; border: none; padding: 2px 0;" <?= $canEditTargets ? '' : 'disabled' ?>>
                        </td>
                        <td style="width: 15%; padding: 6px 8px; font-weight: bold; border-right: 1px solid #000000; background: #fafafa; color: #0f172a;">Classification:</td>
                        <td style="width: 25%; padding: 6px 8px;">
                            <!-- Follows the owner's employment status (User Directory), 2026-11-08 -->
                            <input type="text" id="ratee-classification" value="<?= esc(iperf_classification($ownerInfo['employment_status'] ?? null), 'attr') ?>" class="spms-meta-input" style="width: 100%; font-weight: bold; border: none; padding: 2px 0;" disabled title="Follows the employee's employment status">
                        </td>
                    </tr>
                    <tr style="border-bottom: 1px solid #000000;">
                        <td style="padding: 6px 8px; font-weight: bold; border-right: 1px solid #000000; background: #fafafa; color: #0f172a;">Position:</td>
                        <td style="padding: 6px 8px; border-right: 1px solid #000000;">
                            <input type="text" id="ratee-position" value="" placeholder="indicate the full position title specified in the contract/job order" class="spms-meta-input" style="width: 100%; border: none; padding: 2px 0;" <?= $canEditTargets ? '' : 'disabled' ?>>
                        </td>
                        <td style="padding: 6px 8px; font-weight: bold; border-right: 1px solid #000000; background: #fafafa; color: #0f172a;">Rating Period:</td>
                        <td style="padding: 6px 8px;">
                            <input type="text" id="ratee-period" value="" placeholder="e.g., July - December 2024" class="spms-meta-input" style="width: 100%; font-weight: bold; border: none; padding: 2px 0;" <?= $canEditTargets ? '' : 'disabled' ?>>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 8px; font-weight: bold; border-right: 1px solid #000000; background: #fafafa; color: #0f172a;">Office:</td>
                        <td colspan="3" style="padding: 6px 8px;">
                            <input type="text" id="ratee-dept" value="" placeholder="indicate in full the specific area of assignment (e.g., CIS - Department of Development Communication)" class="spms-meta-input" style="width: 100%; border: none; padding: 2px 0;" <?= $canEditTargets ? '' : 'disabled' ?>>
                        </td>
                    </tr>
                </table>
                <!-- Hidden compatibility elements for IPERF -->
                <input type="hidden" id="ratee-sign-name" value="">
                <input type="hidden" id="ratee-sign-date" value="">
                <div id="approvers-container" style="display: none;">
                    <div class="approver-item">
                        <input type="hidden" class="field-approver-name" id="approver-name" value="">
                        <input type="hidden" class="field-approver-pos" id="approver-pos" value="">
                        <input type="hidden" class="field-approver-date" id="approver-date" value="">
                    </div>
                </div>
