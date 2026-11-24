<?php
/**
 * Backend do Carrinho de Compras e Checkout - HairMatch
 * Gerenciamento do carrinho em sessão nativa e registro de pedidos
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once __DIR__ . '/products.php';

/**
 * Inicializa a estrutura do carrinho na sessão
 */
function initCart(): void {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

/**
 * Adiciona um produto ao carrinho
 */
function addToCart(int $productId, int $quantity = 1): array {
    initCart();
    $quantity = max(1, $quantity);

    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] += $quantity;
    } else {
        $_SESSION['cart'][$productId] = $quantity;
    }

    $product = getProductById($productId);
    $name = $product ? $product['name'] : 'Produto';

    return [
        'success' => true,
        'message' => "{$name} foi adicionado ao seu carrinho!",
        'total_items' => getCartTotalItemCount()
    ];
}

/**
 * Atualiza quantidade de um produto
 */
function updateCartQuantity(int $productId, int $quantity): array {
    initCart();
    if ($quantity <= 0) {
        unset($_SESSION['cart'][$productId]);
    } else {
        $_SESSION['cart'][$productId] = $quantity;
    }

    return [
        'success' => true,
        'message' => 'Carrinho atualizado com sucesso.',
        'total_items' => getCartTotalItemCount()
    ];
}

/**
 * Remove produto do carrinho
 */
function removeFromCart(int $productId): array {
    initCart();
    unset($_SESSION['cart'][$productId]);
    return [
        'success' => true,
        'message' => 'Produto removido do carrinho.',
        'total_items' => getCartTotalItemCount()
    ];
}

/**
 * Retorna contagem total de itens no carrinho
 */
function getCartTotalItemCount(): int {
    initCart();
    return array_sum($_SESSION['cart']);
}

/**
 * Retorna lista detalhada com produtos, preços e totais
 */
function getCartDetails(): array {
    initCart();
    $items = [];
    $subtotal = 0.0;

    foreach ($_SESSION['cart'] as $productId => $qty) {
        $product = getProductById((int)$productId);
        if ($product) {
            $itemSubtotal = $product['price'] * $qty;
            $subtotal += $itemSubtotal;
            $items[] = [
                'product' => $product,
                'quantity' => $qty,
                'subtotal' => $itemSubtotal
            ];
        }
    }

    $shipping = $subtotal > 150 || $subtotal === 0.0 ? 0.0 : 18.90;
    $total = $subtotal + $shipping;

    return [
        'items' => $items,
        'subtotal' => $subtotal,
        'shipping' => $shipping,
        'total' => $total,
        'item_count' => array_sum($_SESSION['cart'])
    ];
}

/**
 * Limpa o carrinho
 */
function clearCart(): void {
    $_SESSION['cart'] = [];
}

/**
 * Finaliza a compra criando o pedido no banco de dados via PDO
 */
function checkoutCart(int $userId, string $paymentMethod = 'Cartão de Crédito'): array {
    $cart = getCartDetails();
    if (empty($cart['items'])) {
        return ['success' => false, 'message' => 'Seu carrinho está vazio.'];
    }

    $pdo = getDBConnection();
    try {
        $pdo->beginTransaction();

        $stmtOrder = $pdo->prepare("
            INSERT INTO orders (user_id, total_amount, payment_method, status) 
            VALUES (?, ?, ?, 'concluido')
        ");
        $stmtOrder->execute([$userId, $cart['total'], $paymentMethod]);
        $orderId = (int)$pdo->lastInsertId();

        $stmtItem = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, unit_price) 
            VALUES (?, ?, ?, ?)
        ");
        foreach ($cart['items'] as $item) {
            $stmtItem->execute([
                $orderId,
                $item['product']['id'],
                $item['quantity'],
                $item['product']['price']
            ]);
        }

        $pdo->commit();
        clearCart();

        return [
            'success' => true,
            'message' => "Pedido #{$orderId} realizado com sucesso! Seus produtos ideais logo estarão a caminho.",
            'order_id' => $orderId
        ];
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return ['success' => false, 'message' => 'Erro ao processar pedido: ' . $e->getMessage()];
    }
}
