<?php
namespace App\Core;

class Validator
{
    // Valida formato de e-mail
    public static function email(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    // Valida CNPJ ou CPF usando o algoritmo oficial
    public static function cnpjCpf(string $valor): bool
    {
        // Remove tudo que não for número
        $valor = preg_replace('/[^0-9]/', '', $valor);

        if (strlen($valor) === 11) {
            return self::validaCPF($valor);
        } elseif (strlen($valor) === 14) {
            return self::validaCNPJ($valor);
        }
        
        return false; // Nem CPF nem CNPJ válido
    }

    private static function validaCPF(string $cpf): bool
    {
        if (preg_match('/(\d)\1{10}/', $cpf)) return false; // Ex: 111.111.111-11
        
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) return false;
        }
        return true;
    }

    private static function validaCNPJ(string $cnpj): bool
    {
        if (preg_match('/(\d)\1{13}/', $cnpj)) return false; // Ex: 11.111.111/1111-11

        for ($t = 12; $t < 14; $t++) {
            for ($d = 0, $m = ($t - 7), $c = 0; $c < $t; $c++) {
                $d += $cnpj[$c] * $m--;
                $m = ($m == 1) ? 9 : $m;
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cnpj[$c] != $d) return false;
        }
        return true;
    }
}