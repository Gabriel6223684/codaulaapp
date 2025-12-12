<?php

namespace app\source;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Email
{
    private $mail;
    private $data;
    private $error;

    public function __construct()
    {
        $this->data = [];
        $this->mail = new PHPMailer(true);
        $this->mail->isSMTP();
        $this->mail->isHTML();
        $this->mail->CharSet = PHPMailer::CHARSET_UTF8;
        $this->mail->Host = CONFIG_SMIP_EMAIL['host'];
        $this->mail->SMTPAuth = true;
        $this->mail->Username = CONFIG_SMIP_EMAIL['user'];
        $this->mail->Password = CONFIG_SMIP_EMAIL['passwd'];
        $this->mail->SMSTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port = CONFIG_SMIP_EMAIL['port'];
    }
    public function add(string $subjetct, string $body, string $recipient_name, string $recipient_email): self
    {
        $self = new self();
        $self->data['subject'] = $subjetct;
        $self->data['body'] = $body;
        $self->data['recipient_name'] = $recipient_name;
        $self->data['recipient_email'] = $recipient_email;
        return $self;
    }
    public function attach(string $filePath, string $fileName): self
    {
        $this->data['attach'][$filePath] = $fileName;
        return $this;
    }
    public function send(string $from_name = CONFIG_SMIP_EMAIL['from_name'], string $from_email = CONFIG_SMIP_EMAIL['from_email']): bool
    {
        try {
            $this->mail->setFrom($from_email, $from_name);
            $this->mail->addAddress($this->data['recipient_email'], $this->data['recipient_name']);
            $this->mail->Subject = $this->data['subject'];
            $this->mail->Body = $this->data['body'];
            if (!empty($this->data['attach'])) {
                foreach ($this->data['attach'] as $Path => $Name) {
                    $this->mail->addAttachment($Path, $Name);
                }
            }
            $this->mail->send();
            return true;
        } catch (Exception $e) {
            $this->error = $e;
            return false;
        }
    }
    public function error(): ?Exception
    {
        return $this->error;
    }
}