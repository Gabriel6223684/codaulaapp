<?php

namespace app\controller;

use app\database\builder\UpdateQuery;
use app\database\builder\SelectQuery;
use app\database\builder\InsertQuery;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use app\trait\Template;
use PDO;

class Login extends Base
{
    // Renderiza a página de login
    public function login(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            return $this->getTwig()->render(
                $response,
                $this->setView('login'),
                ['titulo' => 'Autenticação']
            );
        } catch (\Exception $e) {
            return $this->SendJson($response, [
                'status' => false,
                'msg' => 'Erro ao carregar página'
            ], 500);
        }
    }

    // Health check endpoint to test server responses
    public function ping($request, $response)
    {
        try {
            $server = [
                'php_sapi' => PHP_SAPI,
                'time' => date('c')
            ];
            return $this->SendJson($response, ['status' => true, 'msg' => 'pong', 'server' => $server]);
        } catch (\Exception $e) {
            return $this->SendJson($response, ['status' => false, 'msg' => $e->getMessage()], 500);
        }
    }
    
    // Pré-cadastro de usuários
    public function precadastro($request, $response)
    {
        try {
            $form = $request->getParsedBody();
            // Fallback para quando o body vem como JSON
            if (empty($form)) {
                $json = json_decode((string) $request->getBody(), true);
                $form = $json ?? [];
            }

            // Log para depuração
            $remoteIp = $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown';
            $ct = $request->getHeaderLine('Content-Type');
            error_log("[LOGIN][precadastro] IP: $remoteIp CT: $ct");

            // Validar campos obrigatórios
            if (empty($form['nome']) || empty($form['email']) || empty($form['senhaCadastro'])) {
                return $this->SendJson($response, [
                    'status' => false,
                    'msg' => 'Nome, E-mail e Senha são obrigatórios'
                ], 400);
            }

            // Normalizar dados
            $nome = trim($form['nome']);
            $email = strtolower(trim($form['email']));
            $cpf = preg_replace('/\D+/', '', $form['cpf'] ?? '');
            $celular = preg_replace('/\D+/', '', $form['telefone'] ?? $form['celular'] ?? '');
            $senha = $form['senhaCadastro'];

            // Validar email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->SendJson($response, [
                    'status' => false,
                    'msg' => 'E-mail inválido'
                ], 400);
            }

            try {
                $con = \app\database\Connection::connection();

                // Verificar se já existe usuário com esse email
                $stmt = $con->prepare("SELECT id FROM usuario WHERE LOWER(email) = :email LIMIT 1");
                $stmt->execute(['email' => $email]);
                if ($stmt->fetch()) {
                    return $this->SendJson($response, [
                        'status' => false,
                        'msg' => 'E-mail já cadastrado'
                    ], 409);
                }

                // Verificar CPF duplicado (se fornecido)
                if (!empty($cpf)) {
                    $stmt = $con->prepare("SELECT id FROM usuario WHERE cpf = :cpf LIMIT 1");
                    $stmt->execute(['cpf' => $cpf]);
                    if ($stmt->fetch()) {
                        return $this->SendJson($response, [
                            'status' => false,
                            'msg' => 'CPF já cadastrado'
                        ], 409);
                    }
                }

                // Inserir novo usuário
                $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
                $stmt = $con->prepare("
                    INSERT INTO usuario (nome, email, cpf, celular, senha, ativo, administrador) 
                    VALUES (:nome, :email, :cpf, :celular, :senha, :ativo, :admin)
                ");

                $stmt->execute([
                    ':nome' => $nome,
                    ':email' => $email,
                    ':cpf' => !empty($cpf) ? $cpf : null,
                    ':celular' => !empty($celular) ? $celular : null,
                    ':senha' => $senhaHash,
                    ':ativo' => 1,
                    ':admin' => 0
                ]);

                error_log("[LOGIN][precadastro] Novo usuário criado: $email");

                return $this->SendJson($response, [
                    'status' => true,
                    'msg' => 'Pré-cadastro realizado com sucesso! Você pode fazer login agora.'
                ], 201);
            } catch (\PDOException $e) {
                error_log('[LOGIN][precadastro] Erro BD: ' . $e->getMessage());
                return $this->SendJson($response, [
                    'status' => false,
                    'msg' => 'Erro ao salvar dados: ' . $e->getMessage()
                ], 500);
            }
        } catch (\Exception $e) {
            error_log('[LOGIN][precadastro] Erro geral: ' . $e->getMessage());
            return $this->SendJson($response, [
                'status' => false,
                'msg' => 'Erro: ' . $e->getMessage()
            ], 500);
        }
    }

    // Envia código de verificação para um contato (email ou celular)
    public function enviarCodigoContato($request, $response)
    {
        try {
            $form = $request->getParsedBody();
            if (empty($form)) {
                $json = json_decode((string) $request->getBody(), true);
                $form = $json ?? [];
            }
            $tipo = $form['tipo'] ?? '';
            $contato = trim($form['contato'] ?? '');

            if (empty($tipo) || empty($contato) || !in_array($tipo, ['email', 'celular'])) {
                return $this->SendJson($response, ['status' => false, 'msg' => 'Tipo ou contato inválido'], 400);
            }

            $fileFallback = false;
            try {
                $con = \app\database\Connection::connection();
                error_log('[LOGIN][enviarCodigoContato] Connected to DB');

                // Verifica se já existe contato (evita envio para contatos já registrados)
                $chkSql = $tipo === 'email' ? "LOWER(email) = LOWER(:contato)" : "regexp_replace(celular, '\\D', '', 'g') = :contato";
                $chk = $con->prepare("SELECT id FROM vw_usuario_contatos WHERE " . $chkSql . " LIMIT 1");
                $contatoParam = $tipo === 'email' ? $contato : preg_replace('/\D+/', '', $contato);
                $chk->execute(['contato' => $contatoParam]);
                error_log('[LOGIN][enviarCodigoContato] chk executed');
                if ($chk->fetch()) {
                    error_log('[LOGIN][enviarCodigoContato] contato já cadastrado: ' . $contatoParam);
                    return $this->SendJson($response, ['status' => false, 'msg' => ucfirst($tipo) . ' já cadastrado'], 409);
                }

                // Rate limiting: 1 por minuto e max 5 por 24h
                $recentMin = $con->prepare("SELECT COUNT(*) AS cnt FROM verificacao_contato WHERE tipo = :tipo AND contato = :contato AND codigo_gerado_em > (NOW() - INTERVAL '1 MINUTE')");
                $recentMin->execute(['tipo' => $tipo, 'contato' => $contatoParam]);
                $cntMin = intval($recentMin->fetchColumn());
                if ($cntMin > 0) {
                    return $this->SendJson($response, ['status' => false, 'msg' => 'Aguarde 60 segundos antes de solicitar novo código'], 429);
                }

                $recentDay = $con->prepare("SELECT COUNT(*) AS cnt FROM verificacao_contato WHERE tipo = :tipo AND contato = :contato AND codigo_gerado_em > (NOW() - INTERVAL '24 HOURS')");
                $recentDay->execute(['tipo' => $tipo, 'contato' => $contatoParam]);
                $cntDay = intval($recentDay->fetchColumn());
                if ($cntDay >= 5) {
                    return $this->SendJson($response, ['status' => false, 'msg' => 'Limite de envios diário atingido'], 429);
                }

                $codigo = strval(rand(100000, 999999));
                $now = date('Y-m-d H:i:s');
                $stmt = $con->prepare("INSERT INTO verificacao_contato (tipo, contato, codigo, codigo_gerado_em, usado, data_cadastro) VALUES (:tipo, :contato, :codigo, :agora, false, NOW())");
                $stmt->execute(['tipo' => $tipo, 'contato' => $contatoParam, 'codigo' => $codigo, 'agora' => $now]);
            } catch (\Exception $e) {
                error_log('[LOGIN][enviarCodigoContato] DB unavailable, using file fallback: ' . $e->getMessage());
                $fileFallback = true;
                $contatoParam = $tipo === 'email' ? $contato : preg_replace('/\D+/', '', $contato);

                // Verifica duplicatas em arquivo de usuários
                $usersFile = __DIR__ . '/../../data/usuarios.json';
                if (file_exists($usersFile)) {
                    $t = file_get_contents($usersFile);
                    $users = $t ? json_decode($t, true) ?? [] : [];
                    foreach ($users as $u) {
                        if ($tipo === 'email' && isset($u['email']) && strtolower($u['email']) === strtolower($contatoParam)) {
                            return $this->SendJson($response, ['status' => false, 'msg' => 'Email já cadastrado'], 409);
                        }
                        if ($tipo === 'celular' && isset($u['contatos']) && in_array($contatoParam, $u['contatos'])) {
                            return $this->SendJson($response, ['status' => false, 'msg' => 'Celular já cadastrado'], 409);
                        }
                    }
                }

                $codigo = strval(rand(100000, 999999));
                $now = date('Y-m-d H:i:s');

                $file = __DIR__ . '/../../data/verificacoes.json';
                if (!is_dir(dirname($file)))
                    @mkdir(dirname($file), 0755, true);
                $arr = [];
                if (file_exists($file)) {
                    $txt = file_get_contents($file);
                    $arr = $txt ? json_decode($txt, true) ?? [] : [];
                }
                $id = (count($arr) ? intval($arr[count($arr) - 1]['id']) : 0) + 1;
                $arr[] = ['id' => $id, 'tipo' => $tipo, 'contato' => $contatoParam, 'codigo' => $codigo, 'codigo_gerado_em' => $now, 'usado' => false];
                file_put_contents($file, json_encode($arr));
            }

            if ($tipo === 'email') {
                $mailer = new \app\source\Email();
                $body = "Seu código de verificação é <strong>{$codigo}</strong>. Ele expira em 15 minutos.";
                $sent = $mailer->add('Verificação de e-mail', $body, $contato, $contato)->send();
                if (!$sent) {
                    $err = $mailer->error();
                    if ($fileFallback) {
                        error_log('[LOGIN][enviarCodigoContato] Email send failed but using file fallback, continuing: ' . ($err ? $err->getMessage() : 'unknown'));
                    } else {
                        return $this->SendJson($response, ['status' => false, 'msg' => 'Erro ao enviar e-mail: ' . ($err ? $err->getMessage() : '')], 500);
                    }
                }
            } else {
                $sms = new \app\source\Sms();
                $sent = $sms->add($contatoParam, "Seu código de verificação: {$codigo}")->send();
                if (!$sent) {
                    $err = $sms->error();
                    if ($fileFallback) {
                        error_log('[LOGIN][enviarCodigoContato] SMS send failed but using file fallback, continuing: ' . ($err ? $err->getMessage() : 'unknown'));
                    } else {
                        return $this->SendJson($response, ['status' => false, 'msg' => 'Erro ao enviar SMS: ' . ($err ? $err->getMessage() : '')], 500);
                    }
                }
            }

            return $this->SendJson($response, ['status' => true, 'msg' => 'Código enviado.'], 200);
        } catch (\Exception $e) {
            return $this->SendJson($response, ['status' => false, 'msg' => $e->getMessage()], 500);
        }
    }

    // Confirma código de verificação enviado ao contato
    public function confirmarCodigoContato($request, $response)
    {
        try {
            $form = $request->getParsedBody();
            if (empty($form)) {
                $json = json_decode((string) $request->getBody(), true);
                $form = $json ?? [];
            }
            $tipo = $form['tipo'] ?? '';
            $contato = trim($form['contato'] ?? '');
            $codigo = trim($form['codigo'] ?? '');

            if (empty($tipo) || empty($contato) || empty($codigo) || !in_array($tipo, ['email', 'celular'])) {
                return $this->SendJson($response, ['status' => false, 'msg' => 'Dados inválidos'], 400);
            }

            try {
                $con = \app\database\Connection::connection();
                $contatoParam = $tipo === 'email' ? $contato : preg_replace('/\D+/', '', $contato);
                $stmt = $con->prepare("SELECT * FROM verificacao_contato WHERE tipo = :tipo AND contato = :contato AND codigo = :codigo AND usado = false ORDER BY codigo_gerado_em DESC LIMIT 1");
                $stmt->execute(['tipo' => $tipo, 'contato' => $contatoParam, 'codigo' => $codigo]);
                $row = $stmt->fetch();

                // Bloqueio por tentativas inválidas: max 5 por hora
                $chkAttempts = $con->prepare("SELECT COUNT(*) FROM verificacao_tentativas WHERE tipo = :tipo AND contato = :contato AND sucesso = false AND criado_em > (NOW() - INTERVAL '1 HOUR')");
                $chkAttempts->execute(['tipo' => $tipo, 'contato' => $contatoParam]);
                if (intval($chkAttempts->fetchColumn()) >= 5) {
                    return $this->SendJson($response, ['status' => false, 'msg' => 'Muitas tentativas inválidas. Tente mais tarde'], 429);
                }

                if (!$row) {
                    // registra tentativa inválida
                    $insTry = $con->prepare("INSERT INTO verificacao_tentativas (tipo, contato, sucesso, criado_em) VALUES (:tipo, :contato, false, NOW())");
                    $insTry->execute(['tipo' => $tipo, 'contato' => $contatoParam]);

                    return $this->SendJson($response, ['status' => false, 'msg' => 'Código inválido ou expirado'], 403);
                }

                $generated = $row['codigo_gerado_em'] ?? null;
                if ($generated && (strtotime($generated) + 15 * 60) < time()) {
                    // registra tentativa inválida por expiração
                    $insTry = $con->prepare("INSERT INTO verificacao_tentativas (tipo, contato, sucesso, criado_em) VALUES (:tipo, :contato, false, NOW())");
                    $insTry->execute(['tipo' => $tipo, 'contato' => $contatoParam]);

                    return $this->SendJson($response, ['status' => false, 'msg' => 'Código expirado'], 403);
                }

                $upd = $con->prepare("UPDATE verificacao_contato SET usado = true WHERE id = :id");
                $upd->execute(['id' => $row['id']]);

                // registra tentativa de sucesso
                $insOk = $con->prepare("INSERT INTO verificacao_tentativas (tipo, contato, sucesso, criado_em) VALUES (:tipo, :contato, true, NOW())");
                $insOk->execute(['tipo' => $tipo, 'contato' => $contatoParam]);

                return $this->SendJson($response, ['status' => true, 'msg' => 'Contato verificado com sucesso'], 200);
            } catch (\Exception $e) {
                error_log('[LOGIN][confirmarCodigoContato] DB unavailable, using file fallback: ' . $e->getMessage());
                $contatoParam = $tipo === 'email' ? $contato : preg_replace('/\D+/', '', $contato);

                $file = __DIR__ . '/../../data/verificacoes.json';
                $arr = [];
                if (file_exists($file)) {
                    $txt = file_get_contents($file);
                    $arr = $txt ? json_decode($txt, true) ?? [] : [];
                }

                // Bloqueio por tentativas inválidas: max 5 por hora (arquivo de tentativas)
                $tryFile = __DIR__ . '/../../data/verificacoes_tentativas.json';
                $tries = [];
                if (file_exists($tryFile)) {
                    $t = file_get_contents($tryFile);
                    $tries = $t ? json_decode($t, true) ?? [] : [];
                }
                $cntInvalid = 0;
                $cut = time() - 3600;
                foreach ($tries as $tr) {
                    if ($tr['tipo'] === $tipo && $tr['contato'] === $contatoParam && !$tr['sucesso'] && strtotime($tr['criado_em']) > $cut)
                        $cntInvalid++;
                }
                if ($cntInvalid >= 5) {
                    return $this->SendJson($response, ['status' => false, 'msg' => 'Muitas tentativas inválidas. Tente mais tarde'], 429);
                }

                $found = null;
                for ($i = count($arr) - 1; $i >= 0; $i--) {
                    $item = $arr[$i];
                    if ($item['tipo'] === $tipo && $item['contato'] === $contatoParam && $item['codigo'] === $codigo && $item['usado'] === false) {
                        $found = $item;
                        break;
                    }
                }

                if (!$found) {
                    $tries[] = ['tipo' => $tipo, 'contato' => $contatoParam, 'sucesso' => false, 'criado_em' => date('Y-m-d H:i:s')];
                    file_put_contents($tryFile, json_encode($tries));
                    return $this->SendJson($response, ['status' => false, 'msg' => 'Código inválido ou expirado'], 403);
                }

                $generated = $found['codigo_gerado_em'] ?? null;
                if ($generated && (strtotime($generated) + 15 * 60) < time()) {
                    $tries[] = ['tipo' => $tipo, 'contato' => $contatoParam, 'sucesso' => false, 'criado_em' => date('Y-m-d H:i:s')];
                    file_put_contents($tryFile, json_encode($tries));
                    return $this->SendJson($response, ['status' => false, 'msg' => 'Código expirado'], 403);
                }

                // marca como usado
                for ($i = count($arr) - 1; $i >= 0; $i--) {
                    if ($arr[$i]['id'] === $found['id']) {
                        $arr[$i]['usado'] = true;
                        break;
                    }
                }
                file_put_contents($file, json_encode($arr));

                $tries[] = ['tipo' => $tipo, 'contato' => $contatoParam, 'sucesso' => true, 'criado_em' => date('Y-m-d H:i:s')];
                file_put_contents($tryFile, json_encode($tries));

                return $this->SendJson($response, ['status' => true, 'msg' => 'Contato verificado com sucesso'], 200);
            }
        } catch (\Exception $e) {
            return $this->SendJson($response, ['status' => false, 'msg' => $e->getMessage()], 500);
        }
    }

    // Autenticação de login
    use Template;

    public function autenticar($request, $response)
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
            $con = \app\database\Connection::connection();

            $loginLower = strtolower($login);
            $loginCel = preg_replace('/\D+/', '', $login);

            $stmt = $con->prepare("
                SELECT *
                FROM vw_usuario_contatos
                WHERE LOWER(email) = :email
                   OR regexp_replace(celular, '\\D', '', 'g') = :celular
                   OR cpf = :cpf
                LIMIT 1
            ");

            $stmt->execute([
                'email' => $loginLower,
                'celular' => $loginCel,
                'cpf' => $login
            ]);

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($senha, $user['senha'])) {
                return $this->SendJson($response, [
                    'status' => false,
                    'msg' => 'Usuário ou senha inválidos'
                ], 401);
            }

            if (!$user['ativo']) {
                return $this->SendJson($response, [
                    'status' => false,
                    'msg' => 'Usuário inativo'
                ], 403);
            }

            $_SESSION['usuario'] = [
                'logado' => true,
                'id' => $user['id'],
                'nome' => $user['nome'],
                'email' => $user['email'],
                'administrador' => (bool) ($user['administrador'] ?? false),
                'ativo' => (bool) ($user['ativo'] ?? true)
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

    // Envia código de verificação para o e-mail informado (se existir)
    public function recuperarSenha($request, $response)
    {
        try {
            $form = $request->getParsedBody();
            if (empty($form)) {
                $json = json_decode((string) $request->getBody(), true);
                $form = $json ?? [];
            }
            $email = $form['email'] ?? '';
            if (empty($email)) {
                return $this->SendJson($response, ['success' => false, 'message' => 'Email não informado'], 400);
            }

            $user = SelectQuery::select()->from('vw_usuario_contatos')->where('email', '=', $email)->fetch();

            // Por segurança, retornamos a mesma mensagem mesmo que o e-mail não exista
            if (!$user) {
                return $this->SendJson($response, ['success' => true, 'message' => 'Se o e-mail existir, você receberá instruções para recuperar a senha.']);
            }

            $codigo = strval(rand(100000, 999999));
            $now = date('Y-m-d H:i:s');
            UpdateQuery::table('usuario')->set(['codigo_verificacao' => $codigo, 'codigo_gerado_em' => $now])->where('id', '=', $user['id'])->update();

            error_log("[RECUPERAR SENHA] Código gerado para {$email}: {$codigo}");

            // Retorna sucesso (código já foi salvo no BD)
            // Em ambiente de desenvolvimento, retorna o código para teste
            $isDev = php_sapi_name() === 'cli-server';
            $resp = ['success' => true, 'message' => 'Se o e-mail existir, você receberá instruções para recuperar a senha.'];
            if ($isDev) {
                $resp['codigo_teste'] = $codigo; // Apenas para teste/desenvolvimento
            }
            return $this->SendJson($response, $resp);
        } catch (\Exception $e) {
            return $this->SendJson($response, ['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }

    // Valida o código e redefine a senha
    public function validarCodigo($request, $response)
    {
        try {
            $form = $request->getParsedBody();
            if (empty($form)) {
                $json = json_decode((string) $request->getBody(), true);
                $form = $json ?? [];
            }
            $codigo = $form['codigo'] ?? '';
            $senha = $form['senha'] ?? '';

            if (empty($codigo) || empty($senha)) {
                return $this->SendJson($response, ['success' => false, 'message' => 'Código ou senha não informados'], 400);
            }

            $user = SelectQuery::select()->from('usuario')->where('codigo_verificacao', '=', $codigo)->fetch();

            if (!$user) {
                return $this->SendJson($response, ['success' => false, 'message' => 'Código inválido'], 403);
            }

            // Verifica expiração (15 minutos)
            $generated = $user['codigo_gerado_em'] ?? null;
            if ($generated && (strtotime($generated) + 15 * 60) < time()) {
                return $this->SendJson($response, ['success' => false, 'message' => 'Código expirado'], 403);
            }
            UpdateQuery::table('usuario')->set([
                'senha' => password_hash($senha, PASSWORD_DEFAULT),
                'codigo_verificacao' => null,
                'codigo_gerado_em' => null
            ])->where('id', '=', $user['id'])->update();
            return $this->SendJson($response, ['success' => true, 'message' => 'Senha atualizada com sucesso']);
        } catch (\Exception $e) {
            return $this->SendJson($response, ['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }
}
