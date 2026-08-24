<?php

namespace Agencia\Close\Adapters;

use Agencia\Close\Helpers\Result;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailAdapter
{
    private PHPMailer $mail;
    private Result $result;

    public function __construct()
    {
        $this->result = new Result();
        $this->mail = new PHPMailer(false);
        $this->mail->isSMTP();
        $this->mail->CharSet = 'UTF-8';
        $this->mail->Host = $this->config('MAIL_HOST');
        $this->mail->SMTPAuth = true;
        $this->mail->Username = $this->config('MAIL_USER');
        $this->mail->Password = $this->mailPassword();
        $this->mail->SMTPSecure = $this->mailSecure();
        $this->mail->Port = (int)$this->config('MAIL_PORT', '587');
        $this->mail->setFrom($this->mailFrom(), $this->mailFromName());
        $this->mail->isHTML(true);
    }

    public static function isConfigured(): bool
    {
        $value = static function (string $name): string {
            return defined($name) ? trim((string)constant($name)) : '';
        };

        $from = $value('MAIL_FROM');
        if ($from === '') {
            $from = $value('MAIL_EMAIL');
        }
        if ($from === '') {
            $from = $value('MAIL_USER');
        }

        return $value('MAIL_HOST') !== '' && $value('MAIL_USER') !== '' && $from !== '';
    }

    public function addAddress(string $email)
    {
        $this->mail->addAddress($email);
    }

    public function setSubject($subject)
    {
        $this->mail->Subject = $subject;
    }

    public function setBody(string $file, array $data = [])
    {
        $template = new TemplateAdapter();
        $this->mail->Body = $template->render($file, $data);
    }

    public function send($result)
    {
        try {
            if ($this->mail->send()) {
                $this->result->setError(false);
                $this->result->setMessage($result);
                return;
            }

            $this->result->setError(true);
            $this->result->setMessage('Falha ao enviar o E-mail!!!');
            $this->result->setInfo([
                'message' => $this->mail->ErrorInfo
            ]);
        } catch (Exception $e) {
            $this->result->setError(true);
            $this->result->setMessage('Falha ao enviar o E-mail!!!');
            $this->result->setInfo([
                'message' => "Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}"
            ]);
        }
    }

    public function getResult(): Result
    {
        return $this->result;
    }

    private function config(string $name, string $default = ''): string
    {
        if (!defined($name)) {
            return $default;
        }

        return trim((string)constant($name));
    }

    private function firstConfig(array $names, string $default = ''): string
    {
        foreach ($names as $name) {
            $value = $this->config($name);
            if ($value !== '') {
                return $value;
            }
        }

        return $default;
    }

    private function mailPassword(): string
    {
        return $this->firstConfig(['MAIL_PASS', 'MAIL_PASSWORD']);
    }

    private function mailFrom(): string
    {
        return $this->firstConfig(['MAIL_FROM', 'MAIL_EMAIL', 'MAIL_USER']);
    }

    private function mailFromName(): string
    {
        return $this->firstConfig(['MAIL_FROM_NAME', 'NAME'], 'EmbalaBag');
    }

    private function mailSecure(): string
    {
        $secure = strtolower($this->config('MAIL_SECURE'));
        $port = (int)$this->config('MAIL_PORT', '587');

        if (in_array($secure, ['ssl', 'smtps'], true) || $port === 465) {
            return PHPMailer::ENCRYPTION_SMTPS;
        }

        return PHPMailer::ENCRYPTION_STARTTLS;
    }
}
