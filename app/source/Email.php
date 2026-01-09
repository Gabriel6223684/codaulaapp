<?php

namespace app\source;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Email
{
    private PHPMailer $mail;
    private array $data = [];
    private ?Exception $error = null;

    public function __construct()
    {
        $this->mail = new PHPMailer(true);
        $this->mail->isSMTP();
        $this->mail->isHTML(true);
        $this->mail->CharSet = 'UTF-8';

        $this->mail->Host = CONFIG_SMTP_EMAIL['host'];
        $this->mail->SMTPAuth = true;
        $this->mail->Username = CONFIG_SMTP_EMAIL['user'];
        $this->mail->Password = CONFIG_SMTP_EMAIL['passwd'];

        $enc = CONFIG_SMTP_EMAIL['encryption'] ?? 'tls';
        $this->mail->SMTPSecure = $enc === 'ssl'
            ? PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer::ENCRYPTION_STARTTLS;

        $this->mail->Port = CONFIG_SMTP_EMAIL['port'];
    }

    public function add(
        string $subject,
        string $body,
        string $recipient_name,
        string $recipient_email
    ): self {
        if (!$subject || !$body) {
            throw new \InvalidArgumentException('Assunto e corpo são obrigatórios');
        }

        if (!filter_var($recipient_email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('E-mail inválido');
        }

        $this->data = compact(
            'subject',
            'body',
            'recipient_name',
            'recipient_email'
        );

        return $this;
    }

    public function attach(string $filePath, string $fileName): self
    {
        $this->data['attach'][$filePath] = $fileName;
        return $this;
    }

    public function send(
        string $from_name = CONFIG_SMTP_EMAIL['from_name'],
        string $from_email = CONFIG_SMTP_EMAIL['from_email']
    ): bool {
        // Se SENDGRID_API_KEY estiver configurado, usa API do SendGrid
        $sendgridKey = getenv('SENDGRID_API_KEY');
        if ($sendgridKey) {
            $payload = [
                'personalizations' => [[
                    'to' => [['email' => $this->data['recipient_email'], 'name' => $this->data['recipient_name']]],
                    'subject' => $this->data['subject']
                ]],
                'from' => ['email' => $from_email, 'name' => $from_name],
                'content' => [['type' => 'text/html', 'value' => $this->data['body']]]
            ];

            $opts = [
                'http' => [
                    'method' => 'POST',
                    'header' => "Authorization: Bearer {$sendgridKey}\r\nContent-Type: application/json\r\n",
                    'content' => json_encode($payload),
                    'timeout' => 10
                ]
            ];

            $result = @file_get_contents('https://api.sendgrid.com/v3/mail/send', false, stream_context_create($opts));
            if ($result === false) {
                error_log('[EMAIL][SENDGRID] Falha no envio (fallback para SMTP)');
                // cai para SMTP abaixo
            } else {
                return true;
            }
        }

        // Fallback: SMTP via PHPMailer (configurado por CONFIG_SMTP_EMAIL)
        try {
            $this->mail->setFrom($from_email, $from_name);
            $this->mail->addAddress(
                $this->data['recipient_email'],
                $this->data['recipient_name']
            );

            $this->mail->Subject = $this->data['subject'];
            $this->mail->Body = $this->data['body'];

            if (!empty($this->data['attach'])) {
                foreach ($this->data['attach'] as $path => $name) {
                    $this->mail->addAttachment($path, $name);
                }
            }

            return $this->mail->send();
        } catch (Exception $e) {
            $this->error = $e;
            error_log('[EMAIL] ' . $e->getMessage());
            return false;
        }
    }

    public function error(): ?Exception
    {
        return $this->error;
    }

    public function errorMessage(): ?string
    {
        return $this->error?->getMessage();
    }
}
