
<?php

namespace app\controller;

<<<<<<< Updated upstream
use App\Database\Builder\UpdateQuery;
use App\Database\Builder\SelectQuery;
use App\Database\Builder\InsertQuery;
use App\Traits\Template;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PDO;
=======
use app\database\builder\InsertQuery;
use app\database\builder\SelectQuery;
use app\database\builder\UpdateQuery;
>>>>>>> Stashed changes

class Login extends Base
{
    public function login($request, $response)
    {
<<<<<<< Updated upstream
        return $this->getTwig()->render(
            $response,
            $this->setView('login'),
            ['titulo' => 'Autenticação']
        );
    }

    // Health check endpoint to test server responses
    public function ping($request, $response)
    {
        try {
            $server = [
                'php_sapi' => PHP_SAPI,
                'time' => date('c')
=======
        try {
            $dadosTemplate = [
                'titulo' => 'Autenticação'
>>>>>>> Stashed changes
            ];
            return $this->getTwig()
                ->render($response, $this->setView('login'), $dadosTemplate)
                ->withHeader('Content-Type', 'text/html')
                ->withStatus(200);
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            die;
        }
    }
<<<<<<< Updated upstream

    // Pré-cadastro de usuários
=======
>>>>>>> Stashed changes
    public function precadastro($request, $response)
    {
        try {
            #Captura os dados do form
            $form = $request->getParsedBody();
            #Capturar os dados do usuário.
            $dadosUsuario = [
                'nome' => $form['nome'],
                'sobrenome' => $form['sobrenome'],
                'cpf' => $form['cpf'],
                'rg' => $form['rg'],
                'senha' => password_hash($form['senhaCadastro'], PASSWORD_DEFAULT)
            ];
            $IsInseted = InsertQuery::table('usuario')->save($dadosUsuario);
            if (!$IsInseted) {
                return $this->SendJson(
                    $response,
                    ['status' => false, 'msg' => 'Restrição: ' . $IsInseted, 'id' => 0],
                    403
                );
            }
            #Captura o código do ultimo usuário cadastrado na tabela de usuário
            $id = SelectQuery::select('id')->from('usuario')->order('id', 'desc')->fetch();
            #Colocamos o ID do ultimo usuário cadastrado na varaivel $id_usuario.
            $id_usuario = $id['id'];
            #Inserimos o e-mail
            $dadosContato = [
                'id_usuario' => $id_usuario,
                'tipo' => 'email',
                'contato' => $form['email']
            ];
            InsertQuery::table('contato')->save($dadosContato);
            $dadosContato = [];
            #Inserimos o celular
            $dadosContato = [
                'id_usuario' => $id_usuario,
                'tipo' => 'celular',
                'contato' => $form['celular']
            ];
            InsertQuery::table('contato')->save($dadosContato);
            $dadosContato = [];
            #Inserimos o WhastaApp
            $dadosContato = [
                'id_usuario' => $id_usuario,
                'tipo' => 'whatsapp',
                'contato' => $form['whatsapp']
            ];
            InsertQuery::table('contato')->save($dadosContato);
            return $this->SendJson($response, ['status' => true, 'msg' => 'Cadastro realizado com sucesso!', 'id' => $id_usuario], 201);
        } catch (\Exception $e) {
            return $this->SendJson($response, ['status' => true, 'msg' => 'Restrição: ' . $e->getMessage(), 'id' => 0], 500);
        }
    }
<<<<<<< Updated upstream

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

            if (empty($tipo) || empty($contato) || !in_array($tipo, ['email', 'telefone'])) {
                return $this->SendJson($response, ['status' => false, 'msg' => 'Tipo ou contato inválido'], 400);
            }

            $fileFallback = false;
            try {
                $con = \app\database\Connection::connection();
                error_log('[LOGIN][enviarCodigoContato] Connected to DB');

                // Verifica se já existe contato (evita envio para contatos já registrados)
                $chkSql = $tipo === 'email' ? "LOWER(email) = LOWER(:contato)" : "regexp_replace(telefone, '\\D', '', 'g') = :contato";
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
                        if ($tipo === 'celular' && isset($u['telefone']) && in_array($contatoParam, $u['contatos'])) {
                            return $this->SendJson($response, ['status' => false, 'msg' => 'Telefone já cadastrado'], 409);
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

            if (empty($tipo) || empty($contato) || empty($codigo) || !in_array($tipo, ['email', 'telefone'])) {
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
    public function autenticar($request, $response)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $data = $request->getParsedBody();
        if (empty($data)) {
            $data = json_decode((string)$request->getBody(), true) ?? [];
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
                FROM usuario
                WHERE LOWER(email) = :login
                   OR cpf = :cpf
                   OR telefone = :tel
                LIMIT 1
            ");

            $stmt->execute([
                ':login' => $loginLower,
                ':cpf'   => $loginCel,
                ':tel'   => $loginCel
            ]);

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($senha, $user['senha'])) {
                return $this->SendJson($response, [
                    'status' => false,
                    'msg' => 'Usuário ou senha inválidos'
                ], 401);
            }

=======
    public function autenticar($request, $response)
    {
        try {
            #Captura os dados do form
            $form = $request->getParsedBody();
            #Caso a posição login não exista, informa a ocorrencia de erro.
            if (!isset($form['login']) || empty($form['login'])) {
                return $this->SendJson($response, ['status' => false, 'msg' => 'Por favor informe o login', 'id' => 0], 403);
            }
            #Caso a posição login não exista, informa a ocorrencia de erro.
            if (!isset($form['senha']) || empty($form['senha'])) {
                return $this->SendJson($response, ['status' => false, 'msg' => 'Por favor informe o senha', 'id' => 0], 403);
            }
            $user = SelectQuery::select()
                ->from('vw_usuario_contatos')
                ->where('cpf', '=', $form['login'], 'or')
                ->where('email', '=', $form['login'], 'or')
                ->where('celular', '=', $form['login'], 'or')
                ->where('whatsapp', '=', $form['login'])
                ->fetch();
            if (!isset($user) || empty($user) || count($user) <= 0) {
                return $this->SendJson(
                    $response,
                    ['status' => false, 'msg' => 'Usuário ou senha inválidos!', 'id' => 0],
                    403
                );
            }
            if (!$user['ativo']) {
                return $this->SendJson(
                    $response,
                    ['status' => false, 'msg' => 'Por enquanto você ainda não tem permissão de acessar o sistema!', 'id' => 0],
                    403
                );
            }
            if (!password_verify($form['senha'], $user['senha'])) {
                return $this->SendJson(
                    $response,
                    ['status' => false, 'msg' => 'Usuário ou senha inválidos!', 'id' => 0],
                    403
                );
            }

            if (password_needs_rehash($user['senha'], PASSWORD_DEFAULT)) {
                UpdateQuery::table('usuario')->set(['senha' => password_hash($form['senha'], PASSWORD_DEFAULT)])->where('id', '=', $user['id'])->update();
            }

>>>>>>> Stashed changes
            $_SESSION['usuario'] = [
                'id' => $user['id'],
                'nome' => $user['nome'],
<<<<<<< Updated upstream
                'email' => $user['email']
=======
                'sobrenome' => $user['sobrenome'],
                'cpf' => $user['cpf'],
                'rg' => $user['rg'],
                'ativo' => $user['ativo'],
                'logado' => true,
                'administrador' => $user['administrador'],
                'celular' => $user['celular'],
                'email' => $user['email'],
                'whatsapp' => $user['whatsapp'],
                'data_cadastro' => $user['data_cadastro'],
                'data_alteracao' => $user['data_alteracao'],
>>>>>>> Stashed changes
            ];

            return $this->SendJson(
                $response,
                ['status' => true, 'msg' => 'Seja bem-vindo de volta!', 'id' => $user['id']],
                200
            );
        } catch (\Exception $e) {
            return $this->SendJson($response, ['status' => false, 'msg' => 'Restrição: ' . $e->getMessage(), 'id' => 0], 500);
        }
    }
}
