class TableTools {
    constructor(editor) {
        this.editor = editor;
        this.activeSelectionQuery = 'td[data-mce-selected="1"], th[data-mce-selected="1"], .custom-selected';
        this.classNames = ['calc-rating', 'calc-row-avg', 'calc-total', 'calc-final-total', 'remarks'];
        this.attrNames = ['data-group-id', 'data-cell-weight'];
    }

    clearMathClasses(cell) {
        this.editor.dom.removeClass(cell, this.classNames.join(' '));
        this.attrNames.forEach(attr => {
            this.editor.dom.setAttrib(cell, attr, null);
        });
        
        this.editor.dom.setAttrib(cell, 'style', null); 
    }

    modifyCell(markType, markColor) {
        AppState.setDirty(true);
        
        let selectedCells = this.editor.dom.select(this.activeSelectionQuery);
        
        if (selectedCells.length === 0) {
            const singleCell = this.editor.dom.getParent(this.editor.selection.getNode(), 'td, th');
            if (singleCell) selectedCells = [singleCell];
        }

        if (selectedCells.length > 0) {
            selectedCells.forEach(cell => {
                this.clearMathClasses(cell); 

                if (!markType) {
                    this.editor.dom.setAttrib(cell, 'data-mce-selected', null);
                    this.editor.dom.removeClass(cell, 'custom-selected');
                } else {
                    console.log('dsasad');
                    this.editor.dom.addClass(cell, markType);
                    this.editor.dom.setStyle(cell, 'background-color', markColor)
                }
            });
        }
    }

    openValueDialog(title, currentValue, config, onsave) {
        const { minValue, maxValue, isRequired = true} = config;

        this.editor.windowManager.open({
            title: title,
            size: 'small',
            body: {
                type: 'panel',
                items: [
                    {
                        type: 'input',
                        name: 'value',
                        placeholder: `Value (${minValue} - ${maxValue})`
                    }
                ]
            },
            initialData: {
                value: currentValue
            },
            buttons: [
                { type: 'cancel', text: 'Cancel' },
                { type: 'submit', text: 'Save', buttonType: 'primary' }
            ],
            onSubmit: (api) => {
                const data = api.getData();
                const newValue = data.value.trim();
                const numValue = Number(newValue);

                if (isRequired && !newValue) {
                    this.editor.windowManager.alert('This field is required');
                    return
                }

                if (newValue && (isNaN(numValue) || numValue < minValue || numValue > maxValue)) {
                    this.editor.windowManager.alert(`Please enter a valid number between ${minValue} and ${maxValue}.`);
                    return;
                }

                onsave(newValue);

                this.editor.nodeChanged();
                api.close(); 
            }
        });
    }

    tablePanelInput(attrName, title, minValue, maxValue) {
        const currentNode = this.editor.selection.getNode();
        const parentTable = this.editor.dom.getParent(currentNode, 'table');

        if (parentTable) {
            let currentValue = this.editor.dom.getAttrib(parentTable, attrName) || '';

            this.openValueDialog(title, currentValue, {minValue, maxValue}, (finalValue) => {
                this.editor.dom.setAttrib(parentTable, attrName, finalValue);
            })
        } 
    }

    cellPanelInput(title, markType, attrName, minValue, maxValue) {
        let selectedCells = this.editor.dom.select(this.activeSelectionQuery);
        
        if (selectedCells.length === 0) {
            const singleCell = this.editor.dom.getParent(this.editor.selection.getNode(), 'td, th');
            if (singleCell) selectedCells = [singleCell];
        }

        const markedCells = selectedCells.filter(cell => 
                cell.matches(markType)
            );
        
        if (markedCells.length > 0) {       
            const nodeCell = this.editor.dom.getParent(this.editor.selection.getNode(), 'td, th');
            const marked = markedCells.some(cell => cell === nodeCell);
            const currentValue = marked ? (nodeCell.getAttribute(attrName) || '') : '';

            this.openValueDialog(title, currentValue, {minValue, maxValue, isRequired: false}, (finalValue) => {
                markedCells.forEach(cell => {
                    this.editor.dom.setAttrib(cell, attrName, finalValue || null);
                });
            })
        }
    }

    bindTableState(api, attrName, defaultValue, textFormatter) {
        const updateButtonState = () => {
            const currentNode = this.editor.selection.getNode();
            const parentTable = this.editor.dom.getParent(currentNode, 'table');
            
            if (parentTable) {
                api.setEnabled(true);

                if (parentTable.hasAttribute(attrName)) {
                    const val = parentTable.getAttribute(attrName);
                    api.setText(textFormatter(val));
                } else {
                    this.editor.dom.setAttrib(parentTable, attrName, defaultValue);
                    api.setText(textFormatter(defaultValue)); 
                }
            } else {
                api.setEnabled(false);
                api.setText(textFormatter('')); 
            }
        };

        this.editor.on('NodeChange', updateButtonState);
        return () => { this.editor.off('NodeChange', updateButtonState); };
    }

    bindCellState(api, markType, attrName, textFormatter, defaultValue) {
        const updateButtonState = () => {
            let selectedCells = this.editor.dom.select(this.activeSelectionQuery);
        
            if (selectedCells.length === 0) {
                const singleCell = this.editor.dom.getParent(this.editor.selection.getNode(), 'td, th');
                if (singleCell) selectedCells = [singleCell];
            }

            const markedCells = selectedCells.filter(cell => 
                cell.matches(markType)
            );

            const nodeCell = this.editor.dom.getParent(this.editor.selection.getNode(), 'td, th');
            const marked = markedCells.some(cell => cell === nodeCell);

            if (markedCells.length >= 1) {
                api.setEnabled(true);

                markedCells.forEach(cell => {
                    if (!cell.hasAttribute(attrName) && defaultValue) {
                        cell.setAttribute(attrName, defaultValue);
                    }
                });

                const val = marked ? (nodeCell.getAttribute(attrName) || '') : '';
                api.setText(textFormatter(val));
            } else {
                api.setEnabled(false);
                api.setText(textFormatter(''));
            }
        }; 

        this.editor.on('NodeChange', updateButtonState);
        return () => { this.editor.off('NodeChange', updateButtonState); };
    }
}