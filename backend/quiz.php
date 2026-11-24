<?php
/**
 * Backend do Questionário Capilar - HairMatch
 * Salva e recupera o perfil capilar do usuário (Cor, Curvatura, Problema e Objetivo)
 */

require_once dirname(__DIR__) . '/config/config.php';

/**
 * Retorna as opções estruturadas do questionário
 */
function getQuizStructure(): array {
    return [
        'colors' => [
            'castanho' => ['label' => 'Castanho / Moreno', 'desc' => 'Tons claros, médios ou escuros naturais', 'icon' => 'fa-circle', 'color' => '#5c3818'],
            'preto' => ['label' => 'Preto', 'desc' => 'Preto natural ou tingido com alta pigmentação', 'icon' => 'fa-circle', 'color' => '#18181b'],
            'loiro' => ['label' => 'Loiro / Dourado', 'desc' => 'Loiro natural, com mechas, luzes ou platinado', 'icon' => 'fa-circle', 'color' => '#facc15'],
            'ruivo' => ['label' => 'Ruivo / Acobreado', 'desc' => 'Ruivo natural, acobreado ou tons avermelhados', 'icon' => 'fa-circle', 'color' => '#ea580c'],
            'grisalho' => ['label' => 'Grisalho / Branco', 'desc' => 'Fios prateados, brancos ou em transição', 'icon' => 'fa-circle', 'color' => '#94a3b8'],
            'colorido' => ['label' => 'Colorido Fantasia', 'desc' => 'Tons fantasia (rosa, azul, verde, roxo)', 'icon' => 'fa-circle', 'color' => '#db2777'],
        ],
        'curvatures' => [
            '1A' => ['type' => 'liso', 'label' => '1A - Liso Fino', 'desc' => 'Super alinhado, fino e sem nenhuma ondulação'],
            '1B' => ['type' => 'liso', 'label' => '1B - Liso Médio', 'desc' => 'Com um pouco mais de corpo e volume natural'],
            '1C' => ['type' => 'liso', 'label' => '1C - Liso Encorpado', 'desc' => 'Fios grossos, pesados e difíceis de modelar'],
            '2A' => ['type' => 'ondulado', 'label' => '2A - Ondulado Suave', 'desc' => 'Raiz lisa com ondas bem suaves nas pontas'],
            '2B' => ['type' => 'ondulado', 'label' => '2B - Ondulado Médio', 'desc' => 'Ondas em formato "S" mais marcadas e tendência ao frizz'],
            '2C' => ['type' => 'ondulado', 'label' => '2C - Ondulado Marcado', 'desc' => 'Ondas muito definidas e início de cachos abertos'],
            '3A' => ['type' => 'cacheado', 'label' => '3A - Cacheado Aberto', 'desc' => 'Espirais largas e soltas, com bastante brilho'],
            '3B' => ['type' => 'cacheado', 'label' => '3B - Cacheado Médio', 'desc' => 'Cachos definidos e fechados, com pontas mais secas'],
            '3C' => ['type' => 'cacheado', 'label' => '3C - Cacheado Fechado', 'desc' => 'Cachos bem apertadinhos e volumosos desde a raiz'],
            '4A' => ['type' => 'crespo', 'label' => '4A - Crespo Suave', 'desc' => 'Molinhas bem pequenas e definidas com padrão em S'],
            '4B' => ['type' => 'crespo', 'label' => '4B - Crespo em Z', 'desc' => 'Padrão em zigue-zague com alta necessidade de nutrição'],
            '4C' => ['type' => 'crespo', 'label' => '4C - Crespo Denso', 'desc' => 'Volumoso, sem padrão de cacho definido, super delicado'],
        ],
        'problems' => [
            'ressecamento' => ['label' => 'Ressecamento & Desidratação', 'desc' => 'Fios ásperos ao toque, opacos e sem movimento', 'icon' => 'fa-droplet-slash'],
            'frizz' => ['label' => 'Frizz & Arrepiados', 'desc' => 'Fios indisciplinados e sensíveis à umidade do ar', 'icon' => 'fa-bolt'],
            'danificado' => ['label' => 'Danificado por Química ou Calor', 'desc' => 'Cabelo poroso, emborrachado por tintura, química ou chapinha', 'icon' => 'fa-shield-halved'],
            'oleosidade' => ['label' => 'Oleosidade Excessiva na Raiz', 'desc' => 'Raiz pesada e brilhosa poucas horas após lavar', 'icon' => 'fa-fire-flame-simple'],
            'queda' => ['label' => 'Queda & Fraqueza', 'desc' => 'Fios caindo pela raiz ou quebrando facilmente no pente', 'icon' => 'fa-heart-crack'],
            'pontas_duplas' => ['label' => 'Pontas Duplas & Espigadas', 'desc' => 'Extremidades bifurcadas, finas e frágeis', 'icon' => 'fa-scissors'],
            'caspa' => ['label' => 'Caspa ou Coceira no Couro', 'desc' => 'Descamação visível e sensibilidade no couro cabeludo', 'icon' => 'fa-feather'],
            'falta_brilho' => ['label' => 'Opacidade & Falta de Brilho', 'desc' => 'Cabelo sem vida que não reflete a luz', 'icon' => 'fa-sun'],
        ],
        'goals' => [
            'hidratacao' => ['label' => 'Hidratação Profunda', 'desc' => 'Repor água, maciez e sedosidade aos fios'],
            'nutricao' => ['label' => 'Nutrição & Definição', 'desc' => 'Repor lipídios, selar pontas e eliminar frizz'],
            'reconstrucao' => ['label' => 'Reconstrução & Força', 'desc' => 'Repor massa capilar, queratina e combater a quebra'],
            'definicao' => ['label' => 'Super Definição dos Cachos', 'desc' => 'Cachos desenhados com durabilidade de vários dias de day after'],
            'crescimento' => ['label' => 'Crescimento e Fortalecimento', 'desc' => 'Estimular a raiz e fortalecer o comprimento'],
        ]
    ];
}

