-- ========================================================
-- Schema do Banco de Dados: HairMatch (MySQL 5.7+ / 8.0+)
-- Conformidade total com PDO e Tipagem Estrita
-- ========================================================

CREATE DATABASE IF NOT EXISTS `hairmatch_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `hairmatch_db`;

-- 1. Tabela de Usuários
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20) NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabela de Categorias de Cosméticos Capilares
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(80) NOT NULL,
    `slug` VARCHAR(80) NOT NULL UNIQUE,
    `description` VARCHAR(255) NULL,
    `icon` VARCHAR(50) DEFAULT 'fa-spray-can'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabela de Perfil Capilar do Usuário (Questionário)
CREATE TABLE IF NOT EXISTS `hair_profiles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `hair_color` VARCHAR(50) NOT NULL,       -- loiro, castanho, preto, ruivo, grisalho, colorido
    `hair_type` VARCHAR(50) NOT NULL,        -- liso, ondulado, cacheado, crespo
    `curvature` VARCHAR(10) NOT NULL,        -- 1A, 1B, 1C, 2A, 2B, 2C, 3A, 3B, 3C, 4A, 4B, 4C
    `problems` TEXT NOT NULL,                -- Lista em JSON ou CSV: ressecamento,frizz,queda,oleosidade,danificado,caspa,pontas_duplas
    `goal` VARCHAR(50) NOT NULL,             -- hidratacao, nutricao, reconstrucao, definicao, crescimento
    `wash_frequency` VARCHAR(30) NULL,       -- diaria, alternada, 2x_semana
    `chemical_treated` TINYINT(1) DEFAULT 0, -- 1 se tiver química/tintura, 0 se natural
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_profile_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabela de Produtos Capilares
CREATE TABLE IF NOT EXISTS `products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    `brand` VARCHAR(80) NOT NULL,
    `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `image_url` VARCHAR(255) NOT NULL,
    `short_desc` VARCHAR(255) NOT NULL,
    `manufacturer_desc` TEXT NOT NULL,        -- Descrição oficial do fabricante (menor peso no match)
    `target_curvatures` TEXT NOT NULL,       -- Curvaturas indicadas: ex: "1A,1B,1C" ou "3A,3B,3C,4A,4B,4C" ou "ALL"
    `target_colors` TEXT NOT NULL,           -- Cores indicadas: ex: "loiro,grisalho" ou "ALL"
    `target_problems` TEXT NOT NULL,         -- Problemas tratados: ex: "ressecamento,frizz,danificado"
    `manufacturer_rating` DECIMAL(3,1) DEFAULT 4.5, -- Nota base do fabricante (escala 0-5)
    `user_rating_avg` DECIMAL(3,2) DEFAULT 0.00,    -- Média calculada das avaliações dos usuários (maior peso no match)
    `reviews_count` INT DEFAULT 0,
    `in_stock` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_product_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Tabela de Avaliações dos Usuários (Reviews - Peso Principal no Algoritmo)
CREATE TABLE IF NOT EXISTS `reviews` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `rating` TINYINT NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
    `title` VARCHAR(120) NOT NULL,
    `comment` TEXT NOT NULL,
    `user_hair_curvature` VARCHAR(10) NULL,   -- Registra a curvatura do avaliador para dar relevância
    `user_hair_color` VARCHAR(50) NULL,       -- Registra a cor do cabelo do avaliador
    `recommended` TINYINT(1) DEFAULT 1,       -- 1 se recomendaria, 0 se não
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_review_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_review_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Tabela de Pedidos / Compras Simuladas
CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `total_amount` DECIMAL(10,2) NOT NULL,
    `status` VARCHAR(30) DEFAULT 'concluido',
    `payment_method` VARCHAR(50) DEFAULT 'Cartao de Credito',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_order_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `order_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `unit_price` DECIMAL(10,2) NOT NULL,
    CONSTRAINT `fk_item_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_item_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Índices para otimização de busca e filtragem
CREATE INDEX `idx_product_brand` ON `products` (`brand`);
CREATE INDEX `idx_product_price` ON `products` (`price`);
CREATE INDEX `idx_review_product` ON `reviews` (`product_id`);
CREATE INDEX `idx_profile_user` ON `hair_profiles` (`user_id`);
