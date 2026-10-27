                <!-- INSTITUTIONAL FORM HEADER -->
                <div style="text-align: center; margin-bottom: 20px;">
                    <h2 style="font-size: 14.5px; font-weight: 900; margin: 0; text-transform: uppercase; letter-spacing: 0.05em; color: #0f172a; line-height: 1.4;" id="spms-doc-title">
                        <?= $isDocIperf 
                            ? 'INDIVIDUAL PERFORMANCE EVALUATION RATING FORM FOR CONTRACT OF SERVICE AND JOB ORDER PERSONNEL' 
                            : ($isDocDpcr ? 'DEPARTMENT PERFORMANCE COMMITMENT AND REVIEW (DPCR)' : ($isDocOpcr ? 'OFFICE PERFORMANCE COMMITMENT AND REVIEW (OPCR)' : 'INDIVIDUAL PERFORMANCE COMMITMENT AND REVIEW (IPCR)')) ?>
                    </h2>
                    <?php if ($isDocIperf): ?>
                    <div style="font-size: 11px; font-style: italic; color: #64748b; margin-top: 4px;">
                        (attach rubrics for the rating of actual accomplishments vis-à-vis expected outputs)
                    </div>
                    <?php endif; ?>
                </div>

