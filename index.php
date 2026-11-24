<?php
/**
 * Página Inicial (Landing Page & Vitrine de Entrada) - HairMatch
 */

$pageTitle = 'Diagnóstico Capilar Inteligente & Vitrine Personalizada';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/backend/products.php';

// Busca os 6 produtos com melhor avaliação para a seção de destaque
$featuredProducts = getProducts(['order' => 'rating_desc'], $hairProfile);
$topProducts = array_slice($featuredProducts, 0, 6);
?>

<!-- Hero Section -->
<section class="relative overflow-hidden bg-gradient-to-b from-hair-50/70 via-white to-slate-50 py-16 sm:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            
            <!-- Coluna de Texto Hero -->
            <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-hair-100/80 border border-hair-200 text-hair-800 text-xs font-bold tracking-wide">
                    <i class="fa-solid fa-sparkles text-hair-600"></i>
                    <span>Diagnóstico Personalizado de Fios</span>
                </div>

                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-slate-900 tracking-tight leading-[1.15]">
                    O produto perfeito para a sua 
                    <span class="text-gradient">curvatura e cor</span>.
                </h1>

                <p class="text-lg sm:text-xl text-slate-600 max-w-2xl leading-relaxed">
                    Chega de gastar com produtos que não funcionam para o seu cabelo. O <strong>HairMatch</strong> cruza <strong>avaliações reais de consumidores</strong> com a indicação dos fabricantes para ranquear o que realmente dá resultado nos seus fios.
                </p>

                <!-- Ações Principais -->
                <div class="flex flex-col sm:flex-row items-center gap-4 pt-2 justify-center lg:justify-start">
                    <a href="<?= BASE_URL ?>/quiz.php" class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-hair-600 hover:bg-hair-700 text-white font-bold text-base shadow-lg shadow-hair-600/30 hover:scale-[1.02] active:scale-[0.98] transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-clipboard-question"></i>
                        <span>Fazer Diagnóstico Capilar</span>
                    </a>

                    <a href="<?= BASE_URL ?>/produtos.php" class="w-full sm:w-auto px-7 py-4 rounded-2xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 font-semibold text-base transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-store text-slate-500"></i>
                        <span>Explorar Vitrine</span>
                    </a>
                </div>

                <!-- Estatísticas de Confiança -->
                <div class="pt-6 border-t border-slate-200/80 grid grid-cols-3 gap-4 text-center sm:text-left">
                    <div>
                        <p class="text-2xl sm:text-3xl font-extrabold text-slate-900">12</p>
                        <p class="text-xs text-slate-500 font-medium">Curvaturas Capilares (1A a 4C)</p>
                    </div>
                    <div>
                        <p class="text-2xl sm:text-3xl font-extrabold text-slate-900">70%</p>
                        <p class="text-xs text-slate-500 font-medium">Peso em Avaliações Reais</p>
                    </div>
                    <div>
                        <p class="text-2xl sm:text-3xl font-extrabold text-slate-900">100%</p>
                        <p class="text-xs text-slate-500 font-medium">Recomendações Personalizadas</p>
                    </div>
                </div>
            </div>

            <!-- Coluna de Imagem / Card Visual de Destaque -->
            <div class="lg:col-span-5 relative flex justify-center">
                <div class="relative w-full max-w-md">
                    <!-- Glow decorativo -->
                    <div class="absolute -inset-2 bg-gradient-to-r from-hair-400 to-amber-400 rounded-3xl blur-2xl opacity-30"></div>
                    
                    <div class="relative bg-white rounded-3xl p-6 shadow-2xl border border-slate-100 space-y-5">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-2xl bg-hair-100 text-hair-700 flex items-center justify-center text-xl font-bold">
                                    3B
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-900 text-sm">Cabelo Cacheado & Castanho</h4>
                                    <p class="text-xs text-slate-500">Foco: Ressecamento & Frizz</p>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold">
                                98% Match
                            </span>
                        </div>

                        <div class="relative rounded-2xl overflow-hidden aspect-video bg-slate-100">
                            <img src="https://images.unsplash.com/photo-1608248597359-4573138ec301?auto=format&fit=crop&w=600&q=80" alt="Máscara Morte Súbita" class="w-full h-full object-cover">
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-hair-700 uppercase tracking-wider">Lola Cosmetics</span>
                                <div class="flex items-center gap-1 text-amber-500 text-xs font-bold">
                                    <i class="fa-solid fa-star"></i>
                                    <span>5.0 (3 avaliações)</span>
                                </div>
                            </div>
                            <h3 class="font-extrabold text-slate-900 text-base leading-snug">
                                Máscara Morte Súbita Hidratação Total
                            </h3>
                            <p class="text-xs text-slate-500 line-clamp-2">
                                Fórmula com complexo lipídico, extrato de aloe vera e óleo de coco para reposição hídrica profunda.
                            </p>
                        </div>

                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100 text-xs text-slate-600 flex items-start gap-2">
                            <i class="fa-solid fa-quote-left text-hair-400 mt-0.5"></i>
                            <span>"Salvou meu cabelo 3B na primeira aplicação! Zero frizz e super hidratado." — <strong>Maria S.</strong></span>
                        </div>

                        <a href="<?= BASE_URL ?>/quiz.php" class="block w-full py-3 text-center rounded-xl bg-slate-900 hover:bg-black text-white font-bold text-xs transition">
                            Descobrir o Match do Meu Cabelo &rarr;
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Seção: Como Funciona o Algoritmo HairMatch -->
<section id="como-funciona" class="py-16 sm:py-20 bg-white border-y border-slate-200/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
            <span class="text-hair-600 text-xs font-extrabold tracking-wider uppercase">Metodologia Ponderada</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                Como encontramos o cosmético ideal para você
            </h2>
            <p class="text-slate-600 text-base">
                Eliminamos o desperdício com recomendações que colocam a experiência real de quem usa em primeiro lugar.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            
            <!-- Passo 1 -->
            <div class="bg-slate-50 rounded-2xl p-6 border border-slate-100 hover:border-hair-200 transition group">
                <div class="w-12 h-12 rounded-xl bg-hair-100 text-hair-600 flex items-center justify-center text-xl font-bold mb-4 group-hover:scale-110 transition">
                    1
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">Questionário Rápido</h3>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Você indica a curvatura exata (1A a 4C), cor, química e os principais problemas dos seus fios.
                </p>
            </div>

            <!-- Passo 2 -->
            <div class="bg-slate-50 rounded-2xl p-6 border border-slate-100 hover:border-hair-200 transition group">
                <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-xl font-bold mb-4 group-hover:scale-110 transition">
                    2
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">Avaliações têm Peso Maior</h3>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Atribuímos <strong>70% de peso</strong> às notas e relatos de consumidores com perfil capilar idêntico ao seu.
                </p>
            </div>

            <!-- Passo 3 -->
            <div class="bg-slate-50 rounded-2xl p-6 border border-slate-100 hover:border-hair-200 transition group">
                <div class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center text-xl font-bold mb-4 group-hover:scale-110 transition">
                    3
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">Descrição do Fabricante</h3>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Atribuímos <strong>30% de peso</strong> aos ativos, fórmula e indicações laboratoriais do fabricante no rótulo.
                </p>
            </div>

            <!-- Passo 4 -->
            <div class="bg-slate-50 rounded-2xl p-6 border border-slate-100 hover:border-hair-200 transition group">
                <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xl font-bold mb-4 group-hover:scale-110 transition">
                    4
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-2">Vitrine Sob Medida</h3>
                <p class="text-sm text-slate-600 leading-relaxed">
                    Você acessa produtos ranqueados pelo % de match, compra com segurança e depois avalia para ajudar outros.
                </p>
            </div>

        </div>
    </div>
