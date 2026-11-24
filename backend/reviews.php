<?php
/**
 * Backend de Avaliações (Reviews) - HairMatch
 * Submissão de avaliação, listagem e recálculo dinâmico da nota média com maior peso
 */

require_once dirname(__DIR__) . '/config/config.php';

/**
 * Adiciona uma nova avaliação ao produto
 */
function addProductReview(int $productId, int $userId, int $rating, string $title, string $comment, int $recommended = 1): array {
    $title = trim($title);
    $comment = trim($comment);
    $rating = max(1, min(5, $rating));

    if (empty($title) || mb_strlen($title) < 3) {
        return ['success' => false, 'message' => 'Por favor, insira um título para sua avaliação (mínimo 3 caracteres).'];
    }

    if (empty($comment) || mb_strlen($comment) < 10) {
        return ['success' => false, 'message' => 'Por favor, escreva um comentário com no mínimo 10 caracteres contando sua experiência com o produto.'];
    }

    $pdo = getDBConnection();

    // Obtém o perfil capilar do avaliador para enriquecer a resenha
    $stmtProfile = $pdo->prepare("SELECT curvature, hair_color FROM hair_profiles WHERE user_id = ? ORDER BY id DESC LIMIT 1");
    $stmtProfile->execute([$userId]);
    $profile = $stmtProfile->fetch();

    $userCurvature = $profile['curvature'] ?? null;
    $userColor = $profile['hair_color'] ?? null;

    // Insere a avaliação via PDO
    $stmtInsert = $pdo->prepare("
        INSERT INTO reviews 
        (product_id, user_id, rating, title, comment, user_hair_curvature, user_hair_color, recommended) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmtInsert->execute([
        $productId,
        $userId,
        $rating,
        $title,
        $comment,
        $userCurvature,
        $userColor,
        $recommended ? 1 : 0
    ]);

    // Recalcula atomicamente a média e contagem de avaliações do produto
    $calcStmt = $pdo->prepare("
        SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews 
        FROM reviews 
        WHERE product_id = ?
    ");
    $calcStmt->execute([$productId]);
    $stats = $calcStmt->fetch();

    $newAvg = round((float)($stats['avg_rating'] ?? 0), 2);
    $newCount = (int)($stats['total_reviews'] ?? 0);

    $updStmt = $pdo->prepare("
        UPDATE products 
        SET user_rating_avg = ?, reviews_count = ? 
        WHERE id = ?
    ");
    $updStmt->execute([$newAvg, $newCount, $productId]);

    return [
        'success' => true,
        'message' => 'Avaliação publicada com sucesso! Obrigado por ajudar a comunidade HairMatch.',
        'new_avg' => $newAvg,
        'new_count' => $newCount
    ];
}

/**
 * Busca todas as avaliações de um produto
 */
function getProductReviews(int $productId): array {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        SELECT r.*, u.name as user_name
        FROM reviews r
        JOIN users u ON r.user_id = u.id
        WHERE r.product_id = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$productId]);
    return $stmt->fetchAll();
}

/**
 * Verifica se o usuário já avaliou este produto recentemente
 */
function hasUserReviewedProduct(int $productId, int $userId): bool {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT id FROM reviews WHERE product_id = ? AND user_id = ? LIMIT 1");
    $stmt->execute([$productId, $userId]);
    return (bool)$stmt->fetch();
}
