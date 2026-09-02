tinymce.PluginManager.add('saveShortcut', function(editor) {
    editor.addShortcut('meta+s', 'Save Document', () => saveDocument());
});

tinymce.PluginManager.add('setDirty', function(editor) {
    editor.on('input ExecCommand', function (e) {
        if (e.type === 'execcommand') {
            const ignoredCommands = ['mceFocus', 'mceAutoResize'];

            if (ignoredCommands.includes(e.command)) {
                return;
            }
        }

        if (editor.initialized && !AppState.isDirty) {
            AppState.setDirty(true);
        }
    });
})

tinymce.PluginManager.add('cellSelect', function(editor) {
    editor.on('click', function(e) {
        if (e.ctrlKey || e.metaKey) {
            const targetCell = editor.dom.getParent(e.target, 'td, th');
            if (targetCell) {
                e.preventDefault(); 
                const isSelected = editor.dom.hasClass(targetCell, 'custom-selected');
                
                if (isSelected) {
                    editor.dom.removeClass(targetCell, 'custom-selected');
                } else {
                    editor.dom.addClass(targetCell, 'custom-selected');
                }
            }
        } else {
            const selectedCells = editor.dom.select('.custom-selected');
            selectedCells.forEach(cell => {
                editor.dom.removeClass(cell, 'custom-selected');
            });
        }
    });
})

tinymce.PluginManager.add('disableBackgroundCloning', function(editor) {
    editor.on('ExecCommand', function (e) {
            
            // ==========================================
            // 1. HANDLE ROW INSERTIONS
            // ==========================================
            if (e.command === 'mceTableInsertRowAfter' || e.command === 'mceTableInsertRowBefore') {
                const newRow = editor.dom.getParent(editor.selection.getStart(), 'tr');
                
                if (newRow) {
                    editor.dom.setStyle(newRow, 'background-color', '');
                    const cells = newRow.querySelectorAll('td, th');
                    cells.forEach(cell => {
                        editor.dom.setStyle(cell, 'background-color', '');
                    });
                }
            }

            // ==========================================
            // 2. HANDLE COLUMN INSERTIONS
            // ==========================================
            if (e.command === 'mceTableInsertColAfter' || e.command === 'mceTableInsertColBefore') {
                // Grab the cell where the cursor landed (this is the newly created cell)
                const currentCell = editor.dom.getParent(editor.selection.getStart(), 'td, th');
                
                if (currentCell) {
                    // Find the row it belongs to
                    const currentRow = editor.dom.getParent(currentCell, 'tr');
                    
                    // Calculate the index of our new column (e.g., index 2 = 3rd column)
                    const cellIndex = Array.from(currentRow.children).indexOf(currentCell);
                    
                    // Find the parent table so we can loop through it
                    const table = editor.dom.getParent(currentRow, 'table');
                    
                    if (table && cellIndex > -1) {
                        // Loop through every row in the table
                        const rows = table.querySelectorAll('tr');
                        rows.forEach(row => {
                            // Grab the cell at the exact index of our new column
                            const cellInColumn = row.children[cellIndex];
                            
                            if (cellInColumn) {
                                // Strip the background color
                                editor.dom.setStyle(cellInColumn, 'background-color', '');
                                
                                // Optional: If you use classes for background colors, remove them here:
                                // editor.dom.removeClass(cellInColumn, 'your-custom-bg-class');
                            }
                        });
                    }
                }
            }

        });
});

tinymce.PluginManager.add('addRowButton', function(editor) {
    editor.on('init', function() {
        // Use capture phase to intercept before TinyMCE does
        editor.getBody().addEventListener('mousedown', function(e) {
            const button = e.target.closest('.add-row-btn');
            if (button) {
                e.preventDefault();
                e.stopPropagation();
            }
        }, true);
        
        editor.getBody().addEventListener('dblclick', function(e) {
            const button = e.target.closest('.add-row-btn');
            if (button) {
                e.preventDefault();
                e.stopPropagation();
                editor.windowManager.open({
                    title: 'Rename Button',
                    size: 'small',
                    body: {
                        type: 'panel',
                        items: [{
                            type: 'input',
                            name: 'label',
                            label: 'New Button Label'
                        }]
                    },
                    initialData: {
                        label: button.innerText
                    },
                    buttons: [
                        { type: 'cancel', text: 'Cancel' },
                        { type: 'submit', text: 'Rename', buttonType: 'primary' }
                    ],
                    onSubmit: function (api) {
                        const data = api.getData();
                        const newName = data.label.trim();
                        if (newName) {
                            const safeName = newName.replace(/</g, '&lt;').replace(/>/g, '&gt;');
                            button.innerHTML = safeName;
                            if (typeof AppState !== 'undefined') AppState.setDirty(true);
                            editor.nodeChanged();
                            api.close();
                        } else {
                            editor.windowManager.alert('Label cannot be empty.');
                        }
                    }
                });
            }
        }, true);
        
        editor.getBody().addEventListener('click', function(e) {
            const button = e.target.closest('.add-row-btn');
            if (button) {
                e.preventDefault();
                e.stopPropagation();
                
                const buttonRow = button.closest('tr');
                if (buttonRow) {
                    const previousRow = buttonRow.previousElementSibling;
                    if (previousRow) {
                        const newRow = previousRow.cloneNode(true);
                        const cells = newRow.querySelectorAll('td, th');
                        cells.forEach(cell => {
                            cell.innerHTML = '&nbsp;';
                        });
                        
                        buttonRow.parentNode.insertBefore(newRow, buttonRow);
                        if (typeof AppState !== 'undefined') AppState.setDirty(true);
                        
                        editor.nodeChanged();
                    }
                }
            }
        }, true);
    });
});