/**
 * Salva ou atualiza o perfil capilar do usuário
 */
function saveHairProfile(int $userId, array $data): array {
    $color = trim($data['hair_color'] ?? '');
    $curvature = trim($data['curvature'] ?? '');
    $problems = $data['problems'] ?? [];
    $goal = trim($data['goal'] ?? '');
    $washFreq = trim($data['wash_frequency'] ?? 'alternada');
    $chemTreated = !empty($data['chemical_treated']) ? 1 : 0;

    if (is_string($problems)) {
        $problems = [$problems];
    }
    $problems = array_filter($problems);

    if (empty($color) || empty($curvature) || empty($problems) || empty($goal)) {
        return ['success' => false, 'message' => 'Por favor, responda todas as perguntas obrigatórias do questionário.'];
    }

    // Determina o tipo de cabelo com base na curvatura (1=liso, 2=ondulado, 3=cacheado, 4=crespo)
    $curvatureGroup = substr($curvature, 0, 1);
    $hairType = match ($curvatureGroup) {
        '1' => 'liso',
        '2' => 'ondulado',
        '3' => 'cacheado',
        '4' => 'crespo',
        default => 'cacheado'
    };

    $pdo = getDBConnection();

    // Verifica se já existe perfil para este usuário
    $checkStmt = $pdo->prepare("SELECT id FROM hair_profiles WHERE user_id = ? LIMIT 1");
    $checkStmt->execute([$userId]);
    $existing = $checkStmt->fetch();

    $problemsJson = json_encode(array_values($problems), JSON_UNESCAPED_UNICODE);

    if ($existing) {
        $stmt = $pdo->prepare("
            UPDATE hair_profiles 
            SET hair_color = ?, hair_type = ?, curvature = ?, problems = ?, goal = ?, wash_frequency = ?, chemical_treated = ? 
            WHERE id = ?
        ");
        $stmt->execute([$color, $hairType, $curvature, $problemsJson, $goal, $washFreq, $chemTreated, $existing['id']]);
        $profileId = (int)$existing['id'];
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO hair_profiles 
            (user_id, hair_color, hair_type, curvature, problems, goal, wash_frequency, chemical_treated) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $color, $hairType, $curvature, $problemsJson, $goal, $washFreq, $chemTreated]);
        $profileId = (int)$pdo->lastInsertId();
    }

    // Atualiza a sessão
    $_SESSION['hair_profile'] = [
        'id' => $profileId,
        'user_id' => $userId,
        'hair_color' => $color,
        'hair_type' => $hairType,
        'curvature' => $curvature,
        'problems' => $problems,
        'goal' => $goal,
        'wash_frequency' => $washFreq,
        'chemical_treated' => $chemTreated
    ];

    return [
        'success' => true,
        'message' => 'Diagnóstico capilar concluído! Selecionamos os produtos com maior match para o seu perfil.',
        'profile' => $_SESSION['hair_profile']
    ];
}

/**
 * Retorna o perfil capilar do usuário autenticado
 */
function getUserHairProfile(int $userId): ?array {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM hair_profiles WHERE user_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$userId]);
    $profile = $stmt->fetch();

    if ($profile) {
        if (!is_array($profile['problems'])) {
            $profile['problems'] = json_decode($profile['problems'], true) ?: explode(',', $profile['problems']);
        }
    }
    return $profile ?: null;
}
