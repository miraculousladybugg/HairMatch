# HairMatch — Plataforma Inteligente de Cosméticos Capilares

> Sistema web desenvolvido rigorosamente de acordo com as especificações em `requisitos.md`.

---

## 🌟 Visão Geral do Projeto

O **HairMatch** é uma loja virtual inteligente onde o consumidor responde a um questionário diagnóstico sobre seu tipo de cabelo (**cor, curvatura de 1A a 4C, e desafios capilares**). 

Com base nessas respostas, o sistema ranqueia e apresenta os produtos ideais através de um **algoritmo ponderado** onde:
- **Avaliações dos Usuários têm MAIOR PESO (70%):** Notas reais, depoimentos e eficácia relatada por pessoas com o mesmo padrão capilar.
- **Descrição do Fabricante tem MENOR PESO (30%):** Ativos químicos, fórmulas laboratoriais e indicações do rótulo.

---

## 🛠️ Stack Tecnológica

- **Backend:** PHP 8 estruturado com sessões nativas (`$_SESSION`)
- **Banco de Dados:** MySQL com **PDO** (com suporte automático transparente a SQLite PDO para testes rápidos sem depender de servidor MySQL ativo)
- **Frontend:** HTML5 Semântico, PHP, CSS3, **Tailwind CSS v3 (MobileFirst)**, FontAwesome 6, Google Fonts
- **Componentes Globais:**
  - **TOAST Notifications:** Mensagens de erro e sucesso claras, não-bloqueantes e elegantes na interface.
  - **MODAL Customizado:** Substituição total de caixas nativas (`window.alert`, `confirm`) por modais modernos estilizados em Tailwind.

---

## 🎯 Regras de Negócio (CORE) Implementadas

1. **Fazer o login na plataforma (`login.php`, `cadastro.php`, `perfil.php`):**
   - Autenticação segura com senhas criptografadas via `password_hash(..., PASSWORD_BCRYPT)`.
   - Gerenciamento de sessão nativa segura.
   - Painel do usuário com diagnóstico capilar ativo e histórico de avaliações.

2. **Responder o questionário capilar (`quiz.php`):**
   - **Passo 1: Cor do Cabelo:** Loiro, Castanho, Preto, Ruivo, Grisalho, Colorido.
   - **Passo 2: Curvatura e Tipo de Fio:** Lisos (1A, 1B, 1C), Ondulados (2A, 2B, 2C), Cacheados (3A, 3B, 3C), Crespos (4A, 4B, 4C).
   - **Passo 3: Desafios Capilares:** Ressecamento, Frizz, Danos por Química, Oleosidade, Queda, Pontas Duplas, Caspa, Falta de Brilho.
   - **Passo 4: Objetivo do Cronograma:** Hidratação profunda, Nutrição e definição, Reconstrução e força, Crescimento.
   - Salva o perfil capilar no banco de dados via PDO e sincroniza com a sessão.

3. **Buscar o produto desejado dentre os selecionados pós-questionário (`produtos.php`):**
   - Vitrine de produtos filtrados e ranqueados especificamente para a curvatura do usuário.
   - Campo de busca em tempo real por nome do produto, marca e termos.
   - Filtros por categoria (Shampoos, Condicionadores, Máscaras, Leave-in, Óleos).
   - Filtros por marca e faixa de preço.
   - Ordenação pelo **Maior Match Capilar (Ponderado)**.
   - Exibição de % de compatibilidade (ex: "98% Match", selos "Match Perfeito", "Altamente Recomendado").

4. **Avaliar o produto (`produto.php`):**
   - Usuários logados podem atribuir de 1 a 5 estrelas, título, relato de experiência e recomendação.
   - A avaliação registra o tipo de cabelo de quem avaliou (ex: *Cabelo 3B Cacheado*).
   - Recálculo atômico e instantâneo da nota média e contagem de avaliações via PDO.

5. **Carrinho de Compras e Checkout Simulado (`carrinho.php`):**
   - Adição e remoção de cosméticos, controle de quantidade.
   - Cálculo automático de frete grátis para compras acima de R$ 150.
   - Finalização de pedido com transações PDO no banco de dados.

---

## 📐 Estrutura de Arquivos (Separação Lógica Não-Monolítica)

```
HairMatch/
├── index.php                 # Página Inicial & Destaques
├── quiz.php                  # Questionário Capilar (Etapas 1 a 4)
├── produtos.php              # Vitrine Virtual Pós-Diagnóstico & Busca
├── produto.php               # Detalhes do Cosmético & Avaliações
├── carrinho.php              # Carrinho de Compras & Checkout
├── login.php                 # Autenticação de Usuários
├── cadastro.php              # Criação de Contas
├── perfil.php                # Painel de Diagnóstico Capilar
├── logout.php                # Encerramento de Sessão
├── config/
│   ├── bd.php                # Conexão PDO segura (MySQL & fallback SQLite)
│   └── config.php            # Sessões nativas, helpers, toasts e CSRF
├── backend/
│   ├── auth.php              # Lógica de login e cadastro
│   ├── quiz.php              # Lógica do questionário e perfil capilar
│   ├── products.php          # Algoritmo de recomendação ponderado e busca
│   ├── reviews.php           # Avaliações com recálculo atômico da média
│   └── cart.php              # Carrinho e criação de pedidos via PDO
├── database/
│   ├── schema.sql            # Script DDL/DML oficial para MySQL
│   ├── seed.php              # Seeder com 18 produtos de marcas renomadas
│   └── hairmatch.db          # Base SQLite criada automaticamente
├── includes/
│   ├── header.php            # Layout topo MobileFirst, Tailwind e Meta tags
│   ├── navbar.php            # Menu responsivo e status capilar
│   ├── footer.php            # Rodapé informativo
│   ├── toast.php             # Notificações Toast nativas
│   └── modal.php             # Gerenciador de Modais Customizados
├── assets/
│   ├── css/style.css         # Estilos complementares e animações
│   └── js/
│       ├── toast.js          # API JS do Toast (AppToast.show)
│       ├── modal.js          # API JS do Modal (AppModal.alert / confirm)
│       └── quiz.js           # Navegação step-by-step do questionário
└── README.md
```

---

## 🚀 Como Executar o Projeto

### Opção 1: Servidor Embutido do PHP (Imediato)
Se você possui o PHP instalado (ou utilizar o PHP portable disponibilizado no ambiente):
```powershell
cd C:\Users\Aluno\.gemini\antigravity\scratch\HairMatch
php -S localhost:8000
```
Acesse no navegador: **http://localhost:8000**

### Opção 2: XAMPP / WampServer / Laragon (MySQL)
1. Copie a pasta `HairMatch` para a raiz web (ex: `C:\xampp\htdocs\HairMatch`).
2. Abra o **phpMyAdmin** e crie a base de dados `hairmatch_db`.
3. Importe o arquivo `database/schema.sql`.
4. Execute `php database/seed.php` para popular os produtos e avaliações.
5. Acesse: **http://localhost/HairMatch**

---

## 🔑 Credenciais de Teste Rápido

O banco de dados já vem populado com uma usuária e perfil capilar completo para testes imediatos:
- **E-mail:** `maria@hairmatch.com`
- **Senha:** `senha123`
- **Perfil Capilar Pré-configurado:** Curvatura 3B (Cacheado Médio), Cor Castanho, Foco em Ressecamento e Frizz.
- *Dica:* Na tela de login, há um botão "Preencher Dados" para preencher instantaneamente.
