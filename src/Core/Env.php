<?php
namespace App\Core;

class Env
{
    public static function carregar(string $caminhoArquivo): void
    {
        if (!file_exists($caminhoArquivo)) {
            return;
        }

        $linhas = file($caminhoArquivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($linhas as $linha) {
            if (strpos(trim($linha), '#') === 0) continue; // Ignora comentários

            if (strpos($linha, '=') !== false) {
                list($nome, $valor) = explode('=', $linha, 2);
                $nome = trim($nome);
                $valor = trim($valor, " \t\n\r\0\x0B\"");

                // Salva tanto no $_ENV quanto no getenv()
                $_ENV[$nome] = $valor;
                putenv("{$nome}={$valor}");
            }
        }
    }
}