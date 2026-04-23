<?php

namespace App\Service;

use PHPMailer\PHPMailer\PHPMailer;

class EmailService
{
    public function send(string $to, float $amount): void
    {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host = 'mailhog';
        $mail->Port = 1025;

        $mail->setFrom('no-reply@saque-pix.com', 'Saque PIX');
        $mail->addAddress($to);

        $mail->Subject = 'Saque realizado';
        $mail->Body = "Valor: R$ {$amount}\nData: " . date('Y-m-d H:i:s');

        $mail->send();
    }
}
