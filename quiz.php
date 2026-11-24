<?php
/**
 * Questionário Capilar Step-by-Step - HairMatch
 * Core Requirement: Responder o questionário capilar (cor, curvatura, problemas e objetivos)
 */

$pageTitle = 'Questionário Capilar Personalizado';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/backend/quiz.php';

$structure = getQuizStructure();
$user = getCurrentUser();
$existingProfile = $user ? getUserHairProfile((int)$user['id']) : ($_SESSION['hair_profile'] ?? null);

// Processamento do formulário
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $hairColor = $_POST['hair_color'] ?? '';
    $curvature = $_POST['curvature'] ?? '';
    $problems = $_POST['problems'] ?? [];
    $goal = $_POST['goal'] ?? '';
    $washFreq = $_POST['wash_frequency'] ?? 'alternada';
    $chemTreated = !empty($_POST['chemical_treated']) ? 1 : 0;

    $curvatureGroup = substr($curvature, 0, 1);
    $hairType = match ($curvatureGroup) {
        '1' => 'liso',
        '2' => 'ondulado',
        '3' => 'cacheado',
        '4' => 'crespo',
        default => 'cacheado'
    };

    if (empty($hairColor) || empty($curvature) || empty($problems) || empty($goal)) {
        setFlash('error', 'Por favor, selecione todas as opções obrigatórias para o diagnóstico.');
    } else {
        if ($user) {
            // Salva no banco de dados via PDO
            $result = saveHairProfile((int)$user['id'], [
                'hair_color' => $hairColor,
                'curvature' => $curvature,
                'problems' => $problems,
                'goal' => $goal,
                'wash_frequency' => $washFreq,
                'chemical_treated' => $chemTreated
            ]);
            setFlash('success', 'Diagnóstico capilar concluído com sucesso! Veja abaixo os produtos ideais para o seu perfil.');
        } else {
            // Salva na sessão nativa do visitante
            $_SESSION['hair_profile'] = [
                'hair_color' => $hairColor,
                'hair_type' => $hairType,
                'curvature' => $curvature,
                'problems' => is_array($problems) ? $problems : [$problems],
                'goal' => $goal,
                'wash_frequency' => $washFreq,
                'chemical_treated' => $chemTreated
            ];
            setFlash('success', 'Diagnóstico capilar concluído! Veja os produtos com maior match. Crie uma conta para salvar e avaliar!');
        }

        // Redireciona imediatamente para a vitrine pós-questionário
        header('Location: ' . BASE_URL . '/produtos.php');
        exit;
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="py-12 bg-slate-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Topo com Indicador de Progresso -->
        <div class="text-center max-w-2xl mx-auto mb-8 space-y-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-hair-100 text-hair-800 text-xs font-bold uppercase tracking-wider">
                <i class="fa-solid fa-sparkles text-hair-600"></i>
                <span>Diagnóstico Capilar Inteligente</span>
            </span>
            <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                Descubra a fórmula ideal para o seu cabelo
            </h1>
            <p class="text-slate-600 text-sm">
                Responda com sinceridade às etapas abaixo para que nosso algoritmo ranqueie os melhores produtos segundo relatos reais.
            </p>

            <!-- Barra de Progresso Visual -->
            <div class="pt-4">
                <div class="flex items-center justify-between text-xs font-bold text-slate-500 mb-2">
                    <span id="quiz-step-label">Etapa 1 de 4</span>
                    <span class="text-hair-600">Recomendação Personalizada</span>
                </div>
                <div class="w-full bg-slate-200 rounded-full h-2.5 overflow-hidden">
                    <div id="quiz-progress-bar" class="bg-gradient-to-r from-hair-500 to-hair-600 h-2.5 rounded-full transition-all duration-300 w-1/4"></div>
                </div>
            </div>
        </div>

        <!-- Formulário do Quiz -->
        <div class="bg-white rounded-3xl p-6 sm:p-10 shadow-xl border border-slate-200/80">
            <form id="quiz-form" method="POST" action="<?= BASE_URL ?>/quiz.php">
                
                <!-- ========================================================================= -->
                <!-- ETAPA 1: COR DO CABELO -->
                <!-- ========================================================================= -->
                <div id="quiz-step-1" class="space-y-6">
                    <div class="border-b border-slate-100 pb-4">
                        <span class="text-xs font-bold text-hair-600 uppercase tracking-wider">Passo 1</span>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">Qual é a cor atual do seu cabelo?</h2>
                        <p class="text-slate-500 text-xs sm:text-sm mt-1">Isso ajuda a selecionar ativos matizadores, protetores de cor ou potencializadores de brilho.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php foreach ($structure['colors'] as $key => $color): ?>
                            <?php $checked = ($existingProfile && ($existingProfile['hair_color'] ?? '') === $key); ?>
                            <label class="relative flex items-center gap-3.5 p-4 rounded-2xl border-2 border-slate-200 hover:border-hair-300 cursor-pointer transition has-[:checked]:border-hair-600 has-[:checked]:bg-hair-50/50">
                                <input type="radio" name="hair_color" value="<?= $key ?>" class="sr-only" <?= $checked ? 'checked' : '' ?>>
                                <div class="w-8 h-8 rounded-full border border-slate-300 flex-shrink-0 shadow-inner" style="background-color: <?= $color['color'] ?>;"></div>
                                <div class="flex-1">
                                    <h4 class="font-bold text-slate-900 text-sm"><?= e($color['label']) ?></h4>
                                    <p class="text-xs text-slate-500 leading-snug"><?= e($color['desc']) ?></p>
                                </div>
                                <div class="text-hair-600 opacity-0 has-[:checked]:opacity-100 transition">
                                    <i class="fa-solid fa-circle-check"></i>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- ========================================================================= -->
                <!-- ETAPA 2: CURVATURA DO FIO (1A a 4C) -->
                <!-- ========================================================================= -->
                <div id="quiz-step-2" class="space-y-6 hidden">
                    <div class="border-b border-slate-100 pb-4">
                        <span class="text-xs font-bold text-hair-600 uppercase tracking-wider">Passo 2</span>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">Qual é a curvatura do seu cabelo?</h2>
                        <p class="text-slate-500 text-xs sm:text-sm mt-1">Identifique o formato predominante do seu fio da raiz até as pontas.</p>
                    </div>

                    <div class="space-y-6">
                        <!-- Lisos (1A a 1C) -->
                        <div>
                            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 flex items-center gap-2">
                                <i class="fa-solid fa-water"></i> Tipo 1: Cabelos Lisos
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <?php foreach (['1A', '1B', '1C'] as $c): ?>
                                    <?php $curv = $structure['curvatures'][$c]; $checked = ($existingProfile && ($existingProfile['curvature'] ?? '') === $c); ?>
                                    <label class="p-3.5 rounded-2xl border-2 border-slate-200 hover:border-hair-300 cursor-pointer transition has-[:checked]:border-hair-600 has-[:checked]:bg-hair-50/50 block">
                                        <input type="radio" name="curvature" value="<?= $c ?>" class="sr-only" <?= $checked ? 'checked' : '' ?>>
                                        <span class="inline-block px-2 py-0.5 rounded-md bg-slate-100 font-extrabold text-xs text-slate-800 mb-1"><?= $c ?></span>
                                        <h4 class="font-bold text-slate-900 text-sm"><?= e($curv['label']) ?></h4>
                                        <p class="text-[11px] text-slate-500 leading-tight mt-0.5"><?= e($curv['desc']) ?></p>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Ondulados (2A a 2C) -->
                        <div>
                            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 flex items-center gap-2">
                                <i class="fa-solid fa-wind"></i> Tipo 2: Cabelos Ondulados
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <?php foreach (['2A', '2B', '2C'] as $c): ?>
                                    <?php $curv = $structure['curvatures'][$c]; $checked = ($existingProfile && ($existingProfile['curvature'] ?? '') === $c); ?>
                                    <label class="p-3.5 rounded-2xl border-2 border-slate-200 hover:border-hair-300 cursor-pointer transition has-[:checked]:border-hair-600 has-[:checked]:bg-hair-50/50 block">
                                        <input type="radio" name="curvature" value="<?= $c ?>" class="sr-only" <?= $checked ? 'checked' : '' ?>>
                                        <span class="inline-block px-2 py-0.5 rounded-md bg-slate-100 font-extrabold text-xs text-slate-800 mb-1"><?= $c ?></span>
                                        <h4 class="font-bold text-slate-900 text-sm"><?= e($curv['label']) ?></h4>
                                        <p class="text-[11px] text-slate-500 leading-tight mt-0.5"><?= e($curv['desc']) ?></p>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Cacheados (3A a 3C) -->
                        <div>
                            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 flex items-center gap-2">
                                <i class="fa-solid fa-spinner"></i> Tipo 3: Cabelos Cacheados
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <?php foreach (['3A', '3B', '3C'] as $c): ?>
                                    <?php $curv = $structure['curvatures'][$c]; $checked = ($existingProfile && ($existingProfile['curvature'] ?? '') === $c); ?>
                                    <label class="p-3.5 rounded-2xl border-2 border-slate-200 hover:border-hair-300 cursor-pointer transition has-[:checked]:border-hair-600 has-[:checked]:bg-hair-50/50 block">
                                        <input type="radio" name="curvature" value="<?= $c ?>" class="sr-only" <?= $checked ? 'checked' : '' ?>>
                                        <span class="inline-block px-2 py-0.5 rounded-md bg-slate-100 font-extrabold text-xs text-slate-800 mb-1"><?= $c ?></span>
                                        <h4 class="font-bold text-slate-900 text-sm"><?= e($curv['label']) ?></h4>
                                        <p class="text-[11px] text-slate-500 leading-tight mt-0.5"><?= e($curv['desc']) ?></p>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Crespos (4A a 4C) -->
                        <div>
                            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 flex items-center gap-2">
                                <i class="fa-solid fa-certificate"></i> Tipo 4: Cabelos Crespos
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <?php foreach (['4A', '4B', '4C'] as $c): ?>
                                    <?php $curv = $structure['curvatures'][$c]; $checked = ($existingProfile && ($existingProfile['curvature'] ?? '') === $c); ?>
                                    <label class="p-3.5 rounded-2xl border-2 border-slate-200 hover:border-hair-300 cursor-pointer transition has-[:checked]:border-hair-600 has-[:checked]:bg-hair-50/50 block">
                                        <input type="radio" name="curvature" value="<?= $c ?>" class="sr-only" <?= $checked ? 'checked' : '' ?>>
                                        <span class="inline-block px-2 py-0.5 rounded-md bg-slate-100 font-extrabold text-xs text-slate-800 mb-1"><?= $c ?></span>
                                        <h4 class="font-bold text-slate-900 text-sm"><?= e($curv['label']) ?></h4>
                                        <p class="text-[11px] text-slate-500 leading-tight mt-0.5"><?= e($curv['desc']) ?></p>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ========================================================================= -->
                <!-- ETAPA 3: PRINCIPAIS PROBLEMAS CAPILARES -->
                <!-- ========================================================================= -->
                <div id="quiz-step-3" class="space-y-6 hidden">
                    <div class="border-b border-slate-100 pb-4">
                        <span class="text-xs font-bold text-hair-600 uppercase tracking-wider">Passo 3</span>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">Quais problemas mais incomodam seu cabelo?</h2>
                        <p class="text-slate-500 text-xs sm:text-sm mt-1">Você pode selecionar uma ou mais opções para afinarmos o tratamento.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <?php 
                        $selectedProbs = $existingProfile['problems'] ?? [];
                        if (!is_array($selectedProbs)) $selectedProbs = [];
                        ?>
                        <?php foreach ($structure['problems'] as $key => $prob): ?>
                            <?php $isChecked = in_array($key, $selectedProbs); ?>
                            <label class="relative flex items-start gap-3.5 p-4 rounded-2xl border-2 border-slate-200 hover:border-hair-300 cursor-pointer transition has-[:checked]:border-hair-600 has-[:checked]:bg-hair-50/50">
                                <input type="checkbox" name="problems[]" value="<?= $key ?>" class="rounded border-slate-300 text-hair-600 focus:ring-hair-500 mt-1" <?= $isChecked ? 'checked' : '' ?>>
                                <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center flex-shrink-0 text-sm">
                                    <i class="fa-solid <?= $prob['icon'] ?>"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-bold text-slate-900 text-sm"><?= e($prob['label']) ?></h4>
                                    <p class="text-xs text-slate-500 leading-snug mt-0.5"><?= e($prob['desc']) ?></p>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- ========================================================================= -->
                <!-- ETAPA 4: OBJETIVO PRINCIPAL & HÁBITOS -->
                <!-- ========================================================================= -->
                <div id="quiz-step-4" class="space-y-6 hidden">
                    <div class="border-b border-slate-100 pb-4">
                        <span class="text-xs font-bold text-hair-600 uppercase tracking-wider">Passo 4 (Final)</span>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900">Qual é o seu objetivo de tratamento principal?</h2>
                        <p class="text-slate-500 text-xs sm:text-sm mt-1">Para priorizarmos a máscara e finalizador certos no seu cronograma capilar.</p>
                    </div>

                    <!-- Objetivos -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <?php foreach ($structure['goals'] as $key => $goal): ?>
                            <?php $isGoalChecked = ($existingProfile && ($existingProfile['goal'] ?? '') === $key); ?>
                            <label class="p-4 rounded-2xl border-2 border-slate-200 hover:border-hair-300 cursor-pointer transition has-[:checked]:border-hair-600 has-[:checked]:bg-hair-50/50 block">
                                <input type="radio" name="goal" value="<?= $key ?>" class="sr-only" <?= $isGoalChecked ? 'checked' : '' ?>>
                                <h4 class="font-bold text-slate-900 text-sm"><?= e($goal['label']) ?></h4>
                                <p class="text-xs text-slate-500 leading-snug mt-0.5"><?= e($goal['desc']) ?></p>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <!-- Perguntas Complementares -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-slate-100">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Com que frequência você lava os fios?</label>
                            <select name="wash_frequency" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-sm focus:ring-2 focus:ring-hair-500 focus:outline-none">
                                <option value="diaria" <?= ($existingProfile['wash_frequency'] ?? '') === 'diaria' ? 'selected' : '' ?>>Todos os dias</option>
                                <option value="alternada" <?= ($existingProfile['wash_frequency'] ?? '') === 'alternada' || !isset($existingProfile) ? 'selected' : '' ?>>Dia sim, dia não (Alternada)</option>
                                <option value="2x_semana" <?= ($existingProfile['wash_frequency'] ?? '') === '2x_semana' ? 'selected' : '' ?>>2 a 3 vezes por semana</option>
                                <option value="1x_semana" <?= ($existingProfile['wash_frequency'] ?? '') === '1x_semana' ? 'selected' : '' ?>>1 vez por semana</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Seu cabelo possui química ou descoloração?</label>
                            <div class="flex items-center gap-4 pt-2">
                                <label class="flex items-center gap-2 cursor-pointer text-sm font-semibold text-slate-700">
                                    <input type="radio" name="chemical_treated" value="1" <?= !empty($existingProfile['chemical_treated']) ? 'checked' : '' ?> class="text-hair-600 focus:ring-hair-500">
                                    <span>Sim, com química / tintura</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer text-sm font-semibold text-slate-700">
                                    <input type="radio" name="chemical_treated" value="0" <?= empty($existingProfile['chemical_treated']) ? 'checked' : '' ?> class="text-hair-600 focus:ring-hair-500">
                                    <span>Não, 100% natural</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botões de Navegação -->
                <div class="flex items-center justify-between pt-8 border-t border-slate-100 mt-8">
                    <button type="button" id="btn-prev" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 font-semibold text-sm transition flex items-center gap-2 invisible">
                        <i class="fa-solid fa-arrow-left text-xs"></i>
                        <span>Voltar</span>
                    </button>

                    <button type="button" id="btn-next" class="px-7 py-3 rounded-xl bg-hair-600 hover:bg-hair-700 text-white font-bold text-sm shadow-md shadow-hair-600/30 transition flex items-center gap-2">
                        <span>Avançar</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>

                    <button type="submit" id="btn-submit" class="px-8 py-3.5 rounded-xl bg-gradient-to-r from-hair-600 to-hair-800 hover:from-hair-700 hover:to-hair-900 text-white font-extrabold text-sm shadow-lg shadow-hair-600/30 transition flex items-center gap-2 hidden">
                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                        <span>Finalizar Diagnóstico & Ver Produtos</span>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/quiz.js"></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
