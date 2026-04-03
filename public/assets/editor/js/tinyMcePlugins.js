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