<?php

namespace Agencia\Close\Helpers\String;

class Strings
{
    public static function removePreposition(string $preposition)
    {
        $wordsToRemove = array("da", "de", "di", "do", "du", "para", "pra", "em", "in", "por", "até", "ate");
        return preg_replace('/\b(' . implode('|', $wordsToRemove) . ')\b/', '', $preposition);
    }

    public static function getToString(array $gets): string
    {
        unset($gets['route'], $gets['data']);
        $stringGet = '?';
        $index = 0;
        foreach ($gets as $key => $get) {
            if ($index !== 0) {
                $stringGet .= '&';
            }
            $stringGet .= $key . '=' . $get;
            $index++;
        }
        return $stringGet;
    }

    public static function convertCommaForFormatToInSQl(string $string): string
    {
         $arrayString = explode(',', $string);
         return "('" . implode("','", $arrayString) . "')";
    }

    public static function abreviarNomeEmpresa(string $nome): string
    {
        // Lista de palavras para abreviar
        $abreviacoes = [
            'LINHAS AEREAS' => 'L.A.',
            'LINHAS AÉREAS' => 'L.A.',
            'LINHAS AEREO' => 'L.A.',
            'LINHAS AÉREO' => 'L.A.',
            'LINHAS AEREA' => 'L.A.',
            'LINHAS AÉREA' => 'L.A.',
            'LINHAS AEREAS' => 'L.A.',
            'LINHAS AÉREAS' => 'L.A.',
        ];

        $nomeOriginal = $nome;
        $nome = strtoupper($nome);

        // Aplica as abreviações
        foreach ($abreviacoes as $palavra => $abreviacao) {
            $nome = str_replace($palavra, $abreviacao, $nome);
        }

        // Se houve mudança, retorna o nome abreviado
        if ($nome !== strtoupper($nomeOriginal)) {
            return $nome;
        }

        // Se não houve mudança, retorna o nome original
        return $nomeOriginal;
    }

}