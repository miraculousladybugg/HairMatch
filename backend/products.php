<?php
/**
 * Backend de Produtos e Algoritmo de Ranqueamento Ponderado - HairMatch
 * 
 * Regra de Negócio:
 * - Produtos mostrados pelas avaliações dos usuários (PESO MAIOR: 70%)
 * - E descrição/especificação do fabricante (PESO MENOR: 30%)
 * - Busca de produtos selecionados pós-questionário
 */

require_once dirname(__DIR__) . '/config/config.php';

/**
 * Calcula a pontuação de compatibilidade capilar ponderada entre um produto e o perfil capilar
 * @param array $product Dados do produto
 * @param array|null $profile Perfil capilar do usuário
 * @return array ['match_score' => int, 'review_score' => int, 'mfg_score' => int, 'badge' => string, 'reasons' => array]
 */
function calculateProductMatch(array $product, ?array $profile): array {
    // Se o usuário ainda não respondeu o questionário, exibe pontuação baseada na avaliação geral
    if (!$profile || empty($profile['curvature'])) {
        $avg = (float)($product['user_rating_avg'] > 0 ? $product['user_rating_avg'] : $product['manufacturer_rating']);
        $score = round(($avg / 5.0) * 100);
        return [
            'match_score' => (int)$score,
            'review_score' => (int)$score,
            'mfg_score' => (int)round(($product['manufacturer_rating'] / 5.0) * 100),
            'badge' => $score >= 90 ? 'Destaque' : 'Popular',
            'reasons' => ['Avaliado com nota ' . number_format($avg, 1) . ' / 5.0 pelos consumidores']
        ];
    }

    $reasons = [];

    // =========================================================================
    // 1. COMPONENTE DAS AVALIAÇÕES DOS USUÁRIOS (PESO MAIOR = 70%)
    // =========================================================================
    $userAvg = (float)$product['user_rating_avg'];
    $reviewsCount = (int)$product['reviews_count'];

    if ($reviewsCount > 0 && $userAvg > 0) {
        $reviewBase = ($userAvg / 5.0) * 100;
        // Bônus pela quantidade de avaliações (prova social sólida)
        $popularityBonus = min(5, $reviewsCount * 1.5);
        $reviewScore = min(100, $reviewBase + $popularityBonus);
        $reasons[] = "Média de {$userAvg}★ baseada em {$reviewsCount} avaliações da comunidade";
    } else {
        // Se ainda não tem avaliações de usuários, usa a nota base do fabricante com ponderação menor
        $reviewScore = ($product['manufacturer_rating'] / 5.0) * 85;
        $reasons[] = "Produto recém-chegado com nota laboratorial de {$product['manufacturer_rating']}★";
    }

    // =========================================================================
    // 2. COMPONENTE DA DESCRIÇÃO DO FABRICANTE (PESO MENOR = 30%)
    // =========================================================================
    $targetCurvatures = array_map('trim', explode(',', strtoupper($product['target_curvatures'] ?? '')));
    $targetColors = array_map('trim', explode(',', strtolower($product['target_colors'] ?? '')));
    $targetProblems = array_map('trim', explode(',', strtolower($product['target_problems'] ?? '')));

    $userCurvature = strtoupper($profile['curvature'] ?? '');
    $userColor = strtolower($profile['hair_color'] ?? '');
    $userProblems = is_array($profile['problems']) ? $profile['problems'] : [];

    // Match de Curvatura (40% do fabricante)
    $curvScore = 30; // base mínima
    if (in_array('ALL', $targetCurvatures) || in_array($userCurvature, $targetCurvatures)) {
        $curvScore = 100;
        $reasons[] = "Indicado oficialmente pelo fabricante para curvatura {$userCurvature}";
    } else {
        // Mesmo grupo de curvatura (ex: 3B vs 3A/3C)
        $userGroup = substr($userCurvature, 0, 1);
        $hasGroupMatch = false;
        foreach ($targetCurvatures as $tc) {
            if (str_starts_with($tc, $userGroup)) {
                $hasGroupMatch = true;
                break;
            }
        }
        if ($hasGroupMatch) {
            $curvScore = 75;
            $reasons[] = "Compatível com a família de fios {$profile['hair_type']}";
        }
    }

    // Match de Problemas Capilares (45% do fabricante)
    $probScore = 30;
    if (!empty($userProblems)) {
        $matchedProblems = [];
        foreach ($userProblems as $up) {
            $upClean = strtolower(trim($up));
            if (in_array($upClean, $targetProblems)) {
                $matchedProblems[] = $upClean;
            }
        }
        $matchRatio = count($userProblems) > 0 ? (count($matchedProblems) / count($userProblems)) : 0;
        if (count($matchedProblems) > 0) {
            $probScore = min(100, 50 + ($matchRatio * 50));
            $reasons[] = "Combate especificamente: " . implode(', ', array_slice($matchedProblems, 0, 2));
        }
    } else {
        $probScore = 80;
    }

    // Match de Cor (15% do fabricante)
    $colorScore = 50;
    if (in_array('all', $targetColors) || in_array($userColor, $targetColors)) {
        $colorScore = 100;
        if (!in_array('all', $targetColors)) {
            $reasons[] = "Fórmula otimizada para cabelos {$userColor}";
        }
    }

    $mfgScore = ($curvScore * 0.40) + ($probScore * 0.45) + ($colorScore * 0.15);

    // =========================================================================
    // 3. PONTUAÇÃO FINAL PONDERADA (70% Usuários + 30% Fabricante)
    // =========================================================================
    $finalScore = round(($reviewScore * 0.70) + ($mfgScore * 0.30));
    $finalScore = max(10, min(99, $finalScore)); // limita entre 10% e 99%

    // Determina o selo / badge
    $badge = 'Boa Escolha';
    if ($finalScore >= 92) {
        $badge = 'Match Perfeito ⭐';
    } elseif ($finalScore >= 85) {
        $badge = 'Altamente Recomendado ✨';
    } elseif ($finalScore >= 75) {
        $badge = 'Ótimo Match 👍';
    }

    return [
        'match_score' => (int)$finalScore,
        'review_score' => (int)round($reviewScore),
        'mfg_score' => (int)round($mfgScore),
        'badge' => $badge,
        'reasons' => array_slice($reasons, 0, 3)
    ];
}

