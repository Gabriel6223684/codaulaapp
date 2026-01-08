<?php

namespace app\controller;

use app\database\builder\InsertQuery;
use app\database\builder\SelectQuery;
use app\database\builder\UpdateQuery;

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
                $json = json_decode((string)$request->getBody(), true);
                $form = $json ?? [];
            }

            // Log para depuração (não exponha em produção)
            $remoteIp = $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown';
            $ct = $request->getHeaderLine('Content-Type');
            $logBody = $form;
            if (isset($logBody['senhaCadastro'])) $logBody['senhaCadastro'] = '***';
            error_log("[LOGIN][precadastro] IP: $remoteIp CT: $ct BODY: " . json_encode($logBody));

            if (empty($form['nome']) || empty($form['email']) || empty($form['senhaCadastro'])) {
                return $this->SendJson($response, [
                    'status' => false,
                    'msg' => 'Preencha todos os campos'
                ], 400);
            }

            $existe = SelectQuery::select('id')
                ->from('usuario')
                ->where('email', '=', $form['email'])
                ->fetch();

            if ($existe) {
                return $this->SendJson($response, [
                    'status' => false,
                    'msg' => 'E-mail já cadastrado'
                ], 409);
            }

            InsertQuery::table('usuario')->save([
                'nome' => $form['nome'],
                'email' => $form['email'],
                'senha' => password_hash($form['senhaCadastro'], PASSWORD_DEFAULT),
                'ativo' => true
            ]);

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


    // Autenticação de login
    public function autenticar($request, $response)
    {
        try {
            $form = $request->getParsedBody();
            if (empty($form)) {
                $json = json_decode((string)$request->getBody(), true);
                $form = $json ?? [];
            }

            // Log para depuração (não exponha em produção)
            $remoteIp = $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown';
            $ct = $request->getHeaderLine('Content-Type');
            $logBody = $form;
            if (isset($logBody['senha'])) $logBody['senha'] = '***';
            error_log("[LOGIN][autenticar] IP: $remoteIp CT: $ct BODY: " . json_encode($logBody));

            if (empty($form['login']) || empty($form['senha'])) {
                return $this->SendJson($response, [
                    'status' => false,
                    'msg' => 'Informe login e senha'
                ], 400);
            }

            $user = SelectQuery::select()
                ->from('usuario')
                ->where('email', '=', $form['login'])
                ->fetch();

            if (!$user) {
                return $this->SendJson($response, [
                    'status' => false,
                    'msg' => 'Usuário ou senha inválidos'
                ], 403);
            }

            if (!password_verify($form['senha'], $user['senha'])) {
                return $this->SendJson($response, [
                    'status' => false,
                    'msg' => 'Usuário ou senha inválidos'
                ], 403);
            }

            if (!$user['ativo']) {
                return $this->SendJson($response, [
                    'status' => false,
                    'msg' => 'Usuário inativo'
                ], 403);
            }

            $_SESSION['usuario'] = [
                'id' => $user['id'],
                'nome' => $user['nome'],
                'email' => $user['email'],
                'logado' => true,
                'ativo' => !empty($user['ativo']) ? (bool)$user['ativo'] : true,
                'administrador' => isset($user['administrador']) ? (bool)$user['administrador'] : false
            ];

            return $this->SendJson($response, [
                'status' => true,
                'msg' => 'Login realizado com sucesso',
                'id' => $user['id']
            ], 200);
        } catch (\Exception $e) {
            return $this->SendJson($response, [
                'status' => false,
                'msg' => $e->getMessage()
            ], 500);
        }
    }


    // Envia código de verificação para o e-mail informado (se existir)
    public function recuperarSenha($request, $response)
    {
        try {
            $form = $request->getParsedBody();
            if (empty($form)) {
                $json = json_decode((string)$request->getBody(), true);
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
                $json = json_decode((string)$request->getBody(), true);
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
