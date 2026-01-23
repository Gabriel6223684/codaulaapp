# Fluxo de Login: HTML → JS → PHP → Banco de Dados

## 📋 Resumo do Fluxo

```
HTML (Clique)
    ↓
JavaScript (login.js - Captura evento)
    ↓
Fetch API (Envia JSON via POST)
    ↓
PHP Controller (Login.php - autenticar())
    ↓
Database Connection (Connection.php - PDO)
    ↓
PostgreSQL/PgAdmin (Banco de dados)
```

---

## 🔧 Componentes

### 1️⃣ **HTML** - Formulário de Login (`app/view/login.html`)

```html
<form id="formlogin" action="/login" method="post">
    <input id="loginEmail" name="login" type="text" required>
    <input id="loginPassword" name="senha" type="password" required>
    <button type="button" id="prelogin" class="btn btn-primary">Entrar</button>
</form>
```

**Importante:** O botão tem `type="button"` para não enviar o formulário automaticamente. O JavaScript captura o clique.

---

### 2️⃣ **JavaScript** - Captura e Envio (`html/js/login.js`)

```javascript
// Captura clique do botão "Entrar"
document.getElementById('prelogin')?.addEventListener('click', async function(e) {
    e.preventDefault();
    clearLoginError();

    const login = document.getElementById('loginEmail')?.value?.trim();
    const senha = document.getElementById('loginPassword')?.value?.trim();

    if (!login || !senha) {
        showLoginError('E-mail/telefone e senha são obrigatórios');
        return;
    }

    try {
        // Enviando para o backend via fetch
        const res = await fetch('/login/autenticar', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                login: login,
                senha: senha
            })
        });

        const data = await res.json();

        if (data.status) {
            showLoginSuccess('Login realizado com sucesso!');
            setTimeout(() => {
                window.location.href = '/dashboard';
            }, 1000);
        } else {
            showLoginError(data.msg || 'Erro ao fazer login');
        }
    } catch(err) {
        showLoginError('Erro de rede: ' + err.message);
    }
});
```

**O que acontece:**
- ✅ Captura o clique do botão "Entrar"
- ✅ Obtém os valores dos inputs
- ✅ Valida se está preenchido
- ✅ Envia JSON via POST para `/login/autenticar`
- ✅ Recebe resposta e trata sucesso/erro

---

### 3️⃣ **PHP Controller** - Processa Login (`app/controller/Login.php`)

```php
public function autenticar(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $data = $request->getParsedBody();
    if (empty($data)) {
        $data = json_decode((string) $request->getBody(), true) ?? [];
    }

    $login = trim($data['login'] ?? '');
    $senha = $data['senha'] ?? '';

    if (!$login || !$senha) {
        return $this->SendJson($response, [
            'status' => false,
            'msg' => 'Informe login e senha'
        ], 400);
    }

    try {
        // Conecta ao banco de dados
        $con = \app\database\Connection::connection();

        $loginLower = strtolower($login);
        $loginCel   = preg_replace('/\D+/', '', $login);

        // Query preparada para buscar usuário
        $stmt = $con->prepare("
            SELECT *
            FROM vw_usuario_contatos
            WHERE LOWER(email) = :email
               OR regexp_replace(celular, '\\D', '', 'g') = :celular
               OR cpf = :login
            LIMIT 1
        ");

        $stmt->execute([
            'email'   => $loginLower,
            'celular' => $loginCel,
            'cpf'     => $login
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Valida se usuário existe e senha está correta
        if (!$user || !password_verify($senha, $user['senha'])) {
            return $this->SendJson($response, [
                'status' => false,
                'msg' => 'Usuário ou senha inválidos'
            ], 401);
        }

        // Verifica se usuário está ativo
        if (!$user['ativo']) {
            return $this->SendJson($response, [
                'status' => false,
                'msg' => 'Usuário inativo'
            ], 403);
        }

        // Salva informações na sessão
        $_SESSION['usuario'] = [
            'logado'        => true,
            'id'            => $user['id'],
            'nome'          => $user['nome'],
            'email'         => $user['email'],
            'administrador' => (bool)($user['administrador'] ?? false)
        ];

        return $this->SendJson($response, [
            'status' => true,
            'msg' => 'Login realizado com sucesso'
        ]);

    } catch (\Exception $e) {
        return $this->SendJson($response, [
            'status' => false,
            'msg' => 'Erro interno no servidor'
        ], 500);
    }
}
```

