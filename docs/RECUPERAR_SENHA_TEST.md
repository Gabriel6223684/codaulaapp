Testes manuais para fluxo de recuperação de senha

1. Endpoint: /recuperar-senha (POST JSON)

curl -X POST -H "Content-Type: application/json" \
 -d '{"email":"usuario@example.com"}' \
 http://localhost/recuperar-senha

Resposta esperada (200):
{ "success": true, "message": "Se o e-mail existir, você receberá instruções para recuperar a senha." }

Verifique no banco de dados (tabela `usuario`) as colunas `codigo_verificacao` e `codigo_gerado_em` para o usuário informado.

2. Endpoint: /validar-codigo (POST JSON)

curl -X POST -H "Content-Type: application/json" \
 -d '{"codigo":"123456","senha":"NovaSenha123"}' \
 http://localhost/validar-codigo

Respostas possíveis:

- Sucesso (200): { "success": true, "message": "Senha atualizada com sucesso" }
- Código inválido (403): { "success": false, "message": "Código inválido" }
- Código expirado (403): { "success": false, "message": "Código expirado" }

Observações:

- O código expira em 15 minutos por padrão.
- Se os e-mails não estiverem sendo entregues, verifique as configurações em `app/helper/settings.php` e os logs do servidor de e-mail.
- Execute o arquivo `migrations/20260104_add_codigo_columns.sql` para adicionar as colunas necessárias.
- Execute o arquivo `migrations/20260104_add_codigo_columns.sql` para adicionar as colunas necessárias.
