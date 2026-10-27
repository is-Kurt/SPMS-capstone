                <!-- Signatories -->
                <table style="width: 100%; border-collapse: collapse; border: none; font-size: 11px; margin-top: 18px;">
                    <tr>
                        <!-- Left: Ratee (Columns A-C) -->
                        <td style="width: 50%; vertical-align: top; border: none; padding: 0 15px 0 0;">
                            <div style="margin-bottom: 6px; color: #0f172a;">Name and Signature of Ratee: 
                                <input type="text" id="sig-ratee-name" value="<?= esc($ownerInfo['name'] ?? '') ?>" placeholder="(name here)" class="spms-meta-input" style="font-weight: bold; width: 55%; padding: 2px 4px;">
                            </div>
                            <div style="margin-bottom: 6px; color: #0f172a;">Position: 
                                <input type="text" id="sig-ratee-pos" value="<?= esc($ownerInfo['position'] ?? '') ?>" placeholder="(position here)" class="spms-meta-input" style="width: 70%; padding: 2px 4px;">
                            </div>
                            <div style="margin-bottom: 6px; color: #0f172a;">
                                Date: <input type="date" id="sig-ratee-date" value="<?= esc($cascadeDate ?? '') ?>" onclick="this.showPicker && this.showPicker()" class="spms-meta-input dark:[color-scheme:dark]" style="width: 130px; cursor: pointer; padding: 1px 4px;">
                            </div>
                        </td>

                        <!-- Right: Office Head (Columns D-H) -->
                        <td style="width: 50%; vertical-align: top; border: none; padding: 0 0 0 15px;">
                            <div id="footer-approvers-container" style="display: flex; flex-direction: column; gap: 8px;">
                                <div class="footer-approver-item" style="position: relative;">
                                    <div style="margin-bottom: 6px; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                                        <span style="font-weight: bold;">Final Rating by:</span>
                                        <button type="button" id="btn-add-footer-approver" onclick="addApproverBlock()" class="print-hide" style="display: <?= $canEditApprover ? 'inline-flex' : 'none' ?>; align-items: center; gap: 4px; padding: 2px 8px; font-size: 10px; font-weight: 700; color: #047857; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 4px; cursor: pointer;" title="Add another approving signatory">
                                            + Add Signatory
                                        </button>
                                    </div>
                                    <div style="margin-bottom: 6px; color: #0f172a;">
                                        <input type="text" class="field-footer-approver-name spms-meta-input" id="sig-dean-name" value="<?= esc($supervisors[0]['name'] ?? '') ?>" placeholder="(name of office head)" style="font-weight: bold; width: 65%; padding: 2px 4px;">
                                    </div>
                                    <div style="margin-bottom: 6px; color: #0f172a;">Position: 
                                        <input type="text" class="field-footer-approver-pos spms-meta-input" id="sig-dean-pos" value="<?= esc($supervisors[0]['position'] ?? '') ?>" placeholder="(position of office head)" style="width: 70%; padding: 2px 4px;">
                                    </div>
                                    <div style="margin-bottom: 6px; color: #0f172a;">
                                        Date: <input type="date" class="field-footer-approver-date spms-meta-input dark:[color-scheme:dark]" id="sig-dean-date" value="<?= esc($supervisors[0]['evalDate'] ?? $cascadeDate ?? '') ?>" onclick="this.showPicker && this.showPicker()" style="width: 130px; cursor: pointer; padding: 1px 4px;">
                                    </div>
                                    <div style="color: #64748b; font-size: 10px; font-style: italic;">(may add signatories depending on position/designation)</div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
                <input type="hidden" id="sig-vp-name" value="">
                <input type="hidden" id="sig-vp-date" value="">
