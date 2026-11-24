<?php
/**
 * Detalhes do Produto e Avaliação de Usuários - HairMatch
 * Core Requirement: Avaliar o produto & ver detalhes do fabricante vs opiniões dos usuários
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/backend/products.php';
require_once __DIR__ . '/backend/reviews.php';
require_once __DIR__ . '/backend/cart.php';

$hairProfile = getCurrentHairProfile();

$productId = (int)($_GET['id'] ?? 0);
$product = getProductById($productId, $hairProfile);

if (!$product) {
    setFlash('error', 'Produto não encontrado ou indisponível.');
    header('Location: ' . BASE_URL . '/produtos.php');
    exit;
}

$pageTitle = $product['name'] . ' - ' . $product['brand'];
$user = getCurrentUser();

// Processa envio de Avaliação (Review)
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_review') {
    if (!isLoggedIn()) {
        setFlash('error', 'Você precisa estar logado para avaliar este produto.');
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }

    $rating = (int)($_POST['rating'] ?? 5);
    $title = trim($_POST['title'] ?? '');
    $comment = trim($_POST['comment'] ?? '');
    $recommended = isset($_POST['recommended']) ? (int)$_POST['recommended'] : 1;

    $reviewResult = addProductReview($productId, (int)$user['id'], $rating, $title, $comment, $recommended);

    if ($reviewResult['success']) {
        setFlash('success', $reviewResult['message']);
    } else {
        setFlash('error', $reviewResult['message']);
    }

    header('Location: ' . BASE_URL . '/produto.php?id=' . $productId . '#avaliacoes');
    exit;
}

// Processa adição ao carrinho
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_cart') {
    $qty = max(1, (int)($_POST['quantity'] ?? 1));
    $res = addToCart($productId, $qty);
    setFlash('success', $res['message']);

    if (!empty($_POST['buy_now'])) {
        header('Location: ' . BASE_URL . '/carrinho.php');
        exit;
    }

    header('Location: ' . BASE_URL . '/produto.php?id=' . $productId);
    exit;
}

$reviews = getProductReviews($productId);

require_once __DIR__ . '/includes/header.php';
?>

<div class="py-10 bg-slate-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
        
        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-2 text-xs font-semibold text-slate-500">
            <a href="<?= BASE_URL ?>/index.php" class="hover:text-hair-600 transition">Início</a>
            <span>/</span>
            <a href="<?= BASE_URL ?>/produtos.php" class="hover:text-hair-600 transition">Produtos</a>
            <span>/</span>
            <a href="<?= BASE_URL ?>/produtos.php?category=<?= e($product['category_slug']) ?>" class="hover:text-hair-600 transition"><?= e($product['category_name']) ?></a>
            <span>/</span>
            <span class="text-slate-800 truncate max-w-xs"><?= e($product['name']) ?></span>
        </nav>

        <!-- Bloco Principal do Produto -->
        <div class="bg-white rounded-3xl p-6 sm:p-10 border border-slate-200/80 shadow-sm grid grid-cols-1 lg:grid-cols-12 gap-10">
            
            <!-- Coluna da Foto -->
            <div class="lg:col-span-5 space-y-4">
                <div class="relative aspect-square rounded-2xl overflow-hidden bg-slate-100 border border-slate-100 shadow-inner">
                    <img src="<?= e($product['image_url']) ?>" alt="<?= e($product['name']) ?>" class="w-full h-full object-cover">
                    
                    <!-- Selo de Match no Topo da Imagem -->
                    <div class="absolute top-4 left-4">
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-extrabold shadow-md <?= $product['match_score'] >= 90 ? 'bg-gradient-to-r from-hair-600 to-hair-800 text-white' : 'bg-slate-900 text-white' ?>">
                            <i class="fa-solid fa-sparkles text-amber-400"></i>
                            <span><?= $product['match_score'] ?>% de Match Capilar</span>
                        </span>
                    </div>
                </div>

                <!-- Card de Transparência do Algoritmo -->
                <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200/80 text-xs space-y-2">
                    <div class="flex items-center justify-between font-bold text-slate-900 border-b border-slate-200/60 pb-2">
                        <span>Ponderação do Algoritmo HairMatch</span>
                        <span class="text-hair-600"><?= $product['match_badge'] ?></span>
                    </div>
                    <div class="flex items-center justify-between text-slate-600">
                        <span>Avaliações dos Usuários (Peso 70%):</span>
                        <strong class="text-slate-900"><?= $product['review_score'] ?> / 100</strong>
                    </div>
                    <div class="flex items-center justify-between text-slate-600">
                        <span>Descrição do Fabricante (Peso 30%):</span>
                        <strong class="text-slate-900"><?= $product['mfg_score'] ?> / 100</strong>
                    </div>
                </div>
            </div>

            <!-- Coluna de Informações e Compra -->
            <div class="lg:col-span-7 flex flex-col justify-between space-y-6">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="px-3 py-1 rounded-lg bg-hair-50 text-hair-700 text-xs font-bold uppercase tracking-wider">
                            <?= e($product['brand']) ?>
                        </span>
                        <span class="text-xs text-slate-400 font-medium">Cód: #<?= str_pad($product['id'], 5, '0', STR_PAD_LEFT) ?></span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight leading-snug">
                        <?= e($product['name']) ?>
                    </h1>

                    <!-- Avaliações dos Usuários em Destaque (Regra Core: maior peso) -->
                    <div class="flex items-center gap-3">
                        <div class="flex items-center text-amber-500 text-sm">
                            <?php 
                            $avg = (float)($product['user_rating_avg'] > 0 ? $product['user_rating_avg'] : $product['manufacturer_rating']);
                            for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fa-solid fa-star <?= $i <= round($avg) ? 'text-amber-500' : 'text-slate-200' ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <span class="font-extrabold text-slate-900 text-sm"><?= number_format($avg, 1) ?></span>
                        <a href="#avaliacoes" class="text-xs text-hair-600 hover:underline font-semibold">
                            (<?= count($reviews) ?> <?= count($reviews) === 1 ? 'avaliação' : 'avaliações de usuárias' ?>)
                        </a>
                    </div>

                    <p class="text-sm text-slate-600 leading-relaxed">
                        <?= e($product['short_desc']) ?>
                    </p>

                    <!-- Tags do Fabricante e Curvaturas Alvo -->
                    <div class="space-y-2 pt-2">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Indicação do Fabricante:</span>
                        <div class="flex flex-wrap gap-1.5">
                            <?php foreach (explode(',', $product['target_curvatures']) as $tc): ?>
                                <span class="px-2.5 py-1 rounded-md bg-slate-100 text-slate-700 text-xs font-semibold">
                                    Curvatura <?= e(trim($tc)) ?>
                                </span>
                            <?php endforeach; ?>
                            <?php foreach (explode(',', $product['target_problems']) as $tp): ?>
                                <span class="px-2.5 py-1 rounded-md bg-pink-50 text-pink-700 border border-pink-100 text-xs font-semibold">
                                    Anti-<?= e(ucfirst(trim($tp))) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Preço em Destaque -->
                    <div class="pt-4 border-t border-slate-100">
                        <span class="text-xs text-slate-400 font-semibold uppercase">Preço Especial</span>
                        <div class="flex items-baseline gap-2">
                            <span class="text-3xl sm:text-4xl font-extrabold text-slate-900">
                                <?= formatMoney((float)$product['price']) ?>
                            </span>
                            <span class="text-xs text-slate-500">em até 3x sem juros</span>
                        </div>
                    </div>
                </div>

                <!-- Formulário de Compra e Quantidade -->
                <form method="POST" action="<?= BASE_URL ?>/produto.php?id=<?= $product['id'] ?>" class="space-y-4 pt-4 border-t border-slate-100">
                    <input type="hidden" name="action" value="add_cart">
                    <div class="flex flex-col sm:flex-row items-center gap-3">
                        <div class="flex items-center border border-slate-300 rounded-2xl p-1 bg-white w-full sm:w-auto justify-between sm:justify-start">
                            <button type="button" onclick="const q = document.getElementById('qty-input'); if(q.value > 1) q.value--;" class="w-10 h-10 rounded-xl hover:bg-slate-100 text-slate-600 font-bold transition flex items-center justify-center">
                                -
                            </button>
                            <input type="number" id="qty-input" name="quantity" value="1" min="1" max="10" class="w-12 text-center text-sm font-extrabold border-0 focus:ring-0">
                            <button type="button" onclick="const q = document.getElementById('qty-input'); if(q.value < 10) q.value++;" class="w-10 h-10 rounded-xl hover:bg-slate-100 text-slate-600 font-bold transition flex items-center justify-center">
                                +
                            </button>
                        </div>

                        <button type="submit" name="add_to_cart" value="1" class="w-full sm:flex-1 py-3.5 px-6 rounded-2xl bg-hair-50 hover:bg-hair-100 text-hair-700 font-extrabold text-sm transition flex items-center justify-center gap-2 border border-hair-200">
                            <i class="fa-solid fa-cart-plus"></i>
                            <span>Adicionar ao Carrinho</span>
                        </button>

                        <button type="submit" name="buy_now" value="1" class="w-full sm:flex-1 py-3.5 px-6 rounded-2xl bg-hair-600 hover:bg-hair-700 text-white font-extrabold text-sm shadow-lg shadow-hair-600/30 transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-bag-shopping"></i>
                            <span>Comprar Agora</span>
                        </button>
                    </div>

                    <!-- Garantias de Loja Segura -->
                    <div class="grid grid-cols-3 gap-2 pt-3 text-center text-[11px] text-slate-500 font-medium">
                        <div class="flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-truck text-hair-600"></i>
                            <span>Envio Rápido</span>
                        </div>
                        <div class="flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-shield-halved text-hair-600"></i>
                            <span>100% Original</span>
                        </div>
                        <div class="flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-arrows-rotate text-hair-600"></i>
                            <span>Troca Fácil</span>
                        </div>
                    </div>
                </form>
            </div>

        </div>

        <!-- Seção de Abas: Descrição do Fabricante vs Avaliações dos Usuários -->
        <div id="avaliacoes" class="bg-white rounded-3xl p-6 sm:p-10 border border-slate-200/80 shadow-sm space-y-8">
            
            <!-- Descrição Oficial do Fabricante (Menor Peso no Algoritmo) -->
            <div class="space-y-4 border-b border-slate-100 pb-8">
                <div class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-wider">
                    <i class="fa-solid fa-industry text-hair-600"></i>
                    <span>Informações Técnicas do Fabricante (Peso 30%)</span>
                </div>
                <h3 class="text-xl font-extrabold text-slate-900">Sobre a Fórmula e Ação do Produto</h3>
                <div class="text-slate-600 text-sm leading-relaxed prose max-w-none">
                    <p><?= nl2br(e($product['manufacturer_desc'])) ?></p>
                </div>
            </div>

            <!-- Regra de Negócio CORE: Avaliações dos Usuários (Maior Peso no Algoritmo) -->
            <div class="space-y-6">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 text-xs font-bold text-hair-600 uppercase tracking-wider">
                            <i class="fa-solid fa-star text-amber-500"></i>
                            <span>Opiniões da Comunidade HairMatch (Peso 70%)</span>
                        </div>
                        <h3 class="text-2xl font-extrabold text-slate-900 tracking-tight">
                            Avaliações de Quem Já Testou
                        </h3>
                    </div>

                    <?php if ($user): ?>
                        <a href="#form-avaliacao" class="px-4 py-2.5 rounded-xl bg-hair-600 text-white font-bold text-xs hover:bg-hair-700 transition flex items-center gap-1.5 shadow-sm">
                            <i class="fa-solid fa-pen"></i>
                            <span>Escrever Minha Avaliação</span>
                        </a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/login.php" class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs hover:bg-slate-200 transition flex items-center gap-1.5">
                            <i class="fa-solid fa-right-to-bracket"></i>
                            <span>Faça Login para Avaliar</span>
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Lista de Avaliações -->
                <?php if (empty($reviews)): ?>
                    <div class="p-8 text-center bg-slate-50 rounded-2xl border border-slate-100 space-y-2">
                        <i class="fa-solid fa-comments text-3xl text-slate-300"></i>
                        <h4 class="font-bold text-slate-800 text-sm">Ainda não há avaliações de consumidores para este cosmético</h4>
                        <p class="text-xs text-slate-500">Seja a primeira pessoa a compartilhar o resultado dele no seu cabelo!</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($reviews as $rev): ?>
                            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <h4 class="font-bold text-slate-900 text-sm"><?= e($rev['title']) ?></h4>
                                        <p class="text-xs text-slate-500">
                                            Por <strong><?= e($rev['user_name']) ?></strong>
                                            <?php if (!empty($rev['user_hair_curvature'])): ?>
                                                • <span class="px-2 py-0.5 rounded-md bg-hair-100 text-hair-800 font-semibold text-[10px]">Cabelo <?= e($rev['user_hair_curvature']) ?></span>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <div class="flex items-center text-amber-500 text-xs">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fa-solid fa-star <?= $i <= (int)$rev['rating'] ? 'text-amber-500' : 'text-slate-200' ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>

                                <p class="text-xs text-slate-600 leading-relaxed italic">
                                    "<?= nl2br(e($rev['comment'])) ?>"
                                </p>

                                <?php if (!empty($rev['recommended'])): ?>
                                    <p class="text-[11px] text-emerald-600 font-semibold flex items-center gap-1">
                                        <i class="fa-solid fa-circle-check"></i>
                                        <span>Recomenda este produto para seu tipo de fio</span>
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Formulário de Avaliação (Para usuários logados) -->
                <div id="form-avaliacao" class="pt-8 border-t border-slate-100">
                    <?php if ($user): ?>
                        <div class="bg-slate-50 rounded-2xl p-6 sm:p-8 border border-slate-200 space-y-5">
                            <div>
                                <h4 class="font-extrabold text-slate-900 text-lg">Avalie este produto</h4>
                                <p class="text-xs text-slate-500">Sua avaliação ajuda a calcular a compatibilidade capilar para centenas de outras pessoas.</p>
                            </div>

                            <form method="POST" action="<?= BASE_URL ?>/produto.php?id=<?= $product['id'] ?>" class="space-y-4">
                                <input type="hidden" name="action" value="submit_review">

                                <!-- Seleção de Estrelas Interativa -->
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Sua Nota (1 a 5 Estrelas)</label>
                                    <div class="flex items-center gap-2" id="star-rating-selector">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <label class="cursor-pointer text-2xl text-slate-300 hover:text-amber-400 transition">
                                                <input type="radio" name="rating" value="<?= $i ?>" <?= $i === 5 ? 'checked' : '' ?> class="sr-only star-radio">
                                                <i class="fa-solid fa-star star-icon" data-value="<?= $i ?>"></i>
                                            </label>
                                        <?php endfor; ?>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Título da Avaliação</label>
                                    <input type="text" name="title" required placeholder="Ex: Deixou meus cachos macios e sem frizz!" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-hair-500 focus:outline-none">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Comentário Detalhado</label>
                                    <textarea name="comment" rows="3" required placeholder="Conte como foi o resultado no seu cabelo, como você aplicou e o que mais gostou..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-hair-500 focus:outline-none"></textarea>
                                </div>

                                <div class="flex items-center gap-2">
                                    <input type="checkbox" name="recommended" value="1" id="rec-check" checked class="rounded border-slate-300 text-hair-600 focus:ring-hair-500">
                                    <label for="rec-check" class="text-xs font-semibold text-slate-700 cursor-pointer">
                                        Eu recomendaria este produto para quem tem o meu tipo de cabelo
                                    </label>
                                </div>

                                <button type="submit" class="px-6 py-3 rounded-xl bg-hair-600 hover:bg-hair-700 text-white font-bold text-xs shadow-md shadow-hair-600/30 transition flex items-center gap-2">
                                    <i class="fa-solid fa-paper-plane"></i>
                                    <span>Publicar Avaliação</span>
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="bg-hair-50/60 rounded-2xl p-6 border border-hair-200 text-center space-y-3">
                            <h4 class="font-bold text-hair-900 text-base">Já usou este cosmético?</h4>
                            <p class="text-xs text-hair-800 max-w-md mx-auto">
                                Faça login na sua conta HairMatch para deixar uma avaliação e nos ajudar a enriquecer o diagnóstico da comunidade.
                            </p>
                            <a href="<?= BASE_URL ?>/login.php" class="inline-block px-5 py-2.5 rounded-xl bg-hair-600 text-white font-bold text-xs hover:bg-hair-700 transition">
                                Fazer Login
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

        </div>

    </div>
</div>

<script>
// Lógica visual da seleção de estrelas
document.addEventListener('DOMContentLoaded', function() {
    const selector = document.getElementById('star-rating-selector');
    if (!selector) return;

    const stars = selector.querySelectorAll('.star-icon');
    const radios = selector.querySelectorAll('.star-radio');

    function highlightStars(val) {
        stars.forEach(star => {
            const starVal = parseInt(star.getAttribute('data-value'));
            if (starVal <= val) {
                star.classList.remove('text-slate-300');
                star.classList.add('text-amber-500');
            } else {
                star.classList.remove('text-amber-500');
                star.classList.add('text-slate-300');
            }
        });
    }

    radios.forEach(radio => {
        radio.addEventListener('change', () => highlightStars(parseInt(radio.value)));
    });

    highlightStars(5);
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
