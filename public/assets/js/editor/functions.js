function calculateAllTables() {
    const editorBody = tinymce.get('editable-doc').getBody();
    const tables = editorBody.querySelectorAll('table');
    
    let grandTotalScore = 0; 

    if (tables.length === 0) {
        return;
    }

    tables.forEach(function(table) {
        // HORIZONTAL (Row Averages & Adjectival Ratings)
        const rows = table.querySelectorAll('tr');
        
        rows.forEach(row => {
            const inputs = row.querySelectorAll('.calc-rating'); 
            const rowAvgCells = row.querySelectorAll('.calc-row-avg'); 
            const arCells = row.querySelectorAll('.calc-ar');
            
            if (inputs.length > 0 && rowAvgCells.length > 0) {
                let sum = 0;
                let count = 0;
                
                inputs.forEach(input => {
                    const val = parseFloat(input.innerText.trim() || 0);
                    if (!isNaN(val)) {
                        sum += val;
                        count++;
                    }
                });
                
                if (count > 0) {
                    const avg = sum / count; // Row Average

                    rowAvgCells.forEach(cell => {
                        cell.innerText = avg.toFixed(2);
                        cell.setAttribute('data-row-avg', avg);
                    });

                    // Adjectival Rating for this specific row
                    arCells.forEach(cell => {
                        cell.innerText = getAdjectivalRating(avg);
                        cell.style.fontWeight = 'bold'; 
                    });
                }
            }
        });

        // VERTICAL (The Category Groupings)
        const rowAvgCells = Array.from(table.querySelectorAll('.calc-row-avg'));
        const totalCells = Array.from(table.querySelectorAll('.calc-total'));
        
        let groupedData = {};
        
        rowAvgCells.forEach(cell => {
            let groupId = cell.getAttribute('data-group-id') || 'no-id';
            
            if (!groupedData[groupId]) {
                groupedData[groupId] = { sumOfAvgs: 0, rowCount: 0};
            }

            const rowAvg = parseFloat(cell.getAttribute('data-row-avg'));

            if (!isNaN(rowAvg)) {
                groupedData[groupId].sumOfAvgs += rowAvg;
                groupedData[groupId].rowCount++;
            }
        });

        let groupedTotals = {};
        totalCells.forEach(cell => {
            let groupId = cell.getAttribute('data-group-id') || 'no-id';
            if (!groupedTotals[groupId]) {
                groupedTotals[groupId] = [];
            }
            groupedTotals[groupId].push(cell);
        });

        // PHASE 3: CATEGORY AVERAGES & WEIGHTS (ABSOLUTE NUMBERS, NO PERCENTAGES)
        for (const groupId in groupedData) {
            const group = groupedData[groupId];
            
            if (group.rowCount > 0) {
                // 1. Get Category Average: (Sum of row avgs / row avg length)
                let categoryAvg = group.sumOfAvgs / group.rowCount; 
                
                let groupWeight = 1; // Default to 1 (100% weight) if not specified

                if (groupedTotals[groupId]) {
                    groupedTotals[groupId].forEach(totalCell => {
                        if (totalCell.hasAttribute('data-cell-weight')) {
                            let parsedWeight = parseFloat(totalCell.getAttribute('data-cell-weight'));
                            if (!isNaN(parsedWeight)) {
                                // Smart Check: If they typed "50" instead of "0.50", convert it!
                                groupWeight = parsedWeight > 1 ? (parsedWeight / 100) : parsedWeight;
                            }
                        }
                        
                        // 2. Calculation: Category Average * Weight
                        let weightedTotal = categoryAvg * groupWeight;
                        
                        // Output numeric score (e.g., 2.500)
                        totalCell.innerText = weightedTotal.toFixed(2);
                    });
                }

                // 3. Add to the final Grand Total
                let finalWeightedScore = categoryAvg * groupWeight;
                grandTotalScore += finalWeightedScore;
            }
        }
    });

    // PHASE 4: OUTPUT TO DASHBOARD AND FINAL CELLS
    const finalTotalCells = editorBody.querySelectorAll('.calc-final-total');
    finalTotalCells.forEach(cell => {
        // Output max of 5.000 without any percentage sign
        cell.innerText = grandTotalScore.toFixed(2); 
    });

    // Populate the Grand Total Adjectival Rating (O, VS, S, etc.)
    const finalARCells = editorBody.querySelectorAll('.calc-final-ar');
    finalARCells.forEach(cell => {
        cell.innerText = getAdjectivalRating(grandTotalScore);
    });

    editorBody.setAttribute('data-final-score', grandTotalScore.toFixed(4));
    tinymce.get('editable-doc').save();
}

function autoResize(el) {
        el.style.width = '0';
        el.style.width = el.scrollWidth + 'px';
}

function restoreTitle(el, defaultTitle) {
    if (el.value.trim() === '') {
    el.value = defaultTitle;
    }
    autoResize(el);
}

function clearMarks(editor, body) {
    const tableTools = new TableTools(editor);
    const allAttribs = tableTools.classNames;

    const markedCells = body.querySelectorAll(allAttribs);
    markedCells.forEach(cell => {
        cell.removeAttribute('style');
    });
}

function cellInspector(editor) {
    editor.on('click', function(e) {
        const targetCell = editor.dom.getParent(e.target, 'td, th');
        
        if (targetCell) {
            const parentRow = editor.dom.getParent(targetCell, 'tr');
            
            const rowIndex = parentRow.rowIndex; 
            const colIndex = targetCell.cellIndex;

            const classNames = targetCell.className || 'None';
            const groupId = editor.dom.getAttrib(targetCell, 'data-group-id') || 'None';
            const cellWeight = editor.dom.getAttrib(targetCell, 'data-cell-weight') || 'None';

            console.group(`🎯 Cell Clicked: [Row ${rowIndex}, Col ${colIndex}]`);
            console.table({
                "Row Index": rowIndex,
                "Column Index": colIndex,
                "Classes": classNames,
                "Group ID": groupId,
                "Cell Weight": cellWeight
            });
            console.log("DOM Element:", targetCell);
            console.groupEnd();
        }
    });
}

function updateDisplayDate(input, spanId) {
    if (!input.value) return;
    
    const date = new Date(input.value);

    let formatted = new Intl.DateTimeFormat('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    }).format(date);
    
    formatted = formatted.replace(' at ', ' ').toLowerCase();
    formatted = formatted.charAt(0).toUpperCase() + formatted.slice(1);

    document.getElementById(spanId).innerText = formatted;

    if (input.id === 'doc-date-start') {
        const endInput = document.getElementById('doc-date-end');
        
        // FIX: Add safety check to ensure endInput actually exists in the DOM
        if (endInput) {
            endInput.min = input.value;
            if (endInput.value < input.value) {
                endInput.value = input.value;
                updateDisplayDate(endInput, 'display-date-end');
            }
        }
    }
}