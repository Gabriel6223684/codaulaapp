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
    public function login($request, $response)
    {
        try {
            $dadosTemplate = ['titulo' => 'Autenticação'];
            return $this->getTwig()
                ->render($response, $this->setView('login'), $dadosTemplate)
                ->withHeader('Content-Type', 'text/html')
                ->withStatus(200);
        } catch (\Exception $e) {
            return $this->SendJson($response, ['status' => false, 'msg' => $e->getMessage()], 500);
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
            // Fallback para quando o body vem como JSON (por exemplo fetch com application/json)
            if (empty($form)) {
                $json = json_decode((string) $request->getBody(), true);
                $form = $json ?? [];
            }

            // Log para depuração (não exponha em produção)
            $remoteIp = $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown';
            $ct = $request->getHeaderLine('Content-Type');
            $logBody = $form;
            if (isset($logBody['senhaCadastro']))
                $logBody['senhaCadastro'] = '***';
            error_log("[LOGIN][precadastro] IP: $remoteIp CT: $ct BODY: " . json_encode($logBody));

            if (empty($form['nome']) || empty($form['email']) || empty($form['senhaCadastro'])) {
                return $this->SendJson($response, [
                    'status' => false,
                    'msg' => 'Preencha todos os campos'
                ], 400);
            }

            $fileFallback = false;
            try {
                $con = \app\database\Connection::connection();
                // Normaliza e valida e-mail e celular
                $email = strtolower(trim($form['email']));
                $celularInput = preg_replace('/\D+/', '', $form['celular'] ?? '');

                // Verifica se e-mail ou celular já existem na view
                $stmt = $con->prepare("SELECT id, email, celular FROM vw_usuario_contatos WHERE email = :email OR celular = :celular LIMIT 1");
                $stmt->execute(['email' => $email, 'celular' => $celularInput]);
                $existe = $stmt->fetch();

                if ($existe) {
                    $msg = 'E-mail já cadastrado';
                    if (!empty($celularInput) && !empty($existe['celular']) && preg_replace('/\D+/', '', $existe['celular']) === $celularInput) {
                        $msg = 'Celular já cadastrado';
                    }
                    return $this->SendJson($response, [
                        'status' => false,
                        'msg' => $msg
                    ], 409);
                }

                // Verifica se o e-mail/celular foram verificados via código
                $verEmail = $con->prepare("SELECT id FROM verificacao_contato WHERE tipo = 'email' AND contato = :email AND usado = true AND codigo_gerado_em > (NOW() - INTERVAL '24 HOURS') LIMIT 1");
                $verEmail->execute(['email' => $email]);
                if (!$verEmail->fetch()) {
                    return $this->SendJson($response, ['status' => false, 'msg' => 'E-mail não verificado'], 400);
                }

                if (!empty($celularInput)) {
                    $verCel = $con->prepare("SELECT id FROM verificacao_contato WHERE tipo = 'celular' AND contato = :celular AND usado = true AND codigo_gerado_em > (NOW() - INTERVAL '24 HOURS') LIMIT 1");
                    $verCel->execute(['celular' => $celularInput]);
                    if (!$verCel->fetch()) {
                        return $this->SendJson($response, ['status' => false, 'msg' => 'Celular não verificado'], 400);
                    }
                }

                // Insere usuario e contatos usando Connection
                $con->beginTransaction();
                $senhaHash = password_hash($form['senhaCadastro'], PASSWORD_DEFAULT);
                $stmt = $con->prepare("INSERT INTO usuario (nome, senha, ativo) VALUES (:nome, :senha, :ativo) RETURNING id");
                $stmt->execute(['nome' => $form['nome'], 'senha' => $senhaHash, 'ativo' => true]);
                $userId = $stmt->fetchColumn();

                $contactStmt = $con->prepare("INSERT INTO contato (id_usuario, tipo, contato, data_cadastro, data_alteracao) VALUES (:id_usuario, :tipo, :contato, NOW(), NOW())");
                $contactStmt->execute(['id_usuario' => $userId, 'tipo' => 'email', 'contato' => $email]);
                if (!empty($celularInput)) {
                    $contactStmt->execute(['id_usuario' => $userId, 'tipo' => 'celular', 'contato' => $celularInput]);
                }
                $con->commit();
            } catch (\Exception $e) {
                error_log('[LOGIN][precadastro] DB unavailable, using file fallback: ' . $e->getMessage());
                $fileFallback = true;
                $emailLower = strtolower(trim($form['email']));
                $contatoCel = $celularInput;

                // Verifica duplicatas em arquivo
                $usersFile = __DIR__ . '/../../data/usuarios.json';
                $users = [];
                if (file_exists($usersFile)) {
                    $t = file_get_contents($usersFile);
                    $users = $t ? json_decode($t, true) ?? [] : [];
                }
                foreach ($users as $u) {
                    if (isset($u['email']) && strtolower($u['email']) === $emailLower) {
                        return $this->SendJson($response, ['status' => false, 'msg' => 'E-mail já cadastrado'], 409);
                    }
                    if (!empty($contatoCel) && isset($u['contatos']) && in_array($contatoCel, $u['contatos'])) {
                        return $this->SendJson($response, ['status' => false, 'msg' => 'Celular já cadastrado'], 409);
                    }
                }

                // Verifica se email/celular foram verificados via arquivo
                $verFile = __DIR__ . '/../../data/verificacoes.json';
                $verArr = [];
                if (file_exists($verFile)) {
                    $t = file_get_contents($verFile);
                    $verArr = $t ? json_decode($t, true) ?? [] : [];
                }
                $checkedEmail = false;
                foreach (array_reverse($verArr) as $v) {
                    if ($v['tipo'] === 'email' && strtolower($v['contato']) === $emailLower && $v['usado'] === true && (strtotime($v['codigo_gerado_em']) + 24 * 3600) > time()) {
                        $checkedEmail = true;
                        break;
                    }
                }
                if (!$checkedEmail) {
                    return $this->SendJson($response, ['status' => false, 'msg' => 'E-mail não verificado'], 400);
                }
                if (!empty($contatoCel)) {
                    $checkedCel = false;
                    foreach (array_reverse($verArr) as $v) {
                        if ($v['tipo'] === 'celular' && preg_replace('/\D+/', '', $v['contato']) === $contatoCel && $v['usado'] === true && (strtotime($v['codigo_gerado_em']) + 24 * 3600) > time()) {
                            $checkedCel = true;
                            break;
                        }
                    }
                    if (!$checkedCel) {
                        return $this->SendJson($response, ['status' => false, 'msg' => 'Celular não verificado'], 400);
                    }
                }

                // Inserir usuário no arquivo
                $id = (count($users) ? intval($users[count($users) - 1]['id']) : 0) + 1;
                $senhaHash = password_hash($form['senhaCadastro'], PASSWORD_DEFAULT);
                $new = ['id' => $id, 'nome' => $form['nome'], 'email' => $emailLower, 'senha' => $senhaHash, 'ativo' => true, 'contatos' => [$emailLower]];
                if (!empty($contatoCel))
                    $new['contatos'][] = $contatoCel;
                $users[] = $new;
                file_put_contents($usersFile, json_encode($users));
            }

            return $this->SendJson($response, [
                'status' => true,
                'msg' => 'Cadastro realizado com sucesso'
            ], 201);
        } catch (\Exception $e) {
            return $this->SendJson($response, [
                'status' => false,
                'msg' => $e->getMessage()
            ], 500);
        }

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
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

    public function autenticar(ServerRequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        session_start(); // ✅ indispensável para sessão

        $data = $request->getParsedBody(); // pega POST form ou JSON

        $login = $data['login'] ?? '';
        $senha = $data['senha'] ?? '';

        if (empty($login) || empty($senha)) {
            return $this->SendJson($response, [
                'status' => false,
                'msg' => 'Informe login e senha.'
            ], 400);
        }

        try {
            // 🔹 Conexão com banco
            $pdo = new PDO("pgsql:host=localhost;port=5432;dbname=seubanco", "usuario", "senha");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // 🔹 Consulta usuário
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :login OR telefone = :login LIMIT 1");
            $stmt->execute(['login' => $login]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($senha, $user['senha'])) {
                return $this->SendJson($response, [
                    'status' => false,
                    'msg' => 'Usuário ou senha inválidos.'
                ], 401);
            }

            if (!$user['ativo']) {
                return $this->SendJson($response, [
                    'status' => false,
                    'msg' => 'Usuário inativo. Contate o administrador.'
                ], 403);
            }

            // 🔹 Cria sessão
            $_SESSION['usuario'] = [
                'logado' => true,
                'id' => $user['id'],
                'nome' => $user['nome'],
                'email' => $user['email']
            ];

            // 🔹 Retorna sucesso em JSON
            return $this->SendJson($response, [
                'status' => true,
                'msg' => 'Login realizado com sucesso.'
            ], 200);

        } catch (\PDOException $e) {
            return $this->SendJson($response, [
                'status' => false,
                'msg' => 'Erro no servidor: ' . $e->getMessage()
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

            $body = "Olá {$user['nome']},<br><br>Utilize o código a seguir para redefinir sua senha: <strong>{$codigo}</strong><br><br>Se você não solicitou, ignore este e-mail.";

            $mailer = new \app\source\Email();
            $sent = $mailer->add('Recuperação de senha', $body, $user['nome'], $email)->send();

            if (!$sent) {
                $err = $mailer->error();
                return $this->SendJson($response, ['success' => false, 'message' => 'Erro ao enviar e-mail.' . ($err ? ' ' . $err->getMessage() : '')], 500);
            }

            return $this->SendJson($response, ['success' => true, 'message' => 'Se o e-mail existir, você receberá instruções para recuperar a senha.']);
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