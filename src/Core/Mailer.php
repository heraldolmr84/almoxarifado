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
            // --- CONFIGURAÇÕES DO SMTP ---
            // Substitua com os dados do seu provedor (Gmail, Mailtrap, etc)
            $mail->isSMTP();
            $mail->SMTPDebug = 3;
            $mail->Debugoutput = 'error_log';
            $mail->Host = 'smtp.gmail.com'; // Ex: smtp.gmail.com
            $mail->SMTPAuth = true;
            $mail->Timeout = 10; // Desiste de tentar conectar após 10 segundos
            $mail->Username = 'heraldo.ribeiro@ufms.br';         // Seu e-mail ou user do mailtrap
            $mail->Password = 'svuxtddvypyzqyfr';           // Sua senha ou pass do mailtrap
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;                      // Mailtrap usa 2525. Gmail usa 587

            // --- REMETENTE E DESTINATÁRIO ---
            $mail->setFrom('nao-responda@almoxarifado.com', 'Almoxarifado UFMS');
            $mail->addAddress($paraEmail);

            // --- CONTEÚDO ---
            $mail->isHTML(true);
            $mail->Subject = 'Recuperação de Senha - Almoxarifado';

            // Link que o usuário vai clicar
            $link = "http://localhost/almoxarifado/public/?controller=auth&action=reset&token=" . $token;

            $mail->Body = "
                <h2>Olá!</h2>
                <p>Você solicitou a recuperação de senha no sistema de Almoxarifado.</p>
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
            // Em produção, salve o erro em um log
            return false;
        }
    }
}