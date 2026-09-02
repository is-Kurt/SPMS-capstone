export default class Modal {
    constructor(modalId, options = {}) {
        this.element = document.getElementById(modalId);
        this.onClose = options.onClose || null;
        this.onOpen = options.onOpen || null;
        this.setupEventListeners();
    }

    open() {
        this.element.classList.remove('hidden');
        this.element.classList.add('flex');
        if (this.onOpen) this.onOpen();
    }

    close() {
        this.element.classList.add('hidden');
        this.element.classList.remove('flex');
        
        const inputs = this.element.querySelectorAll('input, select, textarea');
        inputs.forEach(input => input.value = '');
        
        const errors = this.element.querySelectorAll('[id$="-error"]');
        errors.forEach(err => err.classList.add('hidden'));

        if (this.onClose) this.onClose();
    }

    setupEventListeners() {
        this.element.addEventListener('click', (e) => {
            if (e.target === this.element) this.close();
        });
    }
}