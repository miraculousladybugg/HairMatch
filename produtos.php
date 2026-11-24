<?php
/**
 * Vitrine de Produtos Capilares Pós-Questionário - HairMatch
 * Core Requirement: Buscar o produto desejado dentre os selecionados pós questionário
 * Algoritmo Ponderado: Avaliações dos usuários (70% peso) + Fabricante (30% peso)
 */

$pageTitle = 'Vitrine de Produtos Personalizados';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/backend/products.php';
require_once __DIR__ . '/backend/cart.php';

$hairProfile = getCurrentHairProfile();

// Trata adição ao carrinho via POST
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    $productId = (int)($_POST['product_id'] ?? 0);
    $qty = (int)($_POST['quantity'] ?? 1);

    if ($productId > 0) {
        $res = addToCart($productId, $qty);
        setFlash('success', $res['message']);
    }
    // Redireciona mantendo os filtros atuais
    $queryString = $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '';
    header('Location: ' . BASE_URL . '/produtos.php' . $queryString);
    exit;
}

// Filtros recebidos via GET
$search = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');
$brand = trim($_GET['brand'] ?? '');
$minPrice = !empty($_GET['min_price']) ? (float)$_GET['min_price'] : null;
$maxPrice = !empty($_GET['max_price']) ? (float)$_GET['max_price'] : null;
$order = trim($_GET['order'] ?? 'match_desc');

$filters = [
    'search' => $search,
    'category' => $category,
    'brand' => $brand,
    'min_price' => $minPrice,
    'max_price' => $maxPrice,
    'order' => $order
];

$products = getProducts($filters, $hairProfile);
$categories = getAllCategories();
$brands = getAllBrands();

require_once __DIR__ . '/includes/header.php';
?>

