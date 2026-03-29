function calculateAllTables() {
    const editorBody = tinymce.get('editable-doc').getBody();
    const tables = editorBody.querySelectorAll('table');
    
    let grandTotalScore = 0; 
    let groupsCalculated = 0;

    if (tables.length === 0) {
        return;
    }

    tables.forEach(function(table) {
        // HORIZONTAL (Row Averages)
        const rows = table.querySelectorAll('tr');
        
        rows.forEach(row => {
            const inputs = row.querySelectorAll('.calc-rating'); 
            const rowAvgCells = row.querySelectorAll('.calc-row-avg'); 
            
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
                    const avg = sum / count;

                    rowAvgCells.forEach(cell => {
                        cell.innerText = avg.toFixed(2);
                        
                        cell.setAttribute('data-row-avg', avg);
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

        // PHASE 3: TOTAL PERCENTAGES & WEIGHTS
        const maxScore = parseFloat(table.getAttribute('data-score-range')) || 0;
        
        for (const groupId in groupedData) {
            const group = groupedData[groupId];
            
            if (group.rowCount > 0) {
                let categoryAvg = group.sumOfAvgs / group.rowCount;
                let groupPercentage = categoryAvg / maxScore;
                
                let groupWeight = 100; 

                if (groupedTotals[groupId]) {
                    groupedTotals[groupId].forEach(totalCell => {
                        if (totalCell.hasAttribute('data-cell-weight')) {
                            let parsedWeight = parseFloat(totalCell.getAttribute('data-cell-weight'));
                            if (!isNaN(parsedWeight)) groupWeight = parsedWeight;
                        }
                        
                        totalCell.innerText = (groupPercentage * 100).toFixed(2) + '%';
                    });
                }

                let finalWeightedScore = (groupPercentage * 100) * (groupWeight / 100);
                grandTotalScore += finalWeightedScore;
                groupsCalculated++;
            }
        }
    });

    // PHASE 4: OUTPUT TO DASHBOARD AND FINAL CELLS
    const finalTotalCells = editorBody.querySelectorAll('.calc-final-total');
    finalTotalCells.forEach(cell => {
        cell.innerText = grandTotalScore.toFixed(2) + '%';
    });

    const dashboardRatingSpan = document.getElementById('rating');
    if (dashboardRatingSpan) {
        dashboardRatingSpan.innerText = grandTotalScore.toFixed(2) + '%';
        
        if (grandTotalScore >= 90) dashboardRatingSpan.className = "text-green-600 font-bold";
        else if (grandTotalScore >= 75) dashboardRatingSpan.className = "text-blue-600 font-bold";
        else if (grandTotalScore >= 50) dashboardRatingSpan.className = "text-yellow-600 font-bold";
        else dashboardRatingSpan.className = "text-red-600 font-bold";
    }

    console.log(`Calculated ${groupsCalculated} groups. Final Rating: ${grandTotalScore.toFixed(2)}%`);
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