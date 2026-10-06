# HairMatch

## Objetivos
Criar um sistema onde a pessoa vai responder um questionário relacionado ao tipo de cabelo dela, indicando a cor, a curvatura e o problema, e o sistema vai fornecer os produtos ideais para o tipo de cabelo, como se se fosse uma loja virtual, os produtos vão ser mostrados para o usuário pela avaliações dos usuários onde vai ter mais peso e descrição do fabricante do produto onde terá menor peso. 

### Stack Tecnlógico
- Backend: PHP estruturado com sessôes nativas
- Banco de dados: MySQL (PDO para segurança)
- Frontend: HTML5, PHP, CSS, Tailwind CSS

#### Regras de negócio (CORE)
Fazer o login na plataforma
Responder o questionário
Buscar o produto desejado dentre os selecionados pós questionário
Avaliar o produto

##### Regras Globais
- Use sempre PDO para conexão e queries no MySQL para evitar SQL Injections
- Mantenha o código limpo e comente apenas logicas complexas.
- Separe os arquivos de forma lógica: um arquivo para conexão com a base (bd.php) e scripts de backend isolados e views em HTML5/PHP, nunca faça o sistema como um monolito, deixe sempre separados todas as regras para facilitar os futuros upgrades.
 - Estilize as telas em Tailwind de forma responsiva priorizando o MobileFrist.
 - Retorne sempre as mensagens de erros de forma claras na interface para o usuário (TOAST)
 - sempre trate as mensagens de caixa de mensagens nativas do navegador em um MODAL
