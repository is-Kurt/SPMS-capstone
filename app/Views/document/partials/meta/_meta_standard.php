                <div style="margin-bottom: 18px; font-size: 11px; line-height: 1.8; text-align: justify; color: #0f172a;">
                    I, <input type="text" id="ratee-name" value="<?= esc($ownerInfo['name'] ?? '') ?>" placeholder="FULL NAME HERE" class="spms-meta-input" style="font-weight: bold; text-align: center; min-width: 170px; padding: 2px 6px;" <?= $canEditTargets ? '' : 'disabled' ?>>, 
                    <input type="text" id="ratee-position" value="<?= esc($ownerInfo['position'] ?? '') ?>" placeholder="Position and Official Designation" class="spms-meta-input" style="text-align: center; min-width: 200px; padding: 2px 6px;" <?= $canEditTargets ? '' : 'disabled' ?>> of the 
                    <input type="text" id="ratee-dept" value="<?= esc($ownerInfo['dept'] ?? '') ?>" placeholder="Office Name" class="spms-meta-input" style="text-align: center; min-width: 170px; padding: 2px 6px;" <?= $canEditTargets ? '' : 'disabled' ?>>, commit to deliver and agree to be rated on the attainment of the following targets in accordance with the indicated measure for the period 
                    <input type="text" id="ratee-period" value="<?= esc($ownerInfo['period'] ?? '') ?>" placeholder="January - June or July - December and Year; e.g., July - December 2024" class="spms-meta-input" style="font-weight: bold; text-align: center; min-width: 320px; padding: 2px 6px;" <?= $canEditTargets ? '' : 'disabled' ?>>.
                </div>

                <!-- Approver, Ratee, and Rating Scale Matrix -->
                <table class="spms-meta-matrix" style="width: 100%; border-collapse: collapse; border: none; margin-bottom: 18px; font-size: 11px;">
                    <tr>
                        <!-- Left: Approved By (Extreme Left-most) -->
                        <td style="vertical-align: top; border: none; padding: 0 20px 0 0; text-align: left;">
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                                <div style="font-weight: bold; color: #0f172a;">APPROVED BY:</div>
                                <button type="button" id="btn-add-approver" onclick="addApproverBlock()" class="print-hide" style="display: <?= $canEditApprover ? 'inline-flex' : 'none' ?>; align-items: center; gap: 4px; padding: 2px 8px; font-size: 10px; font-weight: 700; color: #047857; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 4px; cursor: pointer;" title="Add another approving signatory">
                                    + Add Signatory
                                </button>
                            </div>
                            <div id="approvers-container" style="display: flex; flex-direction: column; gap: 8px;">
                                <div class="approver-item" style="position: relative;">
                                    <table style="width: 100%; border-collapse: collapse; border: none; font-size: 11px;">
                                        <tr>
                                            <td style="width: 65px; border: none; padding: 3px 0; font-weight: bold; color: #0f172a;">Name:</td>
                                            <td style="border: none; padding: 3px 0;">
                                                <input type="text" class="field-approver-name spms-meta-input" id="approver-name" value="<?= esc($supervisors[0]['name'] ?? '') ?>" placeholder="<?= $isDocOpcr ? '(name of head of office / approving authority)' : ($isDocDpcr ? '(name of office head)' : 'Name of Approving Authority') ?>" style="font-weight: bold; width: 60%; padding: 2px 4px;" <?= $canEditApprover ? '' : 'disabled' ?>>
                                                <span style="color: #64748b; font-style: italic; font-size: 10px; margin-left: 6px;">(may add signatories depending on position)</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="border: none; padding: 3px 0; font-weight: bold; color: #0f172a;">Position:</td>
                                            <td style="border: none; padding: 3px 0;">
                                                <input type="text" class="field-approver-pos spms-meta-input" id="approver-pos" value="<?= esc($supervisors[0]['position'] ?? '') ?>" placeholder="<?= $isDocOpcr ? '(position / designation)' : ($isDocDpcr ? '(position of office head)' : 'Official Designation') ?>" style="width: 80%; padding: 2px 4px;" <?= $canEditApprover ? '' : 'disabled' ?>>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="border: none; padding: 3px 0; font-weight: bold; color: #0f172a;">Date:</td>
                                            <td style="border: none; padding: 3px 0;">
                                                <input type="date" class="field-approver-date spms-meta-input dark:[color-scheme:dark]" id="approver-date" value="<?= esc($supervisors[0]['date'] ?? $cascadeDate ?? '') ?>" onclick="this.showPicker && this.showPicker()" style="width: 130px; padding: 2px 4px; cursor: pointer;" <?= $canEditApprover ? '' : 'disabled' ?>>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </td>

                        <!-- Right: Name of Employee & Rating Scale (Extreme Right-most) -->
                        <td style="vertical-align: top; border: none; padding: 0; text-align: right; width: 1%; white-space: nowrap;">
                            <div style="display: inline-block; text-align: left; min-width: 240px;">
                                <!-- Name of Employee Block -->
                                <div style="margin-bottom: 14px;">
                                    <div>
                                        <input type="text" id="ratee-sign-name" value="<?= esc($ownerInfo['name'] ?? '') ?>" placeholder="(full name here)" class="spms-meta-input" style="font-weight: bold; width: 240px; padding: 2px 0;" <?= $canEditTargets ? '' : 'disabled' ?>>
                                    </div>
                                    <div style="font-size: 11px; color: #334155; margin-top: 3px; font-weight: 600;">Name of Employee</div>
                                    <div style="margin-top: 4px; font-size: 11px; color: #334155;">
                                        Date: <input type="date" id="ratee-sign-date" value="<?= esc($cascadeDate ?? '') ?>" onclick="this.showPicker && this.showPicker()" class="spms-meta-input dark:[color-scheme:dark]" style="width: 130px; cursor: pointer; padding: 1px 2px;" <?= $canEditTargets ? '' : 'disabled' ?>>
                                    </div>
                                </div>

                                <!-- Rating Scale -->
                                <div style="font-size: 10px; color: #334155;">
                                    <div style="font-weight: bold; margin-bottom: 3px; color: #0f172a;">Rating Scale:</div>
                                    <div style="display: flex; flex-direction: column; gap: 2px; line-height: 1.35; color: #475569;">
                                        <div><strong style="color: #0f172a;">5</strong> – Outstanding</div>
                                        <div><strong style="color: #0f172a;">4</strong> – Very Satisfactory</div>
                                        <div><strong style="color: #0f172a;">3</strong> – Satisfactory</div>
                                        <div><strong style="color: #0f172a;">2</strong> – Unsatisfactory</div>
                                        <div><strong style="color: #0f172a;">1</strong> – Poor</div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>

