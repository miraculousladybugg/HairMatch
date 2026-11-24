/**
 * Gerenciador de Modais Customizados - HairMatch
 * Regra: "sempre trate as mensagens de caixa de mensagens nativas do navegador em um MODAL"
 * Substitui o window.alert nativo e intercepta ações de confirmação
 */

const AppModal = {
    overlay: null,

    init() {
        if (!this.overlay) {
            this.overlay = document.getElementById('app-modal-overlay');
            if (!this.overlay) {
                this.overlay = document.createElement('div');
                this.overlay.id = 'app-modal-overlay';
                this.overlay.className = 'fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 opacity-0 pointer-events-none transition-opacity duration-200';
                this.overlay.innerHTML = `
                    <div id="app-modal-box" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl transform scale-95 transition-transform duration-200 border border-slate-100">
                        <div id="app-modal-header" class="flex items-center gap-3 mb-3">
                            <div id="app-modal-icon" class="w-10 h-10 rounded-full flex items-center justify-center text-lg"></div>
                            <h3 id="app-modal-title" class="text-lg font-bold text-slate-900"></h3>
                        </div>
                        <p id="app-modal-message" class="text-slate-600 text-sm leading-relaxed mb-6"></p>
                        <div id="app-modal-actions" class="flex items-center justify-end gap-3"></div>
                    </div>
                `;
                document.body.appendChild(this.overlay);

                // Fechar ao clicar no backdrop (se não for confirm obrigatório)
                this.overlay.addEventListener('click', (e) => {
                    if (e.target === this.overlay && this.canCloseOnBackdrop) {
                        this.close();
                    }
                });
            }
        }
    },

    alert(options) {
        this.init();
        const opts = typeof options === 'string' ? { message: options } : options;
        const title = opts.title || 'Aviso';
        const message = opts.message || '';
        const type = opts.type || 'info';
        const confirmText = opts.confirmText || 'Entendido';

        this.canCloseOnBackdrop = true;

        const iconBox = document.getElementById('app-modal-icon');
        const titleEl = document.getElementById('app-modal-title');
        const messageEl = document.getElementById('app-modal-message');
        const actionsEl = document.getElementById('app-modal-actions');

        titleEl.textContent = title;
        messageEl.textContent = message;

        // Ícone baseado no tipo
        if (type === 'error') {
            iconBox.className = 'w-10 h-10 rounded-full flex items-center justify-center text-lg bg-rose-100 text-rose-600';
            iconBox.innerHTML = '<i class="fa-solid fa-circle-xmark"></i>';
        } else if (type === 'success') {
            iconBox.className = 'w-10 h-10 rounded-full flex items-center justify-center text-lg bg-emerald-100 text-emerald-600';
            iconBox.innerHTML = '<i class="fa-solid fa-circle-check"></i>';
        } else if (type === 'warning') {
            iconBox.className = 'w-10 h-10 rounded-full flex items-center justify-center text-lg bg-amber-100 text-amber-600';
            iconBox.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i>';
        } else {
            iconBox.className = 'w-10 h-10 rounded-full flex items-center justify-center text-lg bg-pink-100 text-pink-600';
            iconBox.innerHTML = '<i class="fa-solid fa-sparkles"></i>';
        }

        actionsEl.innerHTML = `
            <button type="button" id="app-modal-btn-confirm" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-pink-600 hover:bg-pink-700 text-white font-medium text-sm transition shadow-sm">
                ${confirmText}
            </button>
        `;

        document.getElementById('app-modal-btn-confirm').addEventListener('click', () => {
            this.close();
            if (typeof opts.onConfirm === 'function') opts.onConfirm();
        });

        this.open();
    },

    confirm(options) {
        return new Promise((resolve) => {
            this.init();
            const opts = typeof options === 'string' ? { message: options } : options;
            const title = opts.title || 'Confirmação';
            const message = opts.message || '';
            const confirmText = opts.confirmText || 'Confirmar';
            const cancelText = opts.cancelText || 'Cancelar';
            const isDanger = opts.danger || false;

            this.canCloseOnBackdrop = false;

            const iconBox = document.getElementById('app-modal-icon');
            const titleEl = document.getElementById('app-modal-title');
            const messageEl = document.getElementById('app-modal-message');
            const actionsEl = document.getElementById('app-modal-actions');

            titleEl.textContent = title;
            messageEl.textContent = message;

            iconBox.className = isDanger 
                ? 'w-10 h-10 rounded-full flex items-center justify-center text-lg bg-rose-100 text-rose-600'
                : 'w-10 h-10 rounded-full flex items-center justify-center text-lg bg-pink-100 text-pink-600';
            iconBox.innerHTML = isDanger ? '<i class="fa-solid fa-trash-can"></i>' : '<i class="fa-solid fa-circle-question"></i>';

            actionsEl.innerHTML = `
                <button type="button" id="app-modal-btn-cancel" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-sm transition">
                    ${cancelText}
                </button>
                <button type="button" id="app-modal-btn-confirm" class="px-5 py-2.5 rounded-xl font-medium text-sm transition text-white shadow-sm ${isDanger ? 'bg-rose-600 hover:bg-rose-700' : 'bg-pink-600 hover:bg-pink-700'}">
                    ${confirmText}
                </button>
            `;

            document.getElementById('app-modal-btn-cancel').addEventListener('click', () => {
                this.close();
                if (typeof opts.onCancel === 'function') opts.onCancel();
                resolve(false);
            });

            document.getElementById('app-modal-btn-confirm').addEventListener('click', () => {
                this.close();
                if (typeof opts.onConfirm === 'function') opts.onConfirm();
                resolve(true);
            });

            this.open();
        });
    },

    open() {
        this.overlay.classList.remove('opacity-0', 'pointer-events-none');
        document.getElementById('app-modal-box').classList.remove('scale-95');
        document.getElementById('app-modal-box').classList.add('scale-100');
    },

    close() {
        if (!this.overlay) return;
        this.overlay.classList.add('opacity-0', 'pointer-events-none');
        document.getElementById('app-modal-box').classList.remove('scale-100');
        document.getElementById('app-modal-box').classList.add('scale-95');
    }
};

// Sobrescreve o alert nativo do navegador para cumprir a regra de negócio
window.alert = function(msg) {
    AppModal.alert({ title: 'Aviso do Sistema', message: String(msg) });
};

window.AppModal = AppModal;

// Interceptador global de data-confirm para botões ou links
document.addEventListener('DOMContentLoaded', () => {
    AppModal.init();

    document.body.addEventListener('click', function(e) {
        const trigger = e.target.closest('[data-confirm]');
        if (trigger) {
            e.preventDefault();
            const message = trigger.getAttribute('data-confirm');
            const title = trigger.getAttribute('data-confirm-title') || 'Confirmar Ação';
            const isDanger = trigger.classList.contains('text-rose-600') || trigger.classList.contains('bg-rose-600') || trigger.getAttribute('data-danger') === 'true';

            AppModal.confirm({
                title: title,
                message: message,
                confirmText: trigger.getAttribute('data-confirm-btn') || 'Sim, continuar',
                cancelText: 'Cancelar',
                danger: isDanger,
                onConfirm: () => {
                    if (trigger.tagName === 'A' && trigger.href) {
                        window.location.href = trigger.href;
                    } else if (trigger.form) {
                        trigger.form.submit();
                    }
                }
            });
        }
    });
});
