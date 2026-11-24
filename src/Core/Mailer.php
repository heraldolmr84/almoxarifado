<?php
namespace App\Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    public static function enviarRecuperacaoSenha(string $paraEmail, string $token): bool
    {
        $mail = new PHPMailer(true);
        
        try {
            $mail->isSMTP();
            $mail->Host       = $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['MAIL_USER'] ?? '';
            $mail->Password   = $_ENV['MAIL_PASS'] ?? ''; // Senha de app sem espaços
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $_ENV['MAIL_PORT'] ?? 587;
            $mail->Timeout    = 10; // Evita travar a tela se a rede falhar

            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';

            $mail->setFrom($_ENV['MAIL_FROM'] ?? 'nao-responda@almoxarifado.com', 'SISGEM - Almoxarifado');
            $mail->addAddress($paraEmail);
            
            $mail->isHTML(true);
            $mail->Subject = 'Recuperação de Senha - SISGEM';
            
            // Link de recuperação
            $link = "http://" . $_SERVER['HTTP_HOST'] . "/almoxarifado/public/?controller=auth&action=reset&token=" . $token;
            
            $mail->Body = "
                <h2>Olá!</h2>
                <p>Você solicitou a recuperação de senha no sistema SISGEM.</p>
                <p>Clique no botão abaixo para criar uma nova senha. Este link é válido por 1 hora.</p>
                <p>
                    <a href='{$link}' style='background-color: #0088B7; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                        Redefinir Minha Senha
                    </a>
                </p>
                <p>Se você não solicitou isso, ignore este e-mail.</p>
            ";

            $mail->send();
            return true;
        } catch (Exception $e) {
            // Salva o erro no log para o admin ver, sem quebrar a tela do usuário
            error_log("Erro no envio de e-mail: " . $mail->ErrorInfo);
            return false;
        }
    }
}