<?php
/**
 * Painel de Perfil e Diagnóstico Capilar do Usuário - HairMatch
 */

$pageTitle = 'Meu Diagnóstico & Perfil';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/backend/quiz.php';
require_once __DIR__ . '/backend/reviews.php';

requireAuth();

$user = getCurrentUser();
$profile = getUserHairProfile((int)$user['id']);

$pdo = getDBConnection();

// Busca resenhas postadas por este usuário
$stmtMyReviews = $pdo->prepare("
    SELECT r.*, p.name as product_name, p.brand as product_brand, p.image_url as product_image
    FROM reviews r
    JOIN products p ON r.product_id = p.id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC
");
$stmtMyReviews->execute([$user['id']]);
$myReviews = $stmtMyReviews->fetchAll();

// Busca pedidos recentes
$stmtOrders = $pdo->prepare("
    SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC
");
$stmtOrders->execute([$user['id']]);
$myOrders = $stmtOrders->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="py-10 bg-slate-50 min-h-screen">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        
        <!-- Header do Perfil -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-4 text-center sm:text-left">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-hair-600 to-hair-400 text-white font-extrabold text-2xl flex items-center justify-center shadow-md shadow-hair-500/20 flex-shrink-0">
                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                </div>
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-900"><?= e($user['name']) ?></h1>
                    <p class="text-xs sm:text-sm text-slate-500"><?= e($user['email']) ?> • Membro HairMatch</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="<?= BASE_URL ?>/quiz.php" class="px-4 py-2.5 rounded-xl bg-hair-50 text-hair-700 hover:bg-hair-100 font-bold text-xs transition border border-hair-200 flex items-center gap-2">
                    <i class="fa-solid fa-arrows-rotate"></i>
                    <span>Refazer Questionário</span>
                </a>

                <a href="<?= BASE_URL ?>/logout.php" data-confirm="Deseja realmente sair da sua conta?" data-confirm-btn="Sim, Sair" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-600 font-semibold text-xs transition flex items-center gap-1.5">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Sair</span>
                </a>
            </div>
        </div>

        <!-- Card de Diagnóstico Capilar -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-slate-100 pb-5">
                <div>
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-hair-100 text-hair-800 text-xs font-bold uppercase tracking-wider mb-1">
                        <i class="fa-solid fa-sparkles text-hair-600"></i>
                        <span>Diagnóstico Ativo</span>
                    </div>
                    <h2 class="text-xl font-extrabold text-slate-900">Seu Perfil Capilar Oficial</h2>
                </div>

                <a href="<?= BASE_URL ?>/produtos.php" class="px-5 py-2.5 rounded-xl bg-hair-600 hover:bg-hair-700 text-white font-bold text-xs shadow-md shadow-hair-600/30 transition flex items-center gap-2">
                    <i class="fa-solid fa-bag-shopping"></i>
                    <span>Ver Produtos com Seu Match</span>
                </a>
            </div>

            <?php if (!$profile): ?>
                <div class="p-8 text-center bg-slate-50 rounded-2xl border border-slate-100 space-y-3">
                    <p class="text-sm text-slate-600">Você ainda não respondeu ao questionário capilar.</p>
                    <a href="<?= BASE_URL ?>/quiz.php" class="inline-block px-5 py-2.5 rounded-xl bg-hair-600 text-white font-bold text-xs hover:bg-hair-700 transition">
                        Responder Agora (Leva 1 minuto)
                    </a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Curvatura & Tipo</span>
                        <p class="text-lg font-extrabold text-slate-900"><?= e(strtoupper($profile['curvature'])) ?> (<?= e(ucfirst($profile['hair_type'])) ?>)</p>
                        <p class="text-[11px] text-slate-500">Padrão do cacho / fio</p>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Cor do Cabelo</span>
                        <p class="text-lg font-extrabold text-slate-900"><?= e(ucfirst($profile['hair_color'])) ?></p>
                        <p class="text-[11px] text-slate-500">Filtro de proteção de pigmento</p>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Objetivo Principal</span>
                        <p class="text-lg font-extrabold text-slate-900"><?= e(ucfirst($profile['goal'])) ?></p>
                        <p class="text-[11px] text-slate-500">Diretriz do cronograma</p>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Química / Tratado</span>
                        <p class="text-lg font-extrabold text-slate-900"><?= !empty($profile['chemical_treated']) ? 'Sim' : 'Natural' ?></p>
                        <p class="text-[11px] text-slate-500"><?= e($profile['wash_frequency'] ?? 'Alternada') ?></p>
                    </div>

                </div>

                <div>
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-2">Desafios Capilares Identificados:</span>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach (($profile['problems'] ?? []) as $prob): ?>
                            <span class="px-3 py-1.5 rounded-xl bg-pink-50 text-pink-700 border border-pink-200 text-xs font-bold flex items-center gap-1.5">
                                <i class="fa-solid fa-circle-check text-[10px]"></i>
                                <span><?= e(ucfirst($prob)) ?></span>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Seção: Minhas Avaliações Publicadas -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-6">
            <h2 class="text-xl font-extrabold text-slate-900">Minhas Avaliações de Produtos</h2>

            <?php if (empty($myReviews)): ?>
                <p class="text-xs text-slate-500">Você ainda não avaliou nenhum produto. Experimente um produto da vitrine e compartilhe seu relato!</p>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($myReviews as $rev): ?>
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 flex items-start gap-3.5">
                            <img src="<?= e($rev['product_image']) ?>" alt="<?= e($rev['product_name']) ?>" class="w-14 h-14 rounded-xl object-cover bg-slate-100 border border-slate-200 flex-shrink-0">
                            <div class="flex-1 space-y-1">
                                <span class="text-[10px] font-bold text-hair-600 uppercase"><?= e($rev['product_brand']) ?></span>
                                <h4 class="font-bold text-slate-900 text-xs line-clamp-1"><?= e($rev['product_name']) ?></h4>
                                <div class="flex items-center text-amber-500 text-xs">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fa-solid fa-star <?= $i <= (int)$rev['rating'] ? 'text-amber-500' : 'text-slate-200' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <p class="text-xs text-slate-600 italic">"<?= e($rev['comment']) ?>"</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Seção: Histórico de Pedidos / Compras -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs space-y-4">
            <h2 class="text-xl font-extrabold text-slate-900">Histórico de Pedidos</h2>
            <?php if (empty($myOrders)): ?>
                <p class="text-xs text-slate-500">Você ainda não realizou compras. Selecione os produtos do seu match e finalize seu primeiro pedido!</p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($myOrders as $ord): ?>
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between text-xs">
                            <div>
                                <span class="font-bold text-slate-900">Pedido #<?= str_pad($ord['id'], 5, '0', STR_PAD_LEFT) ?></span>
                                <span class="text-slate-400 ml-2"><?= date('d/m/Y H:i', strtotime($ord['created_at'])) ?></span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold uppercase text-[10px]">
                                    <?= e(ucfirst($ord['status'])) ?>
                                </span>
                                <strong class="text-slate-900 font-extrabold text-sm"><?= formatMoney((float)$ord['total_amount']) ?></strong>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
