/**
 * Lógica do Questionário Capilar Step-by-Step - HairMatch
 */

document.addEventListener('DOMContentLoaded', function() {
    const quizForm = document.getElementById('quiz-form');
    if (!quizForm) return;

    let currentStep = 1;
    const totalSteps = 4;

    const progressBar = document.getElementById('quiz-progress-bar');
    const stepLabel = document.getElementById('quiz-step-label');
    const btnPrev = document.getElementById('btn-prev');
    const btnNext = document.getElementById('btn-next');
    const btnSubmit = document.getElementById('btn-submit');

    function updateStep(step) {
        currentStep = step;

        // Atualiza barra de progresso
        const percent = (currentStep / totalSteps) * 100;
        if (progressBar) progressBar.style.width = percent + '%';
        if (stepLabel) stepLabel.textContent = `Etapa ${currentStep} de ${totalSteps}`;

        // Exibe o painel da etapa correspondente
        for (let i = 1; i <= totalSteps; i++) {
            const panel = document.getElementById(`quiz-step-${i}`);
            if (panel) {
                if (i === currentStep) {
                    panel.classList.remove('hidden');
                    panel.classList.add('animate-fade-in');
                } else {
                    panel.classList.add('hidden');
                    panel.classList.remove('animate-fade-in');
                }
            }
        }

        // Controla visibilidade dos botões
        if (btnPrev) {
            btnPrev.classList.toggle('invisible', currentStep === 1);
        }
        if (btnNext && btnSubmit) {
            if (currentStep === totalSteps) {
                btnNext.classList.add('hidden');
                btnSubmit.classList.remove('hidden');
            } else {
                btnNext.classList.remove('hidden');
                btnSubmit.classList.add('hidden');
            }
        }

        window.scrollTo({ top: quizForm.offsetTop - 100, behavior: 'smooth' });
    }

    function validateCurrentStep() {
        if (currentStep === 1) {
            const selected = quizForm.querySelector('input[name="hair_color"]:checked');
            if (!selected) {
                AppToast.show('Por favor, selecione a cor do seu cabelo para continuar.', 'warning');
                return false;
            }
        } else if (currentStep === 2) {
            const selected = quizForm.querySelector('input[name="curvature"]:checked');
            if (!selected) {
                AppToast.show('Por favor, selecione a curvatura do seu fio.', 'warning');
                return false;
            }
        } else if (currentStep === 3) {
            const selected = quizForm.querySelectorAll('input[name="problems[]"]:checked');
            if (selected.length === 0) {
                AppToast.show('Selecione pelo menos um problema capilar que deseja tratar.', 'warning');
                return false;
            }
        } else if (currentStep === 4) {
            const selected = quizForm.querySelector('input[name="goal"]:checked');
            if (!selected) {
                AppToast.show('Selecione o seu objetivo principal de tratamento.', 'warning');
                return false;
            }
        }
        return true;
    }

    if (btnNext) {
        btnNext.addEventListener('click', function() {
            if (validateCurrentStep()) {
                if (currentStep < totalSteps) {
                    updateStep(currentStep + 1);
                }
            }
        });
    }

    if (btnPrev) {
        btnPrev.addEventListener('click', function() {
            if (currentStep > 1) {
                updateStep(currentStep - 1);
            }
        });
    }

    // Inicializa na etapa 1
    updateStep(1);
});
