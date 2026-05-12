# 💰 FinançasPRO — Guia de Instalação

## Estrutura de Arquivos

```
financas/
├── index.php              ← Página principal do app
├── banco.sql              ← Script para criar o banco de dados
├── README.md              ← Este arquivo
└── includes/
    ├── config.php         ← ⚠️ CONFIGURAR AQUI
    └── api.php            ← API de dados (não editar)
```

---

## ✅ Passo a Passo de Instalação

### 1. Criar o Banco de Dados

Acesse o **phpMyAdmin** da sua hospedagem e execute o conteúdo do arquivo `banco.sql`.

Ou via terminal MySQL:
```bash
mysql -u seu_usuario -p < banco.sql
```

---

### 2. Configurar a Conexão

Edite o arquivo `includes/config.php` e altere as 4 linhas:

```php
define('DB_HOST', 'localhost');        // geralmente 'localhost'
define('DB_USER', 'seu_usuario');      // usuário do MySQL
define('DB_PASS', 'sua_senha');        // senha do MySQL
define('DB_NAME', 'financas');         // nome do banco criado
```

---

### 3. Fazer Upload

Envie a pasta `financas/` para o servidor via FTP (FileZilla, etc.) dentro de `public_html/` ou `www/`.

**Exemplo:** `public_html/financas/`

---

### 4. Acessar

Abra no navegador:  
`https://seusite.com.br/financas/`

---

## 🔒 Segurança (Recomendado)

Para proteger o app com senha, crie um arquivo `.htaccess` na raiz do projeto:

```htaccess
AuthType Basic
AuthName "Área Restrita"
AuthUserFile /home/usuario/.htpasswd
Require valid-user
```

E crie o arquivo `.htpasswd` com:
```
seu_nome:senha_criptografada
```

Use https://htpasswd.net para gerar a senha criptografada.

---

## ✨ Funcionalidades

- ✅ Dashboard com resumo do mês (receitas, despesas, saldo, pendentes)
- ✅ Cadastro de receitas e despesas
- ✅ **Despesas parceladas** (cadastra todas as parcelas de uma vez)
- ✅ Filtro por mês, tipo, categoria e busca por texto
- ✅ Lista de pagamentos pendentes com indicador de vencimento
- ✅ Marcar como pago (com data) e estornar pagamento
- ✅ Categorias personalizáveis com cor
- ✅ Relatório anual com gráfico de barras
- ✅ Gráfico de despesas por categoria (donut)
- ✅ 100% responsivo (mobile/tablet/desktop)

---

## 📋 Requisitos

- PHP 7.4+ (recomendado 8.x)
- MySQL 5.7+ ou MariaDB 10.3+
- Extensão PDO_MySQL habilitada (padrão na maioria das hospedagens)
