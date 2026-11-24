<?php
/**
 * Footer Global - HairMatch
 * Rodapé responsivo com links, selos de confiança e newsletter
 */
?>
</main>

<footer class="bg-slate-900 text-slate-300 border-t border-slate-800 mt-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-12">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10 mb-12">
            
            <!-- Coluna 1 & 2: Sobre HairMatch -->
            <div class="lg:col-span-2 space-y-4">
                <a href="<?= BASE_URL ?>/index.php" class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-hair-600 to-hair-400 flex items-center justify-center text-white shadow-md">
                        <i class="fa-solid fa-wand-magic-sparkles text-lg"></i>
                    </div>
                    <span class="text-2xl font-extrabold tracking-tight text-white">
                        Hair<span class="text-hair-400">Match</span>
                    </span>
                </a>
                <p class="text-slate-400 text-sm leading-relaxed max-w-sm">
                    Revolucionando o cuidado capilar com inteligência e comunidade. Recomendamos os produtos ideais com base em avaliações de quem tem o mesmo tipo de cabelo que você.
                </p>

                <!-- Selos de Confiança -->
                <div class="flex flex-wrap gap-3 pt-2">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-slate-800 text-xs text-slate-300 border border-slate-700">
                        <i class="fa-solid fa-shield-check text-emerald-400"></i>
                        <span>Avaliações Verificadas</span>
                    </div>
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-slate-800 text-xs text-slate-300 border border-slate-700">
                        <i class="fa-solid fa-leaf text-emerald-400"></i>
                        <span>Cruelty Free & Vegano</span>
                    </div>
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-slate-800 text-xs text-slate-300 border border-slate-700">
                        <i class="fa-solid fa-lock text-emerald-400"></i>
                        <span>Ambiente 100% Seguro</span>
                    </div>
                </div>
            </div>

            <!-- Coluna 3: Navegação Rápida -->
            <div>
                <h4 class="text-white font-bold text-sm tracking-wider uppercase mb-4">Navegação</h4>
                <ul class="space-y-2.5 text-sm">
                    <li><a href="<?= BASE_URL ?>/index.php" class="hover:text-hair-400 transition">Início</a></li>
                    <li><a href="<?= BASE_URL ?>/quiz.php" class="hover:text-hair-400 font-semibold text-hair-400 transition">Fazer Teste Capilar</a></li>
                    <li><a href="<?= BASE_URL ?>/produtos.php" class="hover:text-hair-400 transition">Vitrine Completa</a></li>
                    <li><a href="<?= BASE_URL ?>/carrinho.php" class="hover:text-hair-400 transition">Meu Carrinho</a></li>
                    <li><a href="<?= BASE_URL ?>/perfil.php" class="hover:text-hair-400 transition">Meu Diagnóstico</a></li>
                </ul>
            </div>

            <!-- Coluna 4: Categorias Populares -->
            <div>
                <h4 class="text-white font-bold text-sm tracking-wider uppercase mb-4">Categorias</h4>
                <ul class="space-y-2.5 text-sm">
                    <li><a href="<?= BASE_URL ?>/produtos.php?category=mascaras" class="hover:text-hair-400 transition">Máscaras de Tratamento</a></li>
                    <li><a href="<?= BASE_URL ?>/produtos.php?category=shampoos" class="hover:text-hair-400 transition">Shampoos Especializados</a></li>
                    <li><a href="<?= BASE_URL ?>/produtos.php?category=condicionadores" class="hover:text-hair-400 transition">Condicionadores Selantes</a></li>
                    <li><a href="<?= BASE_URL ?>/produtos.php?category=finalizadores" class="hover:text-hair-400 transition">Cremes & Finalizadores</a></li>
                    <li><a href="<?= BASE_URL ?>/produtos.php?category=oleos-seruns" class="hover:text-hair-400 transition">Óleos & Séruns Reparadores</a></li>
                </ul>
            </div>

            <!-- Coluna 5: Atendimento e Suporte -->
            <div>
                <h4 class="text-white font-bold text-sm tracking-wider uppercase mb-4">Atendimento</h4>
                <p class="text-xs text-slate-400 mb-3">Dúvidas sobre o cronograma ou seu perfil capilar?</p>
                <div class="space-y-2 text-sm">
                    <p class="flex items-center gap-2">
                        <i class="fa-solid fa-envelope text-hair-400"></i>
                        <span>contato@hairmatch.com</span>
                    </p>
                    <p class="flex items-center gap-2">
                        <i class="fa-solid fa-phone text-hair-400"></i>
                        <span>0800 777 4247</span>
                    </p>
                    <p class="flex items-center gap-2">
                        <i class="fa-solid fa-clock text-hair-400"></i>
                        <span>Seg a Sex: 09h às 18h</span>
                    </p>
                </div>
            </div>

        </div>

        <!-- Linha Divisória e Copyright -->
        <div class="border-t border-slate-800 pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-400">
            <p>&copy; <?= date('Y') ?> HairMatch Plataforma Online Capilar. Todos os direitos reservados.</p>
            <p class="flex items-center gap-1">
                Construído com <i class="fa-solid fa-heart text-rose-500"></i> para a beleza e saúde de todos os cabelos.
            </p>
        </div>
    </div>
</footer>

</body>
</html>
