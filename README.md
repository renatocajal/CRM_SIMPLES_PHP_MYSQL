# Plano de Implementação do CRM Simples (PHP + MySQL)

O objetivo é construir um CRM simples e rápido focado em prospecção manual, follow-up e controle de negociações utilizando PHP puro, MySQL e HTML/CSS/JS.

## Stack de Tecnologia
- **Backend:** PHP Puro
- **Banco de Dados:** MySQL
- **Frontend:** HTML Nativo, CSS (Bootstrap para design responsivo/layout), JS (Vanilla + SortableJS para o pipeline de drag-and-drop)
- **Autenticação:** Sessões do PHP
- **E-mail:** Envio para redefinição ou simulação local de e-mail de recuperação de senha

## Esquema do Banco de Dados ([database.sql](file:///c:/Users/Familia/Desktop/novo_crm/database.sql))
1. `users`: id, email, password_hash, is_admin
2. `password_resets`: id, email, token, expires_at
3. `contatos`: id, nome, telefone, email, nicho, cidade, status, origem, observacoes, created_at, ultima_interacao
4. `produtos`: id, nome, descricao, preco_padrao
5. `contato_produtos`: id, contato_id, produto_id, preco_negociado, quantidade, status
6. `interacoes`: id, contato_id, tipo (primeiro_contato, follow_up), descricao, data
7. `tarefas`: id, contato_id, data, status, descricao
8. `tags`: id, nome, cor
9. `contato_tags`: contato_id, tag_id

## Estrutura do Projeto
```text
c:/novo_crm/
├── config.php             # Configuração do banco de dados (PDO)
├── setup.php              # Script executável para rodar database.sql e seeding
├── database.sql           # Script de estrutura do banco de dados
├── index.php              # Dashboard principal (protegida)
├── login.php              # Tela de login
├── logout.php             # Encerra a sessão
├── forgot_password.php    # Formulário de recuperação de senha
├── reset_password.php     # Validação de token e redefinição (link do email)
├── contatos.php           # Listagem de contatos
├── contato_novo.php       # Cadastro de contato
├── contato_detalhe.php    # Visualização e gestão completa do contato
├── pipeline.php           # Kanban/Pipeline com Drag and Drop
├── update_pipeline.php    # Endpoint AJAX para atualizar status
├── produtos.php           # Gestão de produtos adicionados
├── tags.php               # Gestão de tags
├── includes/
│   ├── header.php         # Cabeçalho da página (navbar e CSS Bootstrap)
│   ├── footer.php         # Rodapé (scripts)
│   ├── functions.php      # Funções utilitárias e envio de email
│   └── auth.php           # Funções de verificação de login
└── assets/
    ├── css/style.css
    └── js/main.js
```

## Funcionalidades
- **Autenticação:** Login padrão e área restrita (sessões). O fluxo de esquecimento de senha gera um token único (expira em 30min), salva na tabela `password_resets` e alerta o usuário para acessar o link de redefinição.
- **Dashboard:** Contabiliza diariamente os registros de `primeiro_contato` (para a meta de prospeção de 20 por dia) e exibe os `follow_up` do dia e progresso. Exibe botão prático pra negociar ao bater meta.
- **Contatos:** Lista os dados básicos e permite gerar o link para WhatsApp (`https://wa.me/55NUMERO`). Indicador temporal de "Lead esfriando" (mais de 3 dias sem conversas).
- **Pipeline:** Visualização estilo Kanban interativo, onde um Lead pode ser movido entre as colunas Novo, Negociação e Fechado, com requisições rodando em segundo plano.
- **Módulos acoplados ao Contato:** Cadastro de Tarefas pendentes/concluídas, adicão de tags classificatórias ("quente", "frio") e vínculo a um tipo de Produto Negociado (Cálculo do valor de Fechamento por multiplicação quantidade * preco_negociado).
#Divirta-se! Contato: 81-98241-8078 >> Whatsapp.
