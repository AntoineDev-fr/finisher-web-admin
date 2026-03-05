<?php
declare(strict_types=1);

namespace App\Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

final class Mailer
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config['mail'];
    }

    public function send(string $to, string $subject, string $html, string $text = ''): bool
    {
        $mail = new PHPMailer(true);

        try {
            if (($this->config['driver'] ?? 'smtp') === 'smtp') {
                $mail->isSMTP();
                $mail->Host = $this->config['host'];
                $mail->Port = (int)$this->config['port'];
                $mail->SMTPAuth = !empty($this->config['user']);
                $mail->Username = $this->config['user'];
                $mail->Password = $this->config['pass'];

                if ((int)$this->config['port'] === 465) {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                } elseif ((int)$this->config['port'] === 587) {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                }
            } else {
                $mail->isMail();
            }

            $mail->setFrom($this->config['from'], $this->config['from_name']);
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->isHTML(true);
            $mail->Body = $html;
            $mail->AltBody = $text !== '' ? $text : strip_tags($html);

            return $mail->send();
        } catch (MailException $e) {
            return false;
        }
    }
}
