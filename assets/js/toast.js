/**
 * Gerenciador de Notificações Toast - HairMatch
 * Regra: "Retorne sempre as mensagens de erros de forma claras na interface para o usuário (TOAST)"
 */

const AppToast = {
    container: null,

    init() {
        if (!this.container) {
            this.container = document.getElementById('toast-container');
            if (!this.container) {
                this.container = document.createElement('div');
                this.container.id = 'toast-container';
                this.container.className = 'fixed top-5 right-5 z-50 flex flex-col gap-3 pointer-events-none max-w-sm w-full px-4';
                document.body.appendChild(this.container);
            }
        }
    },

    show(message, type = 'info', duration = 4500) {
        this.init();

        const toast = document.createElement('div');
        toast.className = 'pointer-events-auto flex items-start gap-3 p-4 rounded-xl shadow-xl border backdrop-blur-md transform transition-all duration-300 animate-slide-in';

        // Configuração por tipo de toast
        const configs = {
            success: {
                bg: 'bg-emerald-50 border-emerald-200 text-emerald-900',
                icon: 'fa-circle-check text-emerald-600',
                title: 'Sucesso'
            },
            error: {
                bg: 'bg-rose-50 border-rose-200 text-rose-900',
                icon: 'fa-circle-xmark text-rose-600',
                title: 'Atenção / Erro'
            },
            warning: {
                bg: 'bg-amber-50 border-amber-200 text-amber-900',
                icon: 'fa-triangle-exclamation text-amber-600',
                title: 'Aviso'
            },
            info: {
                bg: 'bg-indigo-50 border-indigo-200 text-indigo-900',
                icon: 'fa-circle-info text-indigo-600',
                title: 'Informação'
            }
        };

        const config = configs[type] || configs.info;
        toast.className += ' ' + config.bg;

        toast.innerHTML = `
            <div class="text-xl flex-shrink-0 mt-0.5">
                <i class="fa-solid ${config.icon}"></i>
            </div>
            <div class="flex-1 text-sm">
                <div class="font-bold mb-0.5">${config.title}</div>
                <div class="text-slate-700 leading-snug">${message}</div>
            </div>
            <button type="button" class="text-slate-400 hover:text-slate-700 transition p-1 text-sm" onclick="this.parentElement.remove()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        `;

        this.container.appendChild(toast);

        // Auto remove com fade out
        if (duration > 0) {
            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-x-full');
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, duration);
        }
    }
};

window.AppToast = AppToast;
document.addEventListener('DOMContentLoaded', () => AppToast.init());
