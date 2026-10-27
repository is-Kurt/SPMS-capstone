                <!-- IPERF Signatories & Reference Guide -->
                <table class="spms-signatories-matrix" style="width: 100%; border-collapse: collapse; border: 1px solid #000; margin-top: 18px; font-size: 11px;">
                    <tr>
                        <!-- Phase 1: Start of Period (Columns A-B) -->
                        <td style="width: 36%; vertical-align: top; border-right: 1px solid #000; padding: 10px 12px;">
                            <div style="color: #64748b; font-style: italic; font-size: 10.5px; font-weight: bold; margin-bottom: 10px;">
                                signed at the start of the rating period
                            </div>

                            <!-- Targets Prepared By -->
                            <div style="margin-bottom: 16px;">
                                <div style="font-weight: bold; color: #0f172a; margin-bottom: 4px;">Targets prepared by:</div>
                                <input type="text" id="sig-targets-prepared-name" value="" placeholder="(Signature over Printed Name)" class="spms-meta-input" style="width: 90%; font-weight: bold; padding: 2px 4px;" <?= $canEditTargets ? '' : 'disabled' ?>>
                                <div style="font-size: 10px; color: #64748b; margin-top: 2px;">Employee (Ratee)</div>
                                <div style="margin-top: 4px; font-size: 10.5px; color: #334155;">
                                    Date: <input type="date" id="sig-targets-prepared-date" onclick="this.showPicker && this.showPicker()" class="spms-meta-input dark:[color-scheme:dark]" style="width: 130px; cursor: pointer; padding: 1px 4px;" <?= $canEditTargets ? '' : 'disabled' ?>>
                                </div>
                            </div>

                            <!-- Approved By -->
                            <div>
                                <div style="font-weight: bold; color: #0f172a; margin-bottom: 4px;">Approved by:</div>
                                <input type="text" id="sig-targets-approved-name" value="" placeholder="(Signature over Printed Name)" class="spms-meta-input" style="width: 90%; font-weight: bold; padding: 2px 4px;" <?= $canEditTargets ? '' : 'disabled' ?>>
                                <div style="font-size: 10px; color: #64748b; margin-top: 2px;">Immediate Supervisor (Rater)</div>
                                <div style="margin-top: 4px; font-size: 10.5px; color: #334155;">
                                    Date: <input type="date" id="sig-targets-approved-date" onclick="this.showPicker && this.showPicker()" class="spms-meta-input dark:[color-scheme:dark]" style="width: 130px; cursor: pointer; padding: 1px 4px;" <?= $canEditTargets ? '' : 'disabled' ?>>
                                </div>
                            </div>
                        </td>

                        <!-- Phase 2: End of Period (Columns C-D) -->
                        <td style="width: 36%; vertical-align: top; border-right: 1px solid #000; padding: 10px 12px;">
                            <div style="color: #64748b; font-style: italic; font-size: 10.5px; font-weight: bold; margin-bottom: 10px;">
                                signed at the end of the rating period
                            </div>

                            <!-- Rated By -->
                            <div style="margin-bottom: 16px;">
                                <div style="font-weight: bold; color: #0f172a; margin-bottom: 4px;">Rated by:</div>
                                <input type="text" id="sig-eval-rated-name" value="" placeholder="(Signature over Printed Name)" class="spms-meta-input" style="width: 90%; font-weight: bold; padding: 2px 4px;" <?= $canEditEvaluation ? '' : 'disabled' ?>>
                                <div style="font-size: 10px; color: #64748b; margin-top: 2px;">Immediate Supervisor (Rater)</div>
                                <div style="margin-top: 4px; font-size: 10.5px; color: #334155;">
                                    Date: <input type="date" id="sig-eval-rated-date" onclick="this.showPicker && this.showPicker()" class="spms-meta-input dark:[color-scheme:dark]" style="width: 130px; cursor: pointer; padding: 1px 4px;" <?= $canEditEvaluation ? '' : 'disabled' ?>>
                                </div>
                            </div>

                            <!-- Conforme -->
                            <div>
                                <div style="font-weight: bold; color: #0f172a; margin-bottom: 4px;">Conforme:</div>
                                <input type="text" id="sig-eval-conforme-name" value="" placeholder="(Signature over Printed Name)" class="spms-meta-input" style="width: 90%; font-weight: bold; padding: 2px 4px;" <?= $canEditEvaluation ? '' : 'disabled' ?>>
                                <div style="font-size: 10px; color: #64748b; margin-top: 2px;">Employee (Ratee)</div>
                                <div style="margin-top: 4px; font-size: 10.5px; color: #334155;">
                                    Date: <input type="date" id="sig-eval-conforme-date" onclick="this.showPicker && this.showPicker()" class="spms-meta-input dark:[color-scheme:dark]" style="width: 130px; cursor: pointer; padding: 1px 4px;" <?= $canEditEvaluation ? '' : 'disabled' ?>>
                                </div>
                            </div>
                        </td>

                        <!-- Side Reference: Measures & Rating Guide (Columns E-H) -->
                        <td style="width: 28%; vertical-align: top; padding: 10px 12px; background: #fdfdfd;">
                            <!-- Measures -->
                            <div style="margin-bottom: 12px;">
                                <div style="font-weight: bold; margin-bottom: 4px; border-bottom: 1px solid #000; padding-bottom: 2px; color: #0f172a;">Measures:</div>
                                <table style="width: 100%; font-size: 10.5px; border-collapse: collapse;">
                                    <tr><td style="width: 20px; font-weight: bold; padding: 1px 0; color: #0f172a;">Q</td><td style="padding: 1px 0; color: #334155;">Quality</td></tr>
                                    <tr><td style="font-weight: bold; padding: 1px 0; color: #0f172a;">T</td><td style="padding: 1px 0; color: #334155;">Timeliness</td></tr>
                                    <tr><td style="font-weight: bold; padding: 1px 0; color: #0f172a;">E</td><td style="padding: 1px 0; color: #334155;">Efficiency</td></tr>
                                </table>
                            </div>

                            <!-- Rating Guide -->
                            <div>
                                <div style="font-weight: bold; margin-bottom: 4px; border-bottom: 1px solid #000; padding-bottom: 2px; color: #0f172a;">Rating Guide:</div>
                                <table style="width: 100%; font-size: 10.5px; border-collapse: collapse;">
                                    <tr><td style="width: 20px; font-weight: bold; padding: 1px 0; color: #0f172a;">5</td><td style="padding: 1px 0; color: #334155;">Outstanding</td></tr>
                                    <tr><td style="font-weight: bold; padding: 1px 0; color: #0f172a;">4</td><td style="padding: 1px 0; color: #334155;">Very Satisfactory</td></tr>
                                    <tr><td style="font-weight: bold; padding: 1px 0; color: #0f172a;">3</td><td style="padding: 1px 0; color: #334155;">Satisfactory</td></tr>
                                    <tr><td style="font-weight: bold; padding: 1px 0; color: #0f172a;">2</td><td style="padding: 1px 0; color: #334155;">Unsatisfactory</td></tr>
                                    <tr><td style="font-weight: bold; padding: 1px 0; color: #0f172a;">1</td><td style="padding: 1px 0; color: #334155;">Poor</td></tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                </table>
                <!-- Compatibility hidden inputs for standard signatures -->
                <input type="hidden" id="sig-ratee-name" value="">
                <input type="hidden" id="sig-ratee-pos" value="">
                <input type="hidden" id="sig-ratee-date" value="">
                <input type="hidden" id="sig-dean-name" value="">
                <input type="hidden" id="sig-dean-pos" value="">
                <input type="hidden" id="sig-dean-date" value="">
                <input type="hidden" id="sig-vp-name" value="">
                <input type="hidden" id="sig-vp-date" value="">
