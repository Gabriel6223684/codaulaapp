<?php

namespace app\source;

class Sms
{
    private $to;
    private $message;
    private $err;

    public function add($to, $message)
    {
        $this->to = $to;
        $this->message = $message;
        return $this;
    }

    public function send()
    {
        // Implementação mínima: usa Twilio se variáveis de ambiente estiverem definidas,
        // caso contrário apenas loga a mensagem (útil para desenvolvimento).
        $sid = getenv('TWILIO_SID');
        $token = getenv('TWILIO_TOKEN');
        $from = getenv('TWILIO_FROM');

        if ($sid && $token && $from) {
            try {
                // Chamada para a API do Twilio (mensagens)
                $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";
                $data = http_build_query([
                    'From' => $from,
                    'To' => $this->to,
                    'Body' => $this->message
                ]);
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                curl_setopt($ch, CURLOPT_USERPWD, $sid . ':' . $token);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                $result = curl_exec($ch);
                $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                if ($result === false || ($http < 200 || $http >= 300)) {
                    $this->err = new \Exception('Erro ao enviar SMS via Twilio. HTTP=' . $http . ' R=' . ($result ?: ''));
                    error_log('[SMS] ' . $this->err->getMessage());
                    curl_close($ch);
                    return false;
                }
                curl_close($ch);
                return true;
            } catch (\Exception $e) {
                $this->err = $e;
                error_log('[SMS] ' . $e->getMessage());
                return false;
            }
        }

        // Se não há configuração de serviço, apenas logamos para desenvolvimento.
        error_log("[SMS-STUB] To: {$this->to} Msg: {$this->message}");
        return true;
    }

    public function error()
    {
        return $this->err;
    }
}
