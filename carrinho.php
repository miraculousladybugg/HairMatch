<?php
/**
 * Carrinho de Compras e Checkout Simulado - HairMatch
 * Core Requirement: Loja virtual dos produtos ideais
 */

$pageTitle = 'Meu Carrinho de Compras';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/backend/cart.php';

$user = getCurrentUser();

// Ações no carrinho
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $action = $_POST['action'] ?? '';
    $productId = (int)($_POST['product_id'] ?? 0);

    if ($action === 'update_qty') {
        $qty = (int)($_POST['quantity'] ?? 1);
        updateCartQuantity($productId, $qty);
    } elseif ($action === 'remove_item') {
        removeFromCart($productId);
        setFlash('info', 'Item removido do seu carrinho.');
    } elseif ($action === 'clear') {
        clearCart();
        setFlash('info', 'Seu carrinho foi esvaziado.');
    } elseif ($action === 'checkout') {
        if (!isLoggedIn()) {
            setFlash('warning', 'Faça login ou crie uma conta rápida para concluir seu pedido.');
            header('Location: ' . BASE_URL . '/login.php');
            exit;
        }

        $paymentMethod = $_POST['payment_method'] ?? 'Cartão de Crédito';
        $res = checkoutCart((int)$user['id'], $paymentMethod);

        if ($res['success']) {
            setFlash('success', $res['message']);
            header('Location: ' . BASE_URL . '/perfil.php');
            exit;
        } else {
            setFlash('error', $res['message']);
        }
    }

    header('Location: ' . BASE_URL . '/carrinho.php');
    exit;
}

$cart = getCartDetails();

require_once __DIR__ . '/includes/header.php';
?>

