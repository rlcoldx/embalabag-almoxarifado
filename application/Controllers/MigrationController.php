<?php

namespace Agencia\Close\Controllers;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Database\MainDatabase;
use Agencia\Close\Helpers\Migration\SqlFileExecutor;

class MigrationController extends Controller
{
    public function migrate($params)
    {
        $this->setParams($params);
        $result = [];
        $errors = [];
        $migrationDir = __DIR__ . '/../../migrations';
        $executed = 0;

        if (!is_dir($migrationDir)) {
            echo "Diretório de migrations não encontrado.";
            return;
        }

        $files = glob($migrationDir . '/*.sql');
        sort($files);

        $read = new Read(new MainDatabase());
        $reflection = new \ReflectionClass($read);
        $method = $reflection->getMethod('getConn');
        $method->setAccessible(true);
        $pdo = $method->invoke($read);
        $executor = new SqlFileExecutor();

        foreach ($files as $file) {
            try {
                $info = $executor->executeFile($pdo, $file);
                $result[] = $info['file'] . ' executado com sucesso (' . $info['statements'] . ' statements)';
                $executed++;
            } catch (\PDOException $e) {
                $errors[] = 'Erro em ' . basename($file) . ': ' . $e->getMessage();
            } catch (\Throwable $e) {
                $errors[] = 'Erro em ' . basename($file) . ': ' . $e->getMessage();
            }
        }

        echo '<h2>Migrations executadas</h2>';
        if ($executed > 0) {
            echo '<ul>';
            foreach ($result as $msg) {
                echo '<li style="color:green">' . htmlspecialchars($msg) . '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p>Nenhuma migration executada.</p>';
        }
        if ($errors) {
            echo '<h3>Erros:</h3><ul>';
            foreach ($errors as $err) {
                echo '<li style="color:red">' . htmlspecialchars($err) . '</li>';
            }
            echo '</ul>';
        }
        echo '<p>Processo finalizado.</p>';
    }
}