**O que acontece:**
- ✅ Recebe dados JSON do JavaScript
- ✅ Valida entrada
- ✅ Conecta ao banco via `Connection::connection()`
- ✅ Busca usuário por email, celular ou CPF
- ✅ Verifica senha com `password_verify()`
- ✅ Cria sessão se tudo OK
- ✅ Retorna JSON com status

---

### 4️⃣ **Database Connection** - Conecta ao Banco (`app/database/Connection.php`)

```php
public static function connection(): PDO
{
    try {
        if (static::$pdo) {
            return static::$pdo;
        }

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => true,
        ];

        $host = getenv('DB_HOST') ?: 'localhost';
        $port = getenv('DB_PORT') ?: '5432';
        $dbname = getenv('DB_NAME') ?: 'senac';
        $user = getenv('DB_USER') ?: 'senac';
        $password = getenv('DB_PASSWORD') ?: 'senac';

        $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";

        static::$pdo = new PDO($dsn, $user, $password, $options);
        static::$pdo->exec("SET NAMES 'utf8'");

        return static::$pdo;
    } catch (\PDOException $e) {
        error_log('[DB] Postgres connection failed: ' . $e->getMessage());
        throw new Exception('Erro na conexão com banco de dados: ' . $e->getMessage());
    }
}
```

**O que acontece:**
- ✅ Cria conexão PDO ao PostgreSQL
- ✅ Usa variáveis de ambiente para configuração
- ✅ Retorna conexão PDO para usar queries

---

### 5️⃣ **PostgreSQL** - Banco de Dados

Tabela utilizada:
```sql
SELECT *
FROM vw_usuario_contatos
WHERE email = 'usuario@email.com'
  OR celular = '11999999999'
  OR cpf = '12345678900';
```

**Dados retornados:**
- `id` - ID do usuário
- `nome` - Nome completo
- `email` - Email
- `celular` - Telefone
- `cpf` - CPF
- `senha` - Senha hasheada (bcrypt)
- `ativo` - Boolean se usuário está ativo
- `administrador` - Boolean se é admin

---

## 🚀 Teste Completo

### 1️⃣ No Navegador

1. Acesse: `http://localhost/login`
2. Preencha email/telefone e senha
3. Clique em "Entrar"
4. Abra **DevTools (F12)** → **Network** para ver requisição
5. Abra **Console** para ver logs

### 2️⃣ Requisição HTTP

```bash
curl -X POST http://localhost/login/autenticar \
  -H "Content-Type: application/json" \
  -d '{"login":"usuario@email.com","senha":"senha123"}'
```

**Resposta sucesso:**
```json
{
  "status": true,
  "msg": "Login realizado com sucesso"
}
```

**Resposta erro:**
```json
{
  "status": false,
  "msg": "Usuário ou senha inválidos"
}
```

### 3️⃣ Verificar no PostgreSQL

```sql
-- Buscar usuários no banco
SELECT id, nome, email, ativo FROM vw_usuario_contatos LIMIT 10;

-- Buscar logs de conexão
SELECT * FROM pg_stat_statements WHERE query LIKE '%usuario_contatos%';
```

---

## 📝 Configuração de Ambiente

Adicione ao arquivo `.env` ou `docker-compose.yml`:

```env
DB_HOST=localhost
DB_PORT=5432
DB_NAME=senac
DB_USER=senac
DB_PASSWORD=senac
```

---

## ✅ Checklist Completo

- [x] HTML tem botão com ID `#prelogin`
- [x] JavaScript captura evento de clique
- [x] JavaScript envia JSON via fetch POST
- [x] PHP recebe e valida dados
- [x] PHP conecta ao banco via `Connection::connection()`
- [x] PHP busca usuário no banco
- [x] PHP verifica senha
- [x] PHP cria sessão
- [x] PHP retorna JSON com status
- [x] Connection.php usa variáveis de ambiente
- [x] Connection.php conecta ao PostgreSQL via PDO

---

## 🔒 Segurança

- ✅ Senhas hasheadas com `password_hash(PASSWORD_DEFAULT)`
- ✅ Queries preparadas com prepared statements
- ✅ Validação de entrada no PHP
- ✅ PDO com `ATTR_EMULATE_PREPARES => false`
- ✅ Mensagens de erro genéricas para o usuário
- ✅ Log de erros no servidor via `error_log()`

---

## 📞 Suporte

Se tiver problemas:

1. **Verifique os logs:**
   ```bash
   tail -f /var/log/nginx/error.log
   tail -f /var/log/php-fpm.log
   ```

2. **Teste a conexão com o banco:**
   ```bash
   psql -h localhost -U gabriel -d senac
   ```

3. **Verifique as variáveis de ambiente:**
   ```bash
   env | grep DB_
   ```