</section>

<!-- Seção: Vitrine de Destaques -->
<section class="py-16 sm:py-20 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row items-start sm:items-end justify-between gap-4 mb-12">
            <div>
                <span class="text-hair-600 text-xs font-extrabold tracking-wider uppercase">Mais Queridinhos</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Produtos com Melhores Avaliações
                </h2>
                <p class="text-slate-500 text-sm mt-1">
                    Cosméticos consagrados pelas usuárias com notas máximas e resultados comprovados.
                </p>
            </div>
            <a href="<?= BASE_URL ?>/produtos.php" class="text-sm font-bold text-hair-600 hover:text-hair-700 flex items-center gap-1 transition">
                <span>Ver todos os produtos</span>
                <i class="fa-solid fa-arrow-right text-xs"></i>
            </a>
        </div>

        <!-- Grid de Produtos -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($topProducts as $p): ?>
                <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs hover:shadow-md hover:border-hair-300 transition flex flex-col group">
                    <div class="relative aspect-square bg-slate-100 overflow-hidden">
                        <img src="<?= e($p['image_url']) ?>" alt="<?= e($p['name']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        
                        <div class="absolute top-3 left-3">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-white/90 backdrop-blur-xs text-hair-700 shadow-xs">
                                <?= e($p['match_badge']) ?> (<?= $p['match_score'] ?>% Match)
                            </span>
                        </div>

                        <div class="absolute top-3 right-3">
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-bold bg-slate-900/80 backdrop-blur-xs text-white">
                                <i class="fa-solid fa-star text-amber-400 text-[10px]"></i>
                                <span><?= number_format($p['user_rating_avg'] > 0 ? $p['user_rating_avg'] : $p['manufacturer_rating'], 1) ?></span>
                            </span>
                        </div>
                    </div>

                    <div class="p-5 flex-1 flex flex-col">
                        <div class="flex items-center justify-between text-xs text-slate-500 mb-1.5">
                            <span class="font-bold text-hair-600 uppercase tracking-wider"><?= e($p['brand']) ?></span>
                            <span><?= e($p['category_name']) ?></span>
                        </div>

                        <h3 class="font-bold text-slate-900 text-base mb-2 group-hover:text-hair-600 transition leading-snug line-clamp-2">
                            <?= e($p['name']) ?>
                        </h3>

                        <p class="text-xs text-slate-500 mb-4 line-clamp-2">
                            <?= e($p['short_desc']) ?>
                        </p>

                        <div class="mt-auto pt-4 border-t border-slate-100 flex items-center justify-between">
                            <div>
                                <span class="text-xs text-slate-400">Preço</span>
                                <p class="text-lg font-extrabold text-slate-900"><?= formatMoney((float)$p['price']) ?></p>
                            </div>

                            <a href="<?= BASE_URL ?>/produto.php?id=<?= $p['id'] ?>" class="px-4 py-2 rounded-xl bg-hair-50 text-hair-700 hover:bg-hair-600 hover:text-white font-bold text-xs transition">
                                Ver Detalhes
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Banner de Chamada para o Questionário -->
<section class="py-16 bg-hair-gradient text-white relative overflow-hidden">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-6 relative z-10">
        <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight">
            Descubra agora os produtos com o maior match capilar para você!
        </h2>
        <p class="text-hair-100 text-base sm:text-lg max-w-2xl mx-auto leading-relaxed">
            Leva menos de 2 minutos. Responda o questionário e veja na vitrine produtos testados e aprovados para a sua curvatura.
        </p>
        <div>
            <a href="<?= BASE_URL ?>/quiz.php" class="inline-flex items-center gap-2 px-8 py-4 rounded-2xl bg-white text-hair-700 hover:bg-hair-50 font-extrabold text-base shadow-xl hover:scale-105 active:scale-95 transition">
                <i class="fa-solid fa-sparkles text-amber-500"></i>
                <span>Começar Questionário Capilar</span>
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
