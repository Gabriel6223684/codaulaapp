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
}
