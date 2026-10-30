                <!-- REMARKS BOX (ROW 29 IN EXCEL SPREADSHEET) -->
                <div style="border: 1px solid #000; padding: 8px 10px; box-sizing: border-box;">
                    <div style="font-weight: bold; font-size: 11px; color: #000000; margin-bottom: 4px;">
                        Remarks/Suggestions/Recommendations on Ratee's Performance:
                    </div>
                    <textarea id="pmt-remarks" rows="2" placeholder="Enter remarks/suggestions/recommendations on ratee's performance..." class="spms-textarea" style="width: 100%; border: 1px solid transparent; font-style: italic; font-size: 11px;" <?= (!$isOwner && !$isTwg && ($canEditEvaluation || $isAdmin)) ? '' : 'disabled title="Official recommendations are entered by the Immediate Supervisor / Head of Office evaluator."' ?>></textarea>
                </div>