<div class="py-8 bg-slate-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Banner de Perfil Capilar (Se o usuário já realizou o questionário) -->
        <?php if (!empty($hairProfile['curvature'])): ?>
            <div class="mb-8 p-6 rounded-3xl bg-gradient-to-r from-hair-800 via-hair-900 to-slate-900 text-white shadow-xl relative overflow-hidden">
                <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-hair-600/30 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                    <div class="space-y-1.5">
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-xs font-bold">
                            <i class="fa-solid fa-check-circle"></i>
                            <span>Filtro de Match Ativado</span>
                        </div>
                        <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight">
                            Produtos ranqueados para o seu cabelo <?= e(strtoupper($hairProfile['curvature'])) ?> (<?= e(ucfirst($hairProfile['hair_type'])) ?>)
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-300 flex flex-wrap items-center gap-x-3 gap-y-1">
                            <span><strong>Cor:</strong> <?= e(ucfirst($hairProfile['hair_color'])) ?></span>
                            <span>•</span>
                            <span><strong>Problemas Foco:</strong> <?= e(implode(', ', array_map('ucfirst', $hairProfile['problems'] ?? []))) ?></span>
                            <span>•</span>
                            <span><strong>Objetivo:</strong> <?= e(ucfirst($hairProfile['goal'] ?? '')) ?></span>
                        </p>
                    </div>

                    <a href="<?= BASE_URL ?>/quiz.php" class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white border border-white/20 text-xs font-bold transition flex items-center gap-2 flex-shrink-0">
                        <i class="fa-solid fa-arrows-rotate"></i>
                        <span>Refazer Teste</span>
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- Chamada para fazer o teste caso ainda não tenha respondido -->
            <div class="mb-8 p-6 rounded-3xl bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-white border border-amber-200/80 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center text-xl font-bold flex-shrink-0 shadow-md shadow-amber-500/20">
                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">Quer ver os produtos ideais para o seu fio?</h3>
                        <p class="text-slate-600 text-xs sm:text-sm">Responda ao questionário de 1 minuto para calcularmos o % de Match personalizado.</p>
                    </div>
                </div>
                <a href="<?= BASE_URL ?>/quiz.php" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold transition flex items-center justify-center gap-2 flex-shrink-0 shadow-sm">
                    <span>Fazer Teste Capilar Agora</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        <?php endif; ?>

        <!-- Layout em Grid: Barra Lateral de Filtros + Vitrine de Produtos -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            
            <!-- Barra Lateral de Filtros (MobileFirst colapsável ou nativo) -->
            <aside class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xs">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
                        <h3 class="font-extrabold text-slate-900 text-base flex items-center gap-2">
                            <i class="fa-solid fa-sliders text-hair-600"></i>
                            <span>Filtros de Busca</span>
                        </h3>
                        <?php if ($search || $category || $brand || $minPrice || $maxPrice): ?>
                            <a href="<?= BASE_URL ?>/produtos.php" class="text-xs font-bold text-rose-600 hover:underline">
                                Limpar
                            </a>
                        <?php endif; ?>
                    </div>

                    <form method="GET" action="<?= BASE_URL ?>/produtos.php" class="space-y-5">
                        
                        <!-- Busca por Palavra-Chave -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Buscar Produto</label>
                            <div class="relative">
                                <input type="text" name="search" value="<?= e($search) ?>" placeholder="Ex: Morte Súbita, Lola, Nutrição..." class="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-hair-500 focus:outline-none">
                                <i class="fa-solid fa-magnifying-glass absolute left-3 top-3.5 text-xs text-slate-400"></i>
                            </div>
                        </div>

                        <!-- Categoria -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Categoria</label>
                            <select name="category" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-sm focus:ring-2 focus:ring-hair-500 focus:outline-none">
                                <option value="">Todas as Categorias</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= e($cat['slug']) ?>" <?= $category === $cat['slug'] ? 'selected' : '' ?>>
                                        <?= e($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Marca -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Marca</label>
                            <select name="brand" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-sm focus:ring-2 focus:ring-hair-500 focus:outline-none">
                                <option value="">Todas as Marcas</option>
                                <?php foreach ($brands as $b): ?>
                                    <option value="<?= e($b) ?>" <?= $brand === $b ? 'selected' : '' ?>>
                                        <?= e($b) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Faixa de Preço -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Faixa de Preço (R$)</label>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="number" step="0.01" name="min_price" value="<?= e($minPrice ?? '') ?>" placeholder="Mín" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-hair-500">
                                <input type="number" step="0.01" name="max_price" value="<?= e($maxPrice ?? '') ?>" placeholder="Máx" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-hair-500">
                            </div>
                        </div>

                        <!-- Ordenação -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Ordenar Por</label>
                            <select name="order" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-sm focus:ring-2 focus:ring-hair-500 focus:outline-none font-semibold text-slate-800">
                                <option value="match_desc" <?= $order === 'match_desc' ? 'selected' : '' ?>>⭐ Maior Match Capilar (Ponderado)</option>
                                <option value="rating_desc" <?= $order === 'rating_desc' ? 'selected' : '' ?>>🏆 Melhor Avaliação das Usuárias</option>
                                <option value="reviews_desc" <?= $order === 'reviews_desc' ? 'selected' : '' ?>>💬 Mais Avaliados</option>
                                <option value="price_asc" <?= $order === 'price_asc' ? 'selected' : '' ?>>💰 Menor Preço</option>
                                <option value="price_desc" <?= $order === 'price_desc' ? 'selected' : '' ?>>💎 Maior Preço</option>
                            </select>
                        </div>

                        <button type="submit" class="w-full py-3 rounded-xl bg-hair-600 hover:bg-hair-700 text-white font-bold text-xs shadow-md shadow-hair-600/20 transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-filter"></i>
                            <span>Aplicar Filtros</span>
                        </button>
                    </form>
                </div>

                <!-- Info Box sobre o Ranqueamento Ponderado -->
                <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-3xl p-5 border border-indigo-100 text-xs text-indigo-900 space-y-2">
                    <div class="flex items-center gap-2 font-bold text-indigo-950">
                        <i class="fa-solid fa-scale-balanced text-indigo-600 text-sm"></i>
                        <span>Como calculamos o Match?</span>
                    </div>
                    <p class="leading-relaxed text-indigo-800">
                        • <strong>70% do peso:</strong> notas e depoimentos de usuárias com perfil capilar compatível.<br>
                        • <strong>30% do peso:</strong> indicações oficiais de curvatura e ativos do fabricante.
                    </p>
                </div>
            </aside>

            <!-- Lista de Produtos / Vitrine Principal -->
            <main class="lg:col-span-3 space-y-6">
                
                <!-- Barra de Status e Contagem -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-4 rounded-2xl border border-slate-200">
                    <p class="text-xs sm:text-sm text-slate-600">
                        Exibindo <span class="font-extrabold text-slate-900"><?= count($products) ?></span> produtos selecionados
                        <?php if ($search): ?>
                            para a busca "<span class="font-semibold text-hair-600"><?= e($search) ?></span>"
                        <?php endif; ?>
                    </p>

                    <div class="flex items-center gap-2 text-xs font-semibold text-slate-500">
                        <i class="fa-solid fa-circle-check text-emerald-500"></i>
                        <span>Ordenação Ponderada Ativa</span>
                    </div>
                </div>

                <?php if (empty($products)): ?>
                    <!-- Nenhum produto encontrado -->
                    <div class="bg-white rounded-3xl p-12 text-center border border-slate-200 space-y-4">
                        <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-2xl mx-auto">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900">Nenhum cosmético encontrado</h3>
                        <p class="text-sm text-slate-500 max-w-md mx-auto">
                            Não encontramos produtos com os critérios de filtro selecionados. Tente ajustar os termos de busca ou remover os filtros de preço.
                        </p>
                        <a href="<?= BASE_URL ?>/produtos.php" class="inline-block px-5 py-2.5 rounded-xl bg-hair-600 text-white font-bold text-xs hover:bg-hair-700 transition">
                            Limpar Filtros e Ver Todos
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Grid de Produtos Responsivo -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($products as $p): ?>
                            <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-xs hover:shadow-lg hover:border-hair-300 transition duration-300 flex flex-col group">
                                
                                <!-- Foto do Produto com Badges -->
                                <div class="relative aspect-square bg-slate-100 overflow-hidden">
                                    <img src="<?= e($p['image_url']) ?>" alt="<?= e($p['name']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                    
                                    <!-- Selo de Match Capilar Ponderado -->
                                    <div class="absolute top-3 left-3 flex flex-col gap-1 items-start">
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-extrabold shadow-sm <?= $p['match_score'] >= 90 ? 'bg-gradient-to-r from-hair-600 to-hair-800 text-white' : ($p['match_score'] >= 80 ? 'bg-indigo-600 text-white' : 'bg-slate-900/80 backdrop-blur-xs text-white') ?>">
                                            <i class="fa-solid fa-sparkles text-[10px]"></i>
                                            <span><?= $p['match_score'] ?>% Match</span>
                                        </span>
                                    </div>

                                    <!-- Nota dos Usuários (Maior Peso) -->
                                    <div class="absolute top-3 right-3">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-extrabold bg-white/95 backdrop-blur-xs text-slate-900 shadow-sm border border-slate-100">
                                            <i class="fa-solid fa-star text-amber-500 text-xs"></i>
                                            <span><?= number_format($p['user_rating_avg'] > 0 ? $p['user_rating_avg'] : $p['manufacturer_rating'], 1) ?></span>
                                            <span class="text-[10px] text-slate-400">(<?= $p['reviews_count'] ?>)</span>
                                        </span>
                                    </div>
                                </div>

                                <!-- Detalhes do Produto -->
                                <div class="p-5 flex-1 flex flex-col space-y-3">
                                    <div>
                                        <div class="flex items-center justify-between text-xs text-slate-400 mb-1">
                                            <span class="font-bold text-hair-600 uppercase tracking-wider"><?= e($p['brand']) ?></span>
                                            <span><?= e($p['category_name']) ?></span>
                                        </div>

                                        <h3 class="font-extrabold text-slate-900 text-base leading-snug group-hover:text-hair-600 transition line-clamp-2">
                                            <a href="<?= BASE_URL ?>/produto.php?id=<?= $p['id'] ?>">
                                                <?= e($p['name']) ?>
                                            </a>
                                        </h3>
                                    </div>

                                    <p class="text-xs text-slate-500 line-clamp-2">
                                        <?= e($p['short_desc']) ?>
                                    </p>

                                    <!-- Motivo do Match (Transparência do Algoritmo) -->
                                    <?php if (!empty($p['match_reasons'])): ?>
                                        <div class="bg-slate-50 rounded-xl p-2.5 border border-slate-100 space-y-1">
                                            <?php foreach (array_slice($p['match_reasons'], 0, 1) as $reason): ?>
                                                <p class="text-[11px] text-slate-600 flex items-center gap-1.5 truncate">
                                                    <i class="fa-solid fa-circle-check text-emerald-500 text-[10px] flex-shrink-0"></i>
                                                    <span class="truncate"><?= e($reason) ?></span>
                                                </p>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Preço e Ações -->
                                    <div class="mt-auto pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                                        <div>
                                            <span class="text-[10px] text-slate-400 font-semibold uppercase">Preço</span>
                                            <p class="text-lg font-extrabold text-slate-900"><?= formatMoney((float)$p['price']) ?></p>
                                        </div>

                                        <div class="flex items-center gap-1.5">
                                            <!-- Formulário Rápido de Adição ao Carrinho -->
                                            <form method="POST" action="<?= BASE_URL ?>/produtos.php<?= $queryString ?? '' ?>">
                                                <input type="hidden" name="action" value="add_to_cart">
                                                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                                <input type="hidden" name="quantity" value="1">
                                                <button type="submit" class="p-2.5 rounded-xl bg-slate-100 hover:bg-hair-50 text-slate-700 hover:text-hair-600 transition" title="Adicionar ao Carrinho">
                                                    <i class="fa-solid fa-cart-plus text-sm"></i>
                                                </button>
                                            </form>

                                            <a href="<?= BASE_URL ?>/produto.php?id=<?= $p['id'] ?>" class="px-3.5 py-2.5 rounded-xl bg-hair-600 hover:bg-hair-700 text-white font-bold text-xs transition shadow-sm">
                                                Ver
                                            </a>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </main>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
