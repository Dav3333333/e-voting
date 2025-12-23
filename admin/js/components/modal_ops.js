class ModalOps {
    constructor() {
        if (!document.querySelector('link[href$="modal_ops.css"]')) {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = 'style/modal_ops.css';
            document.head.appendChild(link);
        }
    }


    /**
     * Show a success modal with a title and message and a Close button
     * @param {String} title
     * @param {String} message 
     */
    showSuccesMessage(title = 'Success', message = ''){
        this._showModal({type: 'success', title, message, buttons: [{text: 'Close', value: 'close'}]});
    }

    /**
     * Show a failure modal with a title and message and a Close button
     * @param {String} title
     * @param {String} message 
     */
    showFailMessage(title = 'Failure', message = ''){
        this._showModal({type: 'fail', title, message, buttons: [{text: 'Close', value: 'close'}]});
    }

    /**
     * Show a warning modal with a title and message and an OK button
     * @param {String} title
     * @param {String} message 
     */
    showWarning(title = 'Warning', message = ''){
        this._showModal({type: 'warning', title, message, buttons: [{text: 'OK', value: 'ok'}]});
    }

    /**
     * Show a confirm modal and resolve to true if confirmed, false if cancelled
     * @param {String} title
     * @param {String} message
     * @returns {Promise<Boolean>}
     */
    showConfirm(title = 'Confirm', message = ''){
        return this._showModal({type: 'confirm', title, message, buttons: [{text: 'Yes', value: true, primary: true},{text: 'No', value: false}]});
    }

    _showModal({type = 'info', title = '', message = '', buttons = []} = {}){
        return new Promise((resolve) => {
            const existing = document.querySelector('.evops-modal-overlay');
            if (existing) existing.remove();

            const overlay = document.createElement('div');
            overlay.className = 'evops-modal-overlay';

            const modal = document.createElement('div');
            modal.className = `evops-modal evops-modal-${type}`;

            const icon = document.createElement('div');
            icon.className = 'evops-modal-icon';
            modal.appendChild(icon);

            const h = document.createElement('h3');
            h.className = 'ev-modal-title';
            h.textContent = title;
            modal.appendChild(h);

            const p = document.createElement('div');
            p.className = 'evops-modal-message';
            if (typeof message === 'string') p.innerHTML = message;
            else p.appendChild(message);
            modal.appendChild(p);

            const actions = document.createElement('div');
            actions.className = 'evops-modal-actions';

            buttons.forEach((btn) => {
                const b = document.createElement('button');
                b.type = 'button';
                b.className = 'evops-modal-btn' + (btn.primary ? ' primary' : '');
                b.textContent = btn.text;
                b.addEventListener('click', () => {
                    overlay.remove();
                    resolve(btn.value === undefined ? true : btn.value);
                });
                actions.appendChild(b);
            });

            modal.appendChild(actions);
            overlay.appendChild(modal);
            document.body.appendChild(overlay);

            // focus first button
            const firstBtn = actions.querySelector('button');
            if (firstBtn) firstBtn.focus();
        });
    }
}

export const modal_ops = new ModalOps()