<div class="py-10 bg-slate-50 min-h-screen">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        
        <div class="flex items-center justify-between border-b border-slate-200/80 pb-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Carrinho de Compras
                </h1>
                <p class="text-xs sm:text-sm text-slate-500">
                    Seus cosméticos selecionados para o seu cronograma capilar.
                </p>
            </div>

            <?php if (!empty($cart['items'])): ?>
                <form method="POST" action="<?= BASE_URL ?>/carrinho.php">
                    <input type="hidden" name="action" value="clear">
                    <button type="submit" data-confirm="Deseja realmente esvaziar todos os itens do carrinho?" data-confirm-title="Esvaziar Carrinho" class="text-xs font-bold text-rose-600 hover:text-rose-700 transition">
                        <i class="fa-solid fa-trash-can mr-1"></i> Esvaziar Carrinho
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <?php if (empty($cart['items'])): ?>
            <!-- Carrinho Vazio -->
            <div class="bg-white rounded-3xl p-12 text-center border border-slate-200 space-y-4">
                <div class="w-20 h-20 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center text-3xl mx-auto">
                    <i class="fa-solid fa-bag-shopping"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-900">Seu carrinho está vazio</h3>
                <p class="text-sm text-slate-500 max-w-sm mx-auto">
                    Faça o diagnóstico capilar para encontrar os produtos ideais para o seu fio e adicione-os aqui!
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
                    <a href="<?= BASE_URL ?>/quiz.php" class="px-6 py-3 rounded-xl bg-hair-600 hover:bg-hair-700 text-white font-bold text-xs transition shadow-sm">
                        Fazer Teste Capilar
                    </a>
                    <a href="<?= BASE_URL ?>/produtos.php" class="px-6 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                        Ver Todos os Produtos
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                <!-- Lista de Itens do Carrinho -->
                <div class="lg:col-span-8 space-y-4">
                    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xs divide-y divide-slate-100">
                        <?php foreach ($cart['items'] as $item): ?>
                            <?php $p = $item['product']; ?>
                            <div class="py-5 first:pt-0 last:pb-0 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                                
                                <div class="flex items-center gap-4">
                                    <img src="<?= e($p['image_url']) ?>" alt="<?= e($p['name']) ?>" class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-cover bg-slate-100 border border-slate-200 flex-shrink-0">
                                    
                                    <div class="space-y-1">
                                        <span class="text-[10px] font-bold text-hair-600 uppercase tracking-wider"><?= e($p['brand']) ?></span>
                                        <h4 class="font-extrabold text-slate-900 text-sm leading-snug line-clamp-1">
                                            <a href="<?= BASE_URL ?>/produto.php?id=<?= $p['id'] ?>" class="hover:text-hair-600 transition">
                                                <?= e($p['name']) ?>
                                            </a>
                                        </h4>
                                        <p class="text-xs text-slate-500"><?= formatMoney((float)$p['price']) ?> / un</p>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between sm:justify-end gap-6 w-full sm:w-auto pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                                    
                                    <!-- Controles de Quantidade -->
                                    <form method="POST" action="<?= BASE_URL ?>/carrinho.php" class="flex items-center border border-slate-300 rounded-xl bg-slate-50 p-0.5">
                                        <input type="hidden" name="action" value="update_qty">
                                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                        <button type="submit" name="quantity" value="<?= $item['quantity'] - 1 ?>" class="w-8 h-8 rounded-lg hover:bg-white text-slate-600 font-bold text-xs transition flex items-center justify-center">
                                            -
                                        </button>
                                        <span class="w-8 text-center text-xs font-bold text-slate-800"><?= $item['quantity'] ?></span>
                                        <button type="submit" name="quantity" value="<?= $item['quantity'] + 1 ?>" class="w-8 h-8 rounded-lg hover:bg-white text-slate-600 font-bold text-xs transition flex items-center justify-center">
                                            +
                                        </button>
                                    </form>

                                    <!-- Subtotal do Item -->
                                    <div class="text-right">
                                        <p class="text-base font-extrabold text-slate-900"><?= formatMoney((float)$item['subtotal']) ?></p>
                                    </div>

                                    <!-- Botão Remover -->
                                    <form method="POST" action="<?= BASE_URL ?>/carrinho.php">
                                        <input type="hidden" name="action" value="remove_item">
                                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                        <button type="submit" data-confirm="Deseja remover <?= e($p['name']) ?> do seu carrinho?" data-confirm-title="Remover Item" class="p-2 text-slate-400 hover:text-rose-600 transition" title="Remover item">
                                            <i class="fa-solid fa-trash-can text-sm"></i>
                                        </button>
                                    </form>

                                </div>

                            </div>
                        <?php endforeach; ?>
                    </div>

                    <a href="<?= BASE_URL ?>/produtos.php" class="inline-flex items-center gap-2 text-xs font-bold text-hair-600 hover:text-hair-700 transition">
                        <i class="fa-solid fa-arrow-left text-[10px]"></i>
                        <span>Continuar Comprando</span>
                    </a>
                </div>

                <!-- Resumo do Pedido e Checkout -->
                <div class="lg:col-span-4 space-y-6">
                    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xs space-y-5">
                        <h3 class="font-extrabold text-slate-900 text-lg border-b border-slate-100 pb-3">Resumo do Pedido</h3>

                        <div class="space-y-3 text-sm text-slate-600">
                            <div class="flex items-center justify-between">
                                <span>Subtotal dos produtos</span>
                                <span class="font-bold text-slate-900"><?= formatMoney((float)$cart['subtotal']) ?></span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span>Frete Estimado</span>
                                <span class="font-bold <?= $cart['shipping'] == 0 ? 'text-emerald-600' : 'text-slate-900' ?>">
                                    <?= $cart['shipping'] == 0 ? 'GRÁTIS' : formatMoney((float)$cart['shipping']) ?>
                                </span>
                            </div>

                            <?php if ($cart['shipping'] > 0): ?>
                                <p class="text-[11px] text-emerald-700 bg-emerald-50 p-2 rounded-xl border border-emerald-200">
                                    💡 Falta apenas <?= formatMoney(150 - $cart['subtotal']) ?> para <strong>Frete Grátis</strong>!
                                </p>
                            <?php endif; ?>

                            <div class="border-t border-slate-100 pt-3 flex items-baseline justify-between text-base">
                                <span class="font-extrabold text-slate-900">Total do Pedido</span>
                                <span class="text-2xl font-extrabold text-hair-600"><?= formatMoney((float)$cart['total']) ?></span>
                            </div>
                        </div>

                        <!-- Forma de Pagamento e Finalização -->
                        <form method="POST" action="<?= BASE_URL ?>/carrinho.php" class="space-y-4 pt-2">
                            <input type="hidden" name="action" value="checkout">
                            
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Forma de Pagamento</label>
                                <select name="payment_method" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-xs font-semibold focus:ring-2 focus:ring-hair-500">
                                    <option value="Cartão de Crédito">Cartão de Crédito (em até 3x)</option>
                                    <option value="PIX Instantâneo">PIX com 5% de desconto</option>
                                    <option value="Boleto Bancário">Boleto Bancário</option>
                                </select>
                            </div>

                            <button type="submit" class="w-full py-4 rounded-2xl bg-hair-600 hover:bg-hair-700 text-white font-extrabold text-sm shadow-lg shadow-hair-600/30 hover:scale-[1.02] active:scale-[0.98] transition flex items-center justify-center gap-2">
                                <i class="fa-solid fa-lock"></i>
                                <span><?= $user ? 'Concluir Pedido com Segurança' : 'Fazer Login e Concluir Pedido' ?></span>
                            </button>
                        </form>
                    </div>

                    <!-- Selos de Segurança -->
                    <div class="bg-slate-100/80 rounded-2xl p-4 text-center text-xs text-slate-500 space-y-2">
                        <div class="flex items-center justify-center gap-3 text-slate-400 text-lg">
                            <i class="fa-brands fa-cc-visa"></i>
                            <i class="fa-brands fa-cc-mastercard"></i>
                            <i class="fa-brands fa-pix"></i>
                            <i class="fa-solid fa-barcode"></i>
                        </div>
                        <p class="text-[11px]">Pagamento processado com criptografia SSL 256 bits.</p>
                    </div>

                </div>

            </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
