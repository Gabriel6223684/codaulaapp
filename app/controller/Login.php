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

    // Pré-cadastro de usuários
    public function precadastro($request, $response)
    {
        try {
            $form = $request->getParsedBody();

            // Dados do usuário
            $dadosUsuario = [
                'nome' => $form['nome'] ?? '',
                'ativo' => true,
                'senha' => password_hash($form['senhaCadastro'], PASSWORD_DEFAULT)
            ];

            $isInserted = InsertQuery::table('usuario')->save($dadosUsuario);

            if (!$isInserted) {
                return $this->SendJson($response, [
                    'status' => false,
                    'msg' => 'Erro ao cadastrar usuário',
                    'id' => 0
                ], 403);
            }

            // Último usuário cadastrado
            $idUsuario = SelectQuery::select('id')
                ->from('usuario')
                ->order('id', 'desc')
                ->fetch()['id'];

            // Inserir contatos
            $contatos = [
                ['tipo' => 'email', 'contato' => $form['email'] ?? ''],
                ['tipo' => 'celular', 'contato' => $form['celular'] ?? ''],
                ['tipo' => 'whatsapp', 'contato' => $form['whatsapp'] ?? '']
            ];

            foreach ($contatos as $contato) {
                $contato['id_usuario'] = $idUsuario;
                InsertQuery::table('contato')->save($contato);
            }

            // Buscar dados completos do usuário para criar sessão
            $user = SelectQuery::select()
                ->from('vw_usuario_contatos')
                ->where('id', '=', $idUsuario)
                ->fetch();

            // Criar sessão para o usuário recém-cadastrado
            if ($user) {
                $_SESSION['usuario'] = [
                    'id' => $user['id'],
                    'nome' => $user['nome'],
                    'ativo' => $user['ativo'] ?? true,
                    'logado' => true,
                    'administrador' => $user['administrador'] ?? false,
                    'celular' => $user['celular'] ?? '',
                    'email' => $user['email'] ?? '',
                    'whatsapp' => $user['whatsapp'] ?? '',
                    'data_cadastro' => $user['data_cadastro'] ?? null,
                    'data_alteracao' => $user['data_alteracao'] ?? null,
                ];
            }

            return $this->SendJson($response, [
                'status' => true,
                'msg' => 'Cadastro realizado com sucesso!',
                'id' => $idUsuario
            ], 201);
        } catch (\Exception $e) {
            return $this->SendJson($response, [
                'status' => false,
                'msg' => 'Erro: ' . $e->getMessage(),
                'id' => 0
            ], 500);
        }
    }

    // Autenticação de login
    public function autenticar($request, $response)
    {
        try {
            $form = $request->getParsedBody();

            if (empty($form['login'])) {
                return $this->SendJson($response, ['status' => false, 'msg' => 'Informe o login', 'id' => 0], 403);
            }

            if (empty($form['senha'])) {
                return $this->SendJson($response, ['status' => false, 'msg' => 'Informe a senha', 'id' => 0], 403);
            }

            // Buscar usuário pelo login (cpf, email, celular ou whatsapp)
            $user = SelectQuery::select()
                ->from('vw_usuario_contatos')
                ->where('email', '=', $form['login'], 'or')
                ->where('celular', '=', $form['login'], 'or')
                ->where('whatsapp', '=', $form['login'])
                ->fetch();

            if (!$user) {
                return $this->SendJson($response, ['status' => false, 'msg' => 'Usuário ou senha inválidos!', 'id' => 0], 403);
            }

            if (!$user['ativo']) {
                return $this->SendJson($response, ['status' => false, 'msg' => 'Acesso não permitido ainda!', 'id' => 0], 403);
            }

            if (!password_verify($form['senha'], $user['senha'])) {
                return $this->SendJson($response, ['status' => false, 'msg' => 'Usuário ou senha inválidos!', 'id' => 0], 403);
            }

            // Rehash da senha se necessário
            if (password_needs_rehash($user['senha'], PASSWORD_DEFAULT)) {
                UpdateQuery::table('usuario')
                    ->set(['senha' => password_hash($form['senha'], PASSWORD_DEFAULT)])
                    ->where('id', '=', $user['id'])
                    ->update();
            }

            // Criar sessão
            $_SESSION['usuario'] = [
                'id' => $user['id'],
                'nome' => $user['nome'],
                'ativo' => $user['ativo'],
                'logado' => true,
                'administrador' => $user['administrador'],
                'celular' => $user['celular'],
                'email' => $user['email'],
                'whatsapp' => $user['whatsapp'],
                'data_cadastro' => $user['data_cadastro'],
                'data_alteracao' => $user['data_alteracao'],
            ];

            return $this->SendJson($response, [
                'status' => true,
                'msg' => 'Seja bem-vindo de volta!',
                'id' => $user['id']
            ], 200);
        } catch (\Exception $e) {
            return $this->SendJson($response, ['status' => false, 'msg' => 'Erro: ' . $e->getMessage(), 'id' => 0], 500);
        }
    }

    // Envia código de verificação para o e-mail informado (se existir)
    public function recuperarSenha($request, $response)
    {
        try {
            $form = $request->getParsedBody();
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
            UpdateQuery::table('usuario')->set(['codigo_verificacao' => $codigo])->where('id', '=', $user['id'])->update();

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
            $codigo = $form['codigo'] ?? '';
            $senha = $form['senha'] ?? '';

            if (empty($codigo) || empty($senha)) {
                return $this->SendJson($response, ['success' => false, 'message' => 'Código ou senha não informados'], 400);
            }

            $user = SelectQuery::select()->from('usuario')->where('codigo_verificacao', '=', $codigo)->fetch();

            if (!$user) {
                return $this->SendJson($response, ['success' => false, 'message' => 'Código inválido'], 403);
            }

            UpdateQuery::table('usuario')->set(['senha' => password_hash($senha, PASSWORD_DEFAULT), 'codigo_verificacao' => null])->where('id', '=', $user['id'])->update();

            return $this->SendJson($response, ['success' => true, 'message' => 'Senha atualizada com sucesso']);
        } catch (\Exception $e) {
            return $this->SendJson($response, ['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }
}
