                <!-- GRAND SUMMARY & NAVY RATING BAR (BULLETPROOF TABLE LAYOUT FOR BOTH DPCR AND IPCR) -->
                <table class="spms-summary-matrix" style="width: 100%; border-collapse: collapse; border: 1px solid #000; font-size: 11px;">
                    <tr>
                        <!-- Left: Formula Explanation -->
                        <td style="width: 35%; padding: 12px; border: 1px solid #000; vertical-align: top; background: #fafafa;">
                            <div style="font-size: 10px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">Formula Weights:</div>
                            <div id="formula-desc-text" style="font-size: 11px; color: #334155; margin-top: 6px; line-height: 1.5;">
                                Core Functions ( ____ %) + Strategic Functions ( ____ %) + Support Functions ( ____ %).
                            </div>
                            <div style="font-size: 10px; color: #94a3b8; font-style: italic; margin-top: 14px; border-top: 1px solid #e2e8f0; padding-top: 6px;">
                                Validated against standard Civil Service Commission SPMS Guidelines.
                            </div>
                        </td>

                        <!-- Right: Calculation Breakdown & Final Navy Bar -->
                        <td style="width: 65%; padding: 0; vertical-align: top; border: 1px solid #000;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <!-- Core Row -->
                                <tr style="border-bottom: 1px solid #000;">
                                    <td style="padding: 8px 12px; font-weight: 800; font-size: 11px; text-transform: uppercase; width: 45%; color: #0f172a;">
                                        CORE FUNCTION
                                    </td>
                                    <td style="padding: 8px 12px; font-weight: 900; color: #047857; font-size: 13px; width: 20%;" id="sum-core-score">
                                        0.000
                                    </td>
                                    <td style="padding: 8px 12px; font-size: 11px; color: #64748b; text-align: right; width: 35%;">
                                        (Average: <span id="sum-core-avg">0.000</span> × <span id="mult-core-val"><?= ($isDocDpcr || $isDocOpcr) ? '0.60' : '0.70' ?></span>)
                                    </td>
                                </tr>

                                <!-- Strategic Row -->
                                <tr style="border-bottom: 1px solid #000;">
                                    <td style="padding: 8px 12px; font-weight: 800; font-size: 11px; text-transform: uppercase; color: #0f172a;">
                                        STRATEGIC FUNCTION
                                    </td>
                                    <td style="padding: 8px 12px; font-weight: 900; color: #0284c7; font-size: 13px;" id="sum-strategic-score">
                                        0.000
                                    </td>
                                    <td style="padding: 8px 12px; font-size: 11px; color: #64748b; text-align: right;">
                                        (Average: <span id="sum-strategic-avg">0.000</span> × <span id="mult-strategic-val"><?= $isDocDpcr ? '0.30' : ($isDocOpcr ? '0.25' : '0.20') ?></span>)
                                    </td>
                                </tr>

                                <!-- Support Row -->
                                <tr style="border-bottom: 1px solid #000;">
                                    <td style="padding: 8px 12px; font-weight: 800; font-size: 11px; text-transform: uppercase; color: #0f172a;">
                                        SUPPORT FUNCTIONS
                                    </td>
                                    <td style="padding: 8px 12px; font-weight: 900; color: #d97706; font-size: 13px;" id="sum-support-score">
                                        0.000
                                    </td>
                                    <td style="padding: 8px 12px; font-size: 11px; color: #64748b; text-align: right;">
                                        (Average: <span id="sum-support-avg">0.000</span> × <span id="mult-support-val"><?= $isDocOpcr ? '0.15' : '0.10' ?></span>)
                                    </td>
                                </tr>

                                <!-- Institutional Grand Total Banner -->
                                <tr style="background: #062316; color: #ffffff;">
                                    <td style="padding: 12px 14px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; font-size: 11px; color: #e2e8f0;">
                                        FINAL AVERAGE RATING
                                    </td>
                                    <td style="padding: 12px 14px; font-weight: 900; font-size: 24px; color: #fbbf24; font-family: monospace;" id="grand-score">
                                        0.000
                                    </td>
                                    <td style="padding: 12px 14px; text-align: right;">
                                        <span id="adjectival-badge" style="display: inline-block; background: #475569; color: #ffffff; font-weight: 900; font-size: 11px; text-transform: uppercase; padding: 4px 12px; border-radius: 4px; letter-spacing: 0.05em; margin-right: 8px;">
                                            PENDING EVALUATION
                                        </span>
                                        <span id="grand-formula" style="font-size: 9px; color: #94a3b8; font-family: monospace;">
                                            (Core 0.000 + Strategic 0.000 + Support 0.000)
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
