<?php
/**
 * Barra de Navegação Responsiva - HairMatch
 * MobileFirst com menu gaveta e status do perfil capilar
 */
$currentScript = basename($_SERVER['PHP_SELF'] ?? '');
?>
<header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200/80 shadow-xs">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 sm:h-20">
            
            <!-- Logo HairMatch -->
            <div class="flex items-center gap-3">
                <a href="<?= BASE_URL ?>/index.php" class="flex items-center gap-2.5 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-hair-600 to-hair-400 flex items-center justify-center text-white shadow-md shadow-hair-500/20 group-hover:scale-105 transition">
                        <i class="fa-solid fa-wand-magic-sparkles text-lg"></i>
                    </div>
                    <div>
                        <span class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900 group-hover:text-hair-600 transition">
                            Hair<span class="text-hair-600">Match</span>
                        </span>
                        <span class="hidden sm:block text-[10px] font-semibold text-hair-600 uppercase tracking-widest -mt-1">
                            Diagnóstico Capilar
                        </span>
                    </div>
                </a>
            </div>

            <!-- Links de Navegação Desktop -->
            <nav class="hidden md:flex items-center gap-1 lg:gap-2">
                <a href="<?= BASE_URL ?>/index.php" class="px-3 py-2 rounded-lg text-sm font-semibold transition <?= $currentScript === 'index.php' ? 'text-hair-600 bg-hair-50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' ?>">
                    Início
                </a>

                <a href="<?= BASE_URL ?>/quiz.php" class="relative px-3.5 py-2 rounded-lg text-sm font-bold transition flex items-center gap-1.5 <?= $currentScript === 'quiz.php' ? 'text-white bg-hair-600 shadow-md shadow-hair-600/30' : 'text-hair-700 bg-hair-50 hover:bg-hair-100' ?>">
                    <i class="fa-solid fa-clipboard-question text-xs"></i>
                    <span>Fazer Teste Capilar</span>
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-400 text-amber-950">Novo</span>
                </a>

                <a href="<?= BASE_URL ?>/produtos.php" class="px-3 py-2 rounded-lg text-sm font-semibold transition <?= $currentScript === 'produtos.php' ? 'text-hair-600 bg-hair-50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' ?>">
                    Vitrine de Produtos
                </a>

                <a href="<?= BASE_URL ?>/index.php#como-funciona" class="px-3 py-2 rounded-lg text-sm font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition">
                    Como Funciona
                </a>
            </nav>

            <!-- Ações do Topo: Perfil Capilar + Carrinho + Conta -->
            <div class="flex items-center gap-2 sm:gap-3">
                
                <!-- Badge de Perfil Capilar Concluído -->
                <?php if (!empty($hairProfile['curvature'])): ?>
                    <a href="<?= BASE_URL ?>/produtos.php" title="Ver produtos recomendados para o seu perfil" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold hover:bg-emerald-100 transition">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Seu Fio: <?= e(strtoupper($hairProfile['curvature'])) ?> (<?= e(ucfirst($hairProfile['hair_type'])) ?>)</span>
                    </a>
                <?php endif; ?>

                <!-- Carrinho -->
                <a href="<?= BASE_URL ?>/carrinho.php" class="relative p-2.5 rounded-xl text-slate-700 hover:bg-slate-100 hover:text-hair-600 transition flex items-center justify-center" aria-label="Carrinho de Compras">
                    <i class="fa-solid fa-bag-shopping text-lg"></i>
                    <?php if ($cartCount > 0): ?>
                        <span class="absolute -top-1 -right-1 bg-hair-600 text-white font-bold text-[10px] w-5 h-5 rounded-full flex items-center justify-center border-2 border-white shadow-sm">
                            <?= $cartCount ?>
                        </span>
                    <?php endif; ?>
                </a>

                <!-- Autenticação Desktop -->
                <?php if ($user): ?>
                    <div class="relative" id="user-menu-dropdown-container">
                        <button type="button" id="user-menu-button" class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-slate-100 transition" aria-expanded="false">
                            <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-hair-500 to-hair-700 text-white font-bold text-sm flex items-center justify-center shadow-xs">
                                <?= strtoupper(substr($user['name'], 0, 1)) ?>
                            </div>
                            <span class="hidden lg:block text-sm font-semibold text-slate-800 max-w-[120px] truncate">
                                <?= e(explode(' ', $user['name'])[0]) ?>
                            </span>
                            <i class="fa-solid fa-chevron-down text-xs text-slate-400"></i>
                        </button>

                        <!-- Dropdown Menu -->
                        <div id="user-menu-dropdown" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-slate-100 py-2 z-50 animate-fade-in">
                            <div class="px-4 py-2 border-b border-slate-100">
                                <p class="text-xs text-slate-400">Logado como</p>
                                <p class="text-sm font-bold text-slate-900 truncate"><?= e($user['name']) ?></p>
                                <p class="text-xs text-slate-500 truncate"><?= e($user['email']) ?></p>
                            </div>

                            <a href="<?= BASE_URL ?>/perfil.php" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 hover:bg-hair-50 hover:text-hair-700 font-medium transition">
                                <i class="fa-solid fa-id-badge text-slate-400"></i>
                                Meu Diagnóstico Capilar
                            </a>

                            <a href="<?= BASE_URL ?>/quiz.php" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 hover:bg-hair-50 hover:text-hair-700 font-medium transition">
                                <i class="fa-solid fa-arrows-rotate text-slate-400"></i>
                                Refazer Questionário
                            </a>

                            <a href="<?= BASE_URL ?>/carrinho.php" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-slate-700 hover:bg-hair-50 hover:text-hair-700 font-medium transition">
                                <i class="fa-solid fa-receipt text-slate-400"></i>
                                Meus Pedidos
                            </a>

                            <div class="border-t border-slate-100 my-1"></div>

                            <a href="<?= BASE_URL ?>/logout.php" data-confirm="Tem certeza de que deseja encerrar sua sessão?" data-confirm-title="Encerrar Sessão" data-confirm-btn="Sim, Sair" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-rose-600 hover:bg-rose-50 font-medium transition">
                                <i class="fa-solid fa-right-from-bracket"></i>
                                Sair da Conta
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="hidden sm:flex items-center gap-2">
                        <a href="<?= BASE_URL ?>/login.php" class="px-3.5 py-2 rounded-xl text-sm font-semibold text-slate-700 hover:text-hair-600 hover:bg-slate-100 transition">
                            Entrar
                        </a>
                        <a href="<?= BASE_URL ?>/cadastro.php" class="px-4 py-2 rounded-xl text-sm font-bold text-white bg-hair-600 hover:bg-hair-700 shadow-sm shadow-hair-600/30 transition">
                            Cadastre-se
                        </a>
                    </div>
                <?php endif; ?>

                <!-- Botão Menu Mobile Hamburguer -->
                <button type="button" id="mobile-menu-btn" class="md:hidden p-2 rounded-xl text-slate-700 hover:bg-slate-100 transition" aria-label="Abrir Menu">
                    <i class="fa-solid fa-bars text-xl" id="mobile-menu-icon"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Menu Mobile Gaveta -->
    <div id="mobile-menu" class="hidden md:hidden border-t border-slate-200 bg-white px-4 pt-3 pb-6 space-y-3">
        <?php if (!empty($hairProfile['curvature'])): ?>
            <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center justify-between text-xs text-emerald-900 font-semibold">
                <span>💇 Perfil: <?= strtoupper($hairProfile['curvature']) ?> (<?= ucfirst($hairProfile['hair_type']) ?>)</span>
                <a href="<?= BASE_URL ?>/produtos.php" class="text-emerald-700 underline font-bold">Ver Match</a>
            </div>
        <?php endif; ?>

        <div class="space-y-1">
            <a href="<?= BASE_URL ?>/index.php" class="block px-3 py-2.5 rounded-xl font-semibold text-sm <?= $currentScript === 'index.php' ? 'text-hair-600 bg-hair-50' : 'text-slate-700 hover:bg-slate-100' ?>">
                <i class="fa-solid fa-house mr-2 text-slate-400"></i> Início
            </a>
            <a href="<?= BASE_URL ?>/quiz.php" class="block px-3 py-2.5 rounded-xl font-bold text-sm bg-hair-600 text-white shadow-xs">
                <i class="fa-solid fa-clipboard-question mr-2"></i> Fazer Teste Capilar
            </a>
            <a href="<?= BASE_URL ?>/produtos.php" class="block px-3 py-2.5 rounded-xl font-semibold text-sm <?= $currentScript === 'produtos.php' ? 'text-hair-600 bg-hair-50' : 'text-slate-700 hover:bg-slate-100' ?>">
                <i class="fa-solid fa-store mr-2 text-slate-400"></i> Vitrine de Produtos
            </a>
            <a href="<?= BASE_URL ?>/carrinho.php" class="block px-3 py-2.5 rounded-xl font-semibold text-sm text-slate-700 hover:bg-slate-100">
                <i class="fa-solid fa-bag-shopping mr-2 text-slate-400"></i> Carrinho de Compras (<?= $cartCount ?>)
            </a>
        </div>

        <div class="border-t border-slate-200 pt-3">
            <?php if ($user): ?>
                <div class="space-y-1">
                    <div class="px-3 py-2 text-xs font-bold text-slate-400 uppercase">Minha Conta</div>
                    <a href="<?= BASE_URL ?>/perfil.php" class="block px-3 py-2 rounded-xl text-sm font-semibold text-slate-800 hover:bg-slate-100">
                        <i class="fa-solid fa-user mr-2 text-slate-400"></i> Meu Perfil Capilar
                    </a>
                    <a href="<?= BASE_URL ?>/logout.php" data-confirm="Deseja realmente sair da sua conta?" data-confirm-btn="Sim, Sair" class="block px-3 py-2 rounded-xl text-sm font-semibold text-rose-600 hover:bg-rose-50">
                        <i class="fa-solid fa-right-from-bracket mr-2"></i> Sair
                    </a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-2 gap-2 pt-1">
                    <a href="<?= BASE_URL ?>/login.php" class="w-full text-center py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold text-sm hover:bg-slate-100">
                        Entrar
                    </a>
                    <a href="<?= BASE_URL ?>/cadastro.php" class="w-full text-center py-2.5 rounded-xl bg-hair-600 text-white font-semibold text-sm hover:bg-hair-700">
                        Cadastre-se
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</header>

<script>
// Toggle do Menu Mobile
document.addEventListener('DOMContentLoaded', function() {
    const mobileBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    const mobileIcon = document.getElementById('mobile-menu-icon');

    if (mobileBtn && mobileMenu) {
        mobileBtn.addEventListener('click', function() {
            const isHidden = mobileMenu.classList.contains('hidden');
            if (isHidden) {
                mobileMenu.classList.remove('hidden');
                mobileIcon.classList.replace('fa-bars', 'fa-xmark');
            } else {
                mobileMenu.classList.add('hidden');
                mobileIcon.classList.replace('fa-xmark', 'fa-bars');
            }
        });
    }

    // Toggle do Dropdown de Usuário
    const userBtn = document.getElementById('user-menu-button');
    const userDropdown = document.getElementById('user-menu-dropdown');

    if (userBtn && userDropdown) {
        userBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdown.classList.toggle('hidden');
        });

        document.addEventListener('click', function(e) {
            if (!userDropdown.contains(e.target) && !userBtn.contains(e.target)) {
                userDropdown.classList.add('hidden');
            }
        });
    }
});
</script>
