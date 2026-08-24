<?php

namespace Agencia\Close\Helpers\Migration;

use PDO;
use PDOException;

class SqlFileExecutor
{
    /**
     * Divide um arquivo SQL em statements, respeitando DELIMITER (necessário para CREATE TRIGGER).
     *
     * @return string[]
     */
    public function splitStatements(string $sql): array
    {
        $delimiter = ';';
        $statements = [];
        $buffer = '';
        $lines = preg_split('/\R/', $sql) ?: [];

        foreach ($lines as $line) {
            if (preg_match('/^\s*DELIMITER\s+(\S+)\s*$/i', $line, $match)) {
                $pending = $this->flushBuffer($buffer, $delimiter);
                if ($pending !== null) {
                    $statements[] = $pending;
                }
                $buffer = '';
                $delimiter = $match[1];
                continue;
            }

            $buffer .= $line . "\n";
            $trimmed = rtrim($buffer);
            $delimiterLength = strlen($delimiter);

            if ($delimiterLength > 0 && substr($trimmed, -$delimiterLength) === $delimiter) {
                $statement = trim(substr($trimmed, 0, -$delimiterLength));
                if ($this->isExecutable($statement)) {
                    $statements[] = $statement;
                }
                $buffer = '';
            }
        }

        $pending = $this->flushBuffer($buffer, $delimiter);
        if ($pending !== null) {
            $statements[] = $pending;
        }

        return $statements;
    }

    public function executeFile(PDO $pdo, string $filePath): array
    {
        if (!is_file($filePath)) {
            throw new \RuntimeException('Arquivo SQL não encontrado: ' . $filePath);
        }

        $sql = file_get_contents($filePath);
        if ($sql === false) {
            throw new \RuntimeException('Não foi possível ler o arquivo SQL: ' . $filePath);
        }

        $statements = $this->splitStatements($sql);
        $executed = 0;
        $useTransaction = !$this->containsDdl($statements);

        if ($useTransaction && !$pdo->inTransaction()) {
            $pdo->beginTransaction();
        }

        try {
            foreach ($statements as $statement) {
                $pdo->exec($statement);
                $executed++;
            }

            if ($useTransaction && $pdo->inTransaction()) {
                $pdo->commit();
            }
        } catch (PDOException $exception) {
            if ($useTransaction && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }

        return [
            'file' => basename($filePath),
            'statements' => $executed,
        ];
    }

    private function flushBuffer(string $buffer, string $delimiter): ?string
    {
        $trimmed = trim($buffer);
        if ($trimmed === '') {
            return null;
        }

        $delimiterLength = strlen($delimiter);
        if ($delimiterLength > 0 && substr($trimmed, -$delimiterLength) === $delimiter) {
            $trimmed = trim(substr($trimmed, 0, -$delimiterLength));
        }

        return $this->isExecutable($trimmed) ? $trimmed : null;
    }

    private function isExecutable(string $statement): bool
    {
        $withoutLineComments = preg_replace('/--[^\n]*/', '', $statement);
        $withoutBlockComments = preg_replace('/\/\*.*?\*\//s', '', (string)$withoutLineComments);

        return trim((string)$withoutBlockComments) !== '';
    }

    private function containsDdl(array $statements): bool
    {
        foreach ($statements as $statement) {
            if (preg_match('/^\s*(CREATE|DROP|ALTER)\s+(TRIGGER|TABLE|VIEW|PROCEDURE|FUNCTION)\b/i', $statement)) {
                return true;
            }
        }

        return false;
    }
}