/**
 * Busca produtos filtrados e ordenados pelo algoritmo ponderado
 */
function getProducts(array $filters = [], ?array $userProfile = null): array {
    $pdo = getDBConnection();

    $query = "
        SELECT p.*, c.name AS category_name, c.slug AS category_slug, c.icon AS category_icon
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.in_stock = 1
    ";
    $params = [];

    // Filtro por termo de busca
    if (!empty($filters['search'])) {
        $search = '%' . trim($filters['search']) . '%';
        $query .= " AND (p.name LIKE ? OR p.brand LIKE ? OR p.short_desc LIKE ? OR p.manufacturer_desc LIKE ?)";
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
    }

    // Filtro por categoria
    if (!empty($filters['category'])) {
        $query .= " AND (c.slug = ? OR c.id = ?)";
        $params[] = $filters['category'];
        $params[] = $filters['category'];
    }

    // Filtro por marca
    if (!empty($filters['brand'])) {
        $query .= " AND p.brand = ?";
        $params[] = $filters['brand'];
    }

    // Filtro por preço mínimo e máximo
    if (!empty($filters['min_price'])) {
        $query .= " AND p.price >= ?";
        $params[] = (float)$filters['min_price'];
    }
    if (!empty($filters['max_price'])) {
        $query .= " AND p.price <= ?";
        $params[] = (float)$filters['max_price'];
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    // Calcula os matches para cada produto
    foreach ($products as &$prod) {
        $matchData = calculateProductMatch($prod, $userProfile);
        $prod['match_score'] = $matchData['match_score'];
        $prod['review_score'] = $matchData['review_score'];
        $prod['mfg_score'] = $matchData['mfg_score'];
        $prod['match_badge'] = $matchData['badge'];
        $prod['match_reasons'] = $matchData['reasons'];
    }
    unset($prod);

    // Ordenação
    $order = $filters['order'] ?? 'match_desc';
    usort($products, function ($a, $b) use ($order) {
        return match ($order) {
            'match_desc' => $b['match_score'] <=> $a['match_score'],
            'rating_desc' => $b['user_rating_avg'] <=> $a['user_rating_avg'],
            'reviews_desc' => $b['reviews_count'] <=> $a['reviews_count'],
            'price_asc' => $a['price'] <=> $b['price'],
            'price_desc' => $b['price'] <=> $a['price'],
            'name_asc' => strcasecmp($a['name'], $b['name']),
            default => $b['match_score'] <=> $a['match_score']
        };
    });

    return $products;
}

/**
 * Busca produto por ID com dados enriquecidos
 */
function getProductById(int $productId, ?array $userProfile = null): ?array {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("
        SELECT p.*, c.name AS category_name, c.slug AS category_slug, c.icon AS category_icon
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.id = ?
        LIMIT 1
    ");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        return null;
    }

    $matchData = calculateProductMatch($product, $userProfile);
    $product['match_score'] = $matchData['match_score'];
    $product['review_score'] = $matchData['review_score'];
    $product['mfg_score'] = $matchData['mfg_score'];
    $product['match_badge'] = $matchData['badge'];
    $product['match_reasons'] = $matchData['reasons'];

    return $product;
}

/**
 * Retorna todas as categorias disponíveis
 */
function getAllCategories(): array {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    return $stmt->fetchAll();
}

/**
 * Retorna todas as marcas de produtos distintas
 */
function getAllBrands(): array {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT DISTINCT brand FROM products ORDER BY brand ASC");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}
