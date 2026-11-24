<?php
/**
 * Seeder do Banco de Dados - HairMatch
 * Cria tabelas (compatível com MySQL e SQLite PDO) e popula com dados ricos de cosméticos capilares.
 */

require_once dirname(__DIR__) . '/config/config.php';

function runSeed() {
    $pdo = getDBConnection();
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

    echo "Conectado via PDO ao driver: $driver\n";

    // Criação das tabelas
    if ($driver === 'sqlite') {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                password TEXT NOT NULL,
                phone TEXT,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS categories (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                slug TEXT NOT NULL UNIQUE,
                description TEXT,
                icon TEXT DEFAULT 'fa-spray-can'
            );

            CREATE TABLE IF NOT EXISTS hair_profiles (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                hair_color TEXT NOT NULL,
                hair_type TEXT NOT NULL,
                curvature TEXT NOT NULL,
                problems TEXT NOT NULL,
                goal TEXT NOT NULL,
                wash_frequency TEXT,
                chemical_treated INTEGER DEFAULT 0,
                updated_at TEXT DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
            );

            CREATE TABLE IF NOT EXISTS products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                category_id INTEGER NOT NULL,
                name TEXT NOT NULL,
                brand TEXT NOT NULL,
                price REAL NOT NULL DEFAULT 0.0,
                image_url TEXT NOT NULL,
                short_desc TEXT NOT NULL,
                manufacturer_desc TEXT NOT NULL,
                target_curvatures TEXT NOT NULL,
                target_colors TEXT NOT NULL,
                target_problems TEXT NOT NULL,
                manufacturer_rating REAL DEFAULT 4.5,
                user_rating_avg REAL DEFAULT 0.0,
                reviews_count INTEGER DEFAULT 0,
                in_stock INTEGER DEFAULT 1,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE RESTRICT
            );

            CREATE TABLE IF NOT EXISTS reviews (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                product_id INTEGER NOT NULL,
                user_id INTEGER NOT NULL,
                rating INTEGER NOT NULL,
                title TEXT NOT NULL,
                comment TEXT NOT NULL,
                user_hair_curvature TEXT,
                user_hair_color TEXT,
                recommended INTEGER DEFAULT 1,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
            );

            CREATE TABLE IF NOT EXISTS orders (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                total_amount REAL NOT NULL,
                status TEXT DEFAULT 'concluido',
                payment_method TEXT DEFAULT 'Cartão de Crédito',
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
            );

            CREATE TABLE IF NOT EXISTS order_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                order_id INTEGER NOT NULL,
                product_id INTEGER NOT NULL,
                quantity INTEGER NOT NULL DEFAULT 1,
                unit_price REAL NOT NULL,
                FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE RESTRICT
            );
        ");
    } else {
        // MySQL - lê schema.sql
        $sql = file_get_contents(__DIR__ . '/schema.sql');
        $pdo->exec($sql);
    }

    // 1. Popula Categorias
    $categories = [
        ['Shampoos', 'shampoos', 'Limpeza suave, hidratação da raiz e equilíbrio do couro cabeludo', 'fa-pump-soap'],
        ['Condicionadores', 'condicionadores', 'Selagem das cutículas, desembaraço instantâneo e maciez', 'fa-wine-bottle'],
        ['Máscaras de Tratamento', 'mascaras', 'Tratamentos intensivos para cronograma capilar (HNR)', 'fa-jar'],
        ['Leave-in & Finalizadores', 'finalizadores', 'Proteção térmica, definição de cachos e controle de frizz', 'fa-magic'],
        ['Óleos Reparadores & Séruns', 'oleos-seruns', 'Nutrição pura, brilho espelhado e reparação de pontas', 'fa-droplet'],
    ];

    $stmtCat = $pdo->prepare("SELECT COUNT(*) FROM categories");
    $stmtCat->execute();
    if ($stmtCat->fetchColumn() == 0) {
        $insertCat = $pdo->prepare("INSERT INTO categories (name, slug, description, icon) VALUES (?, ?, ?, ?)");
        foreach ($categories as $cat) {
            $insertCat->execute($cat);
        }
        echo "Categorias inseridas com sucesso.\n";
    }

    // 2. Cria Usuário Demo
    $checkUser = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $checkUser->execute(['maria@hairmatch.com']);
    $demoUserId = $checkUser->fetchColumn();

    if (!$demoUserId) {
        $insertUser = $pdo->prepare("INSERT INTO users (name, email, password, phone) VALUES (?, ?, ?, ?)");
        $hashedPass = password_hash('senha123', PASSWORD_BCRYPT);
        $insertUser->execute(['Maria Silva', 'maria@hairmatch.com', $hashedPass, '(11) 98765-4321']);
        $demoUserId = $pdo->lastInsertId();

        // Insere perfil capilar da Maria
        $insertProfile = $pdo->prepare("INSERT INTO hair_profiles (user_id, hair_color, hair_type, curvature, problems, goal, wash_frequency, chemical_treated) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $insertProfile->execute([
            $demoUserId,
            'castanho',
            'cacheado',
            '3B',
            json_encode(['ressecamento', 'frizz', 'pontas_duplas']),
            'hidratacao',
            'alternada',
            0
        ]);

        echo "Usuária de demonstração (maria@hairmatch.com / senha123) criada com perfil capilar.\n";
    }

    // Cria outros usuários para as avaliações
    $reviewers = [
        ['Beatriz Souza', 'beatriz@exemplo.com', 'ondulado', '2B', 'loiro'],
        ['Camila Rocha', 'camila@exemplo.com', 'crespo', '4C', 'preto'],
        ['Juliana Mendes', 'juliana@exemplo.com', 'liso', '1A', 'ruivo'],
        ['Larissa Ferreira', 'larissa@exemplo.com', 'cacheado', '3A', 'castanho'],
    ];
    $reviewerIds = [];
    foreach ($reviewers as $rev) {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$rev[1]]);
        $uid = $check->fetchColumn();
        if (!$uid) {
            $ins = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $ins->execute([$rev[0], $rev[1], password_hash('senha123', PASSWORD_BCRYPT)]);
            $uid = $pdo->lastInsertId();

            $pIns = $pdo->prepare("INSERT INTO hair_profiles (user_id, hair_color, hair_type, curvature, problems, goal) VALUES (?, ?, ?, ?, ?, ?)");
            $pIns->execute([$uid, $rev[4], $rev[2], $rev[3], json_encode(['ressecamento', 'frizz']), 'nutricao']);
        }
        $reviewerIds[] = [
            'id' => $uid,
            'name' => $rev[0],
            'curvature' => $rev[3],
            'color' => $rev[4]
        ];
    }

    // 3. Popula Produtos
    $stmtProd = $pdo->prepare("SELECT COUNT(*) FROM products");
    $stmtProd->execute();
    if ($stmtProd->fetchColumn() == 0) {
        $products = [
            // Categoria 3: Máscaras
            [
                3, // Máscaras
                'Máscara Morte Súbita Hidratação Total',
                'Lola Cosmetics',
                42.90,
                'https://images.unsplash.com/photo-1608248597359-4573138ec301?auto=format&fit=crop&w=600&q=80',
                'Tratamento semanal restaurador e hidratante para fios sedentos de vida.',
                'Fórmula rica em complexo lipídico, extrato de aloe vera e óleo de coco. Promove reposição hídrica profunda, desembaraço instantâneo e alinhamento suave das escamas.',
                '1A,1B,1C,2A,2B,2C,3A,3B,3C,4A,4B,4C', // Todas as curvaturas
                'ALL',
                'ressecamento,frizz,danificado,pontas_duplas',
                4.8
            ],
            [
                3, // Máscaras
                'Máscara Curl Manifesto Masque Beurre Haute Nutrition',
                'Kérastase',
                349.90,
                'https://images.unsplash.com/photo-1535585209827-a15fcdbc4c2d?auto=format&fit=crop&w=600&q=80',
                'Máscara de nutrição extra rica com mel de Manuka e Ceramida para cachos e crespos.',
                'Indicação oficial Kérastase para nutrição ultra profunda, reforço da fibra capilar contra a quebra e garantia de elasticidade natural sem pesar.',
                '3A,3B,3C,4A,4B,4C', // Cachos e crespos
                'ALL',
                'ressecamento,frizz,danificado,quebradico',
                4.9
            ],
            [
                3, // Máscaras
                'Máscara Absolut Repair Gold Quinoa + Proteína',
                'L\'Oréal Professionnel',
                179.90,
                'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?auto=format&fit=crop&w=600&q=80',
                'Reestruturação instantânea para fios danificados, porosos ou quimicamente tratados.',
                'Inovação com micropartículas de ouro e quinoa dourada. Regenera a fibra sem rigidez, promovendo maciez extrema e redução de até 77% dos danos superficiais.',
                '1A,1B,1C,2A,2B,2C,3A,3B,3C,4A,4B,4C',
                'ALL',
                'danificado,pontas_duplas,ressecamento,quebradico',
                4.7
            ],
            [
                3, // Máscaras
                'Máscara Matizadora Blond Absolu Masque Ultra-Violet',
                'Kérastase',
                329.00,
                'https://images.unsplash.com/photo-1526947425960-945c6e72858f?auto=format&fit=crop&w=600&q=80',
                'Neutralização imediata de tons amarelados com Ácido Hialurônico e Flor de Edelweiss.',
                'Especialmente desenvolvida para cabelos descoloridos, loiros platinados e grisalhos. Neutraliza reflexos quentes indesejados e hidrata profundamente a fibra pós-química.',
                '1A,1B,1C,2A,2B,2C,3A,3B,3C,4A,4B,4C',
                'loiro,grisalho,colorido',
                'danificado,ressecamento,cor_desbotada',
                4.8
            ],

            // Categoria 1: Shampoos
            [
                1, // Shampoos
                'Shampoo Nutritive Bain Satin Rico',
                'Kérastase',
                229.00,
                'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?auto=format&fit=crop&w=600&q=80',
                'Shampoo nutritivo e hidratante profundo para cabelos secos a muito secos.',
                'Fórmula suave com proteínas de origem vegetal e niacinamida. Limpa delicadamente o couro cabeludo sem ressecar as pontas.',
                '1A,1B,1C,2A,2B,2C,3A,3B,3C',
                'ALL',
                'ressecamento,frizz,pontas_duplas',
                4.6
            ],
            [
                1, // Shampoos
                'Shampoo Meu Cacho Minha Vida Low Poo',
                'Lola Cosmetics',
                34.90,
                'https://images.unsplash.com/photo-1556228720-195a672e8a03?auto=format&fit=crop&w=600&q=80',
                'Higienizador suave emoliente livre de sulfatos para curvaturas 3 e 4.',
                'Fórmula vegetal com óleo de patauá e extratos botânicos. Mantém a hidratação natural dos cachos desde a lavagem.',
                '3A,3B,3C,4A,4B,4C',
                'ALL',
                'ressecamento,frizz,falta_definicao',
                4.7
            ],
            [
                1, // Shampoos
                'Shampoo Controle de Oleosidade Dercos Oil-Control',
                'Vichy',
                89.90,
                'https://images.unsplash.com/photo-1571781926291-c477ebfd024b?auto=format&fit=crop&w=600&q=80',
                'Complexo anti-sebo que purifica o couro cabeludo e retarda a oleosidade.',
                'Tecnologia dermatológica para raízes hiperoleosas com pontas secas. Sensação de frescor prolongado.',
                '1A,1B,1C,2A,2B',
                'ALL',
                'oleosidade,caspa',
                4.5
            ],
            [
                1, // Shampoos
                'Shampoo Cavalo Forte Força e Crescimento',
                'Haskell',
                48.00,
                'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?auto=format&fit=crop&w=600&q=80',
                'Biotina, Pantenol e Queratina para fios resistentes e estímulo de crescimento.',
                'Recomendado para fios fracos, quebradiços e com queda por tração ou escovação excessiva.',
                '1A,1B,1C,2A,2B,2C,3A,3B,3C,4A,4B,4C',
                'ALL',
                'queda,quebradico,danificado',
                4.4
            ],

            // Categoria 2: Condicionadores
            [
                2, // Condicionadores
                'Condicionador Invigo Nutri-Enrich Nutrição Imediata',
                'Wella Professionals',
                135.00,
                'https://images.unsplash.com/photo-1535585209827-a15fcdbc4c2d?auto=format&fit=crop&w=600&q=80',
                'Condicionador nutritivo com Goji Berry e Vitamina E.',
                'Hidrata instantaneamente os cabelos secos e desgastados, restaurando a vitalidade e facilitando a escovação.',
                '1A,1B,1C,2A,2B,2C,3A,3B',
                'ALL',
                'ressecamento,frizz,pontas_duplas',
                4.8
            ],
            [
                2, // Condicionadores
                'Condicionador S.O.S Cachos Rícino e Queratina',
                'Salon Line',
                27.90,
                'https://images.unsplash.com/photo-1519735777090-ec97162dc266?auto=format&fit=crop&w=600&q=80',
                'Selagem profunda e reparação extrema para cabelos cacheados e crespos.',
                'Rico em ácidos graxos essenciais e queratina vegetal para encorpar o fio e blindar contra o ressecamento.',
                '3A,3B,3C,4A,4B,4C',
                'castanho,preto,colorido',
                'ressecamento,frizz,quebradico',
                4.6
            ],
            [
                2, // Condicionadores
                'Condicionador Soul Color Proteção da Cor',
                'Braé',
                119.00,
                'https://images.unsplash.com/photo-1527799820374-dcf8d9d4a388?auto=format&fit=crop&w=600&q=80',
                'Ação antioxidante e proteção térmica que mantém a intensidade da cor.',
                'Desenvolvido para cabelos coloridos e descoloridos. Evita o desbotamento precoce e sela a cutícula com brilho radiante.',
                '1A,1B,1C,2A,2B,2C',
                'loiro,ruivo,colorido,castanho',
                'danificado,cor_desbotada,ressecamento',
                4.5
            ],

            // Categoria 4: Leave-in & Finalizadores
            [
                4, // Leave-in & Finalizadores
                'Tratamento Reconstrutor Uso Obrigatório',
                'Truss',
                149.90,
                'https://images.unsplash.com/photo-1598440947619-2c35fc9aa908?auto=format&fit=crop&w=600&q=80',
                'O reconstrutor térmico favorito dos salões. Recupera o aspecto saudável dos fios.',
                'Age reconstruindo as áreas fragilizadas, devolvendo brilho, maciez e proteção contra calor de secador e chapinha.',
                '1A,1B,1C,2A,2B,2C,3A,3B,3C,4A,4B,4C',
                'ALL',
                'danificado,frizz,pontas_duplas,ressecamento',
                4.9
            ],
            [
                4, // Leave-in & Finalizadores
                'Creme de Pentear Creoula Modelador de Cachos',
                'Lola Cosmetics',
                49.90,
                'https://images.unsplash.com/photo-1522337094846-8a818192de1f?auto=format&fit=crop&w=600&q=80',
                'Define ondas e cachos com movimento natural, controle de umidade e proteção UV.',
                'Fórmula com óleo de abacate e água de coco. Proporciona super definição, brilho e até 48 horas de efeito antifrizz.',
                '2C,3A,3B,3C,4A,4B,4C',
                'ALL',
                'frizz,ressecamento,falta_definicao',
                4.8
            ],
            [
                4, // Leave-in & Finalizadores
                'Leave-in Disciplinante Liso dos Sonhos Elseve',
                'L\'Oréal Paris',
                32.50,
                'https://images.unsplash.com/photo-1500839941678-aae14dbfae9a?auto=format&fit=crop&w=600&q=80',
                'Alinha os fios e bloqueia o frizz por até 48 horas com Queratina Vegetal e Manteiga de Cacau.',
                'Indicado para cabelos lisos e ondulados. Proteção antiumidade com textura ultraleve que não pesa no comprimento.',
                '1A,1B,1C,2A,2B',
                'ALL',
                'frizz,pontas_duplas',
                4.5
            ],

            // Categoria 5: Óleos Reparadores & Séruns
            [
                5, // Óleos Reparadores & Séruns
                'Óleo Capilar Oil Reflections Luminous Smoothening',
                'Wella Professionals',
                159.00,
                'https://images.unsplash.com/photo-1608248597359-4573138ec301?auto=format&fit=crop&w=600&q=80',
                'Óleo versátil com extrato de chá branco e óleo de macadâmia para efeito luminoso.',
                'Nutre instantaneamente sem pesar. Proporciona maciez aveludada, disciplina o frizz e sela pontas duplas.',
                '1A,1B,1C,2A,2B,2C,3A,3B,3C,4A,4B,4C',
                'ALL',
                'ressecamento,frizz,pontas_duplas',
                4.9
            ],
            [
                5, // Óleos Reparadores & Séruns
                'Óleo Elixir Ultime L\'Huile Originale',
                'Kérastase',
                399.00,
                'https://images.unsplash.com/photo-1526947425960-945c6e72858f?auto=format&fit=crop&w=600&q=80',
                'Ícone de luxo capilar com Óleo de Camélia Francesa e Óleo de Marula.',
                'Confere nutrição sublime, proteção térmica até 230°C, controle de arrepiados e fragrância floral duradoura.',
                '1A,1B,1C,2A,2B,2C,3A,3B,3C,4A,4B,4C',
                'ALL',
                'ressecamento,frizz,falta_brilho,pontas_duplas',
                4.9
            ],
            [
                5, // Óleos Reparadores & Séruns
                'Sérum Noturno Night Spa Reconstrução Noturna',
                'Truss',
                189.90,
                'https://images.unsplash.com/photo-1519735777090-ec97162dc266?auto=format&fit=crop&w=600&q=80',
                'Tratamento noturno sem enxágue para recuperação de cabelos secos e quebradiços.',
                'Com cera vegana 100% natural e blend de óleos nobres. Hidrata e reconstrói as cutículas durante o sono.',
                '1A,1B,1C,2A,2B,2C,3A,3B,3C,4A,4B,4C',
                'ALL',
                'danificado,ressecamento,quebradico,frizz',
                4.8
            ],
            [
                5, // Óleos Reparadores & Séruns
                'Óleo Pinga! Patauá e Moringa',
                'Lola Cosmetics',
                36.90,
                'https://images.unsplash.com/photo-1556228720-195a672e8a03?auto=format&fit=crop&w=600&q=80',
                'Óleo multifuncional pré e pós-shampoo e finalizador com textura leve.',
                'Nutre profundamente cabelos desidratados com toque seco e cheiro irresistível.',
                '2A,2B,2C,3A,3B,3C,4A,4B,4C',
                'ALL',
                'ressecamento,frizz,pontas_duplas',
                4.7
            ],
        ];

        $insProd = $pdo->prepare("
            INSERT INTO products 
            (category_id, name, brand, price, image_url, short_desc, manufacturer_desc, target_curvatures, target_colors, target_problems, manufacturer_rating, user_rating_avg, reviews_count) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0.0, 0)
        ");

        foreach ($products as $p) {
            $insProd->execute($p);
        }
        echo "18 produtos inseridos com sucesso.\n";
    }

    // 4. Popula Avaliações Reais dos Usuários (Reviews com Pesos Reais)
    $stmtRev = $pdo->prepare("SELECT COUNT(*) FROM reviews");
    $stmtRev->execute();
    if ($stmtRev->fetchColumn() == 0) {
        $reviews = [
            // Produto 1: Máscara Morte Súbita
            [1, $demoUserId, 5, 'Salvou meu cabelo cacheado do ressecamento!', 'Comprei após o questionário do HairMatch indicar para o meu perfil 3B. O resultado na primeira aplicação foi inacreditável! Cachos definidos, brilho espelhado e zero frizz.', '3B', 'castanho', 1],
            [1, $reviewerIds[0]['id'], 5, 'Cheiro maravilhoso e maciez imediata', 'Meu cabelo é 2B ondulado e muitas máscaras pesam, mas a Morte Súbita deixou meus fios soltos e super hidratados.', '2B', 'loiro', 1],
            [1, $reviewerIds[1]['id'], 5, 'Excelente para cronograma capilar', 'No meu cabelo crespo 4C ajudou demais na etapa de nutrição. Recomendo muito!', '4C', 'preto', 1],

            // Produto 2: Kérastase Curl Manifesto Masque
            [2, $demoUserId, 5, 'A melhor máscara para cachos que já usei na vida', 'Vale cada centavo. O mel de manuka faz mágica na fibra do cacho. Definição com elasticidade e perfume de salão de luxo!', '3B', 'castanho', 1],
            [2, $reviewerIds[1]['id'], 5, 'Perfeição para cabelo crespo 4C', 'Nutre sem resíduos brancos. A curvatura fica evidente e o toque é aveludado.', '4C', 'preto', 1],

            // Produto 3: Absolut Repair L'Oreal
            [3, $reviewerIds[2]['id'], 5, 'Recuperou meu cabelo pós-química', 'Meu cabelo 1A liso estava quebrando muito por conta de tintura. Com essa máscara parou a quebra na hora.', '1A', 'ruivo', 1],
            [3, $reviewerIds[0]['id'], 4, 'Reconstrução potente', 'Fios muito alinhados e encorpados. Uso quinzenalmente e amo o resultado.', '2B', 'loiro', 1],

            // Produto 4: Blond Absolu Masque Kérastase
            [4, $reviewerIds[0]['id'], 5, 'Loiro platinado sem nenhum tom amarelo!', 'O melhor matizador que já testei. Não chumba o fio e não resseca em nada.', '2B', 'loiro', 1],

            // Produto 6: Shampoo Meu Cacho Minha Vida
            [6, $demoUserId, 5, 'Limpeza suave sem embolar os fios', 'Não agride os cachos e prepara perfeitamente o cabelo para a máscara.', '3B', 'castanho', 1],
            [6, $reviewerIds[3]['id'], 4, 'Muito bom para o dia a dia', 'Adoro a consistência e o aroma refrescante.', '3A', 'castanho', 1],

            // Produto 12: Truss Uso Obrigatório
            [12, $demoUserId, 5, 'Indispensável na minha rotina capilar', 'Uso como protetor térmico antes do difusor e os cachos não ficam com frizz nenhum!', '3B', 'castanho', 1],
            [12, $reviewerIds[2]['id'], 5, 'Brilho espelhado e desembaraço rápido', 'Perfeito para quem faz escova ou usa secador.', '1A', 'ruivo', 1],

            // Produto 15: Wella Oil Reflections
            [15, $demoUserId, 5, 'O melhor óleo finalizador do mercado', 'Não pesa nos cachos, sela as pontas e deixa um cheiro sofisticado o dia todo.', '3B', 'castanho', 1],
            [15, $reviewerIds[1]['id'], 5, 'Brilho incomparável', 'Passo umas gotinhas todo dia de manhã no meu crespo, fica impecável.', '4C', 'preto', 1],
        ];

        $insRev = $pdo->prepare("
            INSERT INTO reviews 
            (product_id, user_id, rating, title, comment, user_hair_curvature, user_hair_color, recommended) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        foreach ($reviews as $rev) {
            $insRev->execute($rev);
        }

        // 5. Atualiza user_rating_avg e reviews_count em todos os produtos
        $calcStmt = $pdo->query("
            SELECT product_id, AVG(rating) as avg_rating, COUNT(*) as total_reviews 
            FROM reviews 
            GROUP BY product_id
        ");
        $results = $calcStmt->fetchAll();

        $updProd = $pdo->prepare("
            UPDATE products 
            SET user_rating_avg = ?, reviews_count = ? 
            WHERE id = ?
        ");
        foreach ($results as $row) {
            $updProd->execute([
                round((float)$row['avg_rating'], 2),
                (int)$row['total_reviews'],
                (int)$row['product_id']
            ]);
        }

        echo "Avaliações inseridas e médias dos produtos recalculadas com sucesso.\n";
    }

    echo "Banco de dados inicializado com sucesso!\n";
}

runSeed();
