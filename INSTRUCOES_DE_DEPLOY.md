# CRM Prospecção - Instruções de Deploy

Este arquivo serve como manual rápido de como colocar o sistema no ar em uma hospedagem real na nuvem.

### 1. Banco de Dados MySQL
Muitas hospedagens utilizam cPanel. Vá na área de "Banco de Dados" do seu painel.
1. Crie um novo Banco de Dados.
2. Crie um novo Usuário MySQL e gere uma Senha forte.
3. Adicione este Usuário ao Banco de Dados com **Todos os Privilégios**.

### 2. Configure a Conexão
Edite o arquivo `config.php` na raiz deste projeto com as credenciais que você acabou de criar no seu servidor:
```php
$host = 'localhost'; // Geralmente se mantém localhost
$dbname = 'nome_do_seu_banco';
$user = 'nome_do_seu_usuario';
$pass = 'senha_escolhida';
```

### 3. Upload de Arquivos
Suba toda a pasta deste projeto (ou o conteúdo dela) para o diretório `public_html` (ou raiz de hospedagem do seu site) usando FileZilla FTP ou o Gerenciador de Arquivos da Hospedagem.

### 4. Instalação / Setup Inicial (Criação de Tabelas)
Pelo seu navegador de internet, acesse o script do instalador:
`http://seusite.com.br/setup.php`
*Se você colocou os arquivos dentro de uma pasta /crm, ficará http://seusite.com.br/crm/setup.php*

Ao acessar essa página, o banco de dados e as tabelas serão estruturadas automaticamente. O usuário administrador `teste@teste.com` também será criado.

### 5. Apague o Setup (Segurança)
**MUITO IMPORTANTE:** Assim que abrir a página do Setup e tudo correr bem, vá no diretório do seu servidor e exclua os arquivos `setup.php` e `database.sql`. Dessa forma, ninguém poderá rodar acidentalmente essas engrenagens novamente.

### 6. Faça o Log in
Acesse o seu painel através do link do login `http://seusite.com.br/login.php` utilizando:
- E-mail: `teste@teste.com`
- Senha: `123456`

Está pronto. Boas vendas!
