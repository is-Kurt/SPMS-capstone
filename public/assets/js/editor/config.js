function initEditor() {
    tinymce.remove('#editable-doc');
    const isDark = document.documentElement.classList.contains('dark');

    tinymce.init({
        selector: '#editable-doc',
        license_key: 'gpl',
        promotion: false,
        branding: false,
        menubar: false,
        statusbar: false,
        height: '100%',
        highlight_on_focus: false,
        
        font_family_formats: 'Roboto=Roboto, Helvetica, Arial, sans-serif; Sans Serif=sans-serif; Andale Mono=andale mono,times; Arial=arial,helvetica,sans-serif; Arial Black=arial black,avant garde; Book Antiqua=book antiqua,palatino; Comic Sans MS=comic sans ms,sans-serif; Courier New=courier new,courier; Georgia=georgia,palatino; Helvetica=helvetica; Impact=impact,chicago; Symbol=symbol; Tahoma=tahoma,arial,helvetica,sans-serif; Terminal=terminal,monaco; Times New Roman=times new roman,times; Trebuchet MS=trebuchet ms,geneva; Verdana=verdana,geneva;',
        line_height_formats: '1 1.15 1.5 2 2.5 3',

        plugins: 'table lists advlist saveShortcut setDirty cellSelect disableBackgroundCloning',
        toolbar_mode: 'wrap',
        toolbar: [
            'undo redo | fontfamily fontsize blocks | bold italic underline strikethrough | forecolor backcolor tablecellbackgroundcolor',
            'alignleft aligncenter alignright alignjustify | lineheight | bullist numlist outdent indent',
            'table tableinsertrowbefore tableinsertrowafter tabledeleterow | tablemergecells tablesplitcells | clearMarks toggleRating toggleRowAvg toggleTotal toggleFinalRating | toggleId toggleCellWeight | toggleScoreRange toggleWeight'
        ],
        
        skin: isDark ? 'oxide-dark' : 'oxide',
        content_css: [(isDark ? 'dark' : 'default'), AppConfig.editorCss],
        
        setup: function(editor) {
            if (typeof AppConfig.ciDebug !== 'undefined' && AppConfig.ciDebug) {
                cellInspector(editor);
                console.log("TinyMCE: Cell Inspector Loaded (Debug Mode)");
            }
            
            const tableTools = new TableTools(editor);
            const DEFAULT_SCORE_RANGE = 5;
            const DEFAULT_WEIGHT = 100;

            editor.ui.registry.addButton('toggleRating', {
                text: '🎯 Rating',
                tooltip: 'Select cells for input',
                onAction: () => tableTools.modifyCell('calc-rating', 'rgba(16, 185, 129, 0.25)') 
            });

            editor.ui.registry.addButton('toggleRowAvg', {
                text: '🟦 Row Avg',
                tooltip: 'Displays the average of the Q, E, T ratings in this row',
                onAction: () => tableTools.modifyCell('calc-row-avg', 'rgba(14, 165, 233, 0.25)') 
            });

            editor.ui.registry.addButton('toggleTotal', {
                text: '🧮 Mark as Total',
                tooltip: 'Display the weighted total for this table',
                onAction: () => tableTools.modifyCell('calc-total', 'rgba(245, 158, 11, 0.25)') 
            });

            editor.ui.registry.addButton('toggleFinalRating', {
                text: '🧮 Mark as Final Rating',
                tooltip: 'Display the final rating for this table',
                onAction: () => tableTools.modifyCell('calc-final-total', 'rgba(139, 92, 246, 0.25)') 
            });
            editor.ui.registry.addButton('clearMarks', {
                text: '🧹 Clear Marks',
                tooltip: 'Removes all markers from the selected area',
                onAction: () => tableTools.modifyCell()
            });

            editor.ui.registry.addButton('toggleId', {
                text: '🏷️ Group ID (____)',
                tooltip: 'Link specific ratings to a specific total cell',
                onAction: () => tableTools.cellPanelInput(
                    'Set Cell Group ID',
                    '.calc-total, .calc-row-avg',
                    'data-group-id',
                    1, 100
                ),
                onSetup: (api) => tableTools.bindCellState(
                    api,
                    '.calc-total, .calc-row-avg',
                    'data-group-id',
                    (val) => `🏷️ Group ID (${val})`
                )
            });

            editor.ui.registry.addButton('toggleCellWeight', {
                text: '⚖️ Cell Weight ____%',
                tooltip: 'Assign a weight specifically to this Total cell',
                onAction: () => tableTools.cellPanelInput(
                    'Set Cell Weight',
                    '.calc-total',
                    'data-cell-weight',
                    1, 100
                ),
                onSetup: (api) => tableTools.bindCellState(
                    api,
                    '.calc-total',
                    'data-cell-weight',
                    (val) => `⚖️ Cell Weight ${val}%`,
                    100
                )
            });

            editor.ui.registry.addButton('toggleScoreRange', {
                text: '💯 Score Range (____)',
                tooltip: 'Set the maximum score range for this entire table',
                
                onAction: () => tableTools.tablePanelInput(
                    'data-score-range',
                    'Set Table Score Range',
                    1, 100
                ),

                onSetup: (api) => tableTools.bindTableState(
                    api, 
                    'data-score-range', 
                    DEFAULT_SCORE_RANGE, 
                    (val) => `💯 Score Range (${val})`
                )
            });
            
            editor.ui.registry.addButton('toggleWeight', {
                text: '⚖️ Weight ____%',
                tooltip: 'Set the percentage weight for this entire table',
                onAction: () => tableTools.tablePanelInput(
                    'data-weight',
                    'Set Table Weight',
                    0,
                    100
                ),

                onSetup: (api) => tableTools.bindTableState(
                    api, 
                    'data-weight', 
                    DEFAULT_WEIGHT, 
                    (val) => `⚖️ Weight ${val}%`
                )
            });        
        }
    });
}

function initPlainEditor(evaluated) {
    tinymce.remove('#editable-doc');
    const isDark = document.documentElement.classList.contains('dark');

    tinymce.init({
        selector: '#editable-doc',
        license_key: 'gpl',
        promotion: false,
        branding: false,
        menubar: false,
        statusbar: false,
        toolbar: false,
        height: '100%',

        plugins: 'saveShortcut, setDirty',
        
        skin: isDark ? 'oxide-dark' : 'oxide',
        content_css: [(isDark ? 'dark' : 'default'), AppConfig.editorCss],

        setup: function(editor) {
            
            editor.on('init', function () {
                const body = editor.getBody();
                
                body.setAttribute('contenteditable', 'false');
                clearMarks(editor, body);

                const markedCells = body.querySelectorAll('.calc-rating,.calc-row-avg, .calc-total, .calc-final-total');
                    markedCells.forEach(cell => {
                    cell.style.backgroundColor = '';
                    cell.removeAttribute('contenteditable');
                });

                if (!evaluated) {
                    const ratingCells = body.querySelectorAll('.calc-rating');
                    ratingCells.forEach(cell => {
                        cell.setAttribute('contenteditable', 'true');
                        cell.style.backgroundColor = 'rgba(16, 185, 129, 0.25)';
                    });
                }
            });
        }
    });
}
