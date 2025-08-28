<?php

namespace Agencia\Close\Controllers;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Database\MainDatabase;
use PDO;

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

        // Usar a classe Read que já estende Conn e tem acesso ao método protegido
        $read = new Read(new MainDatabase());
        
        // Acessar a conexão PDO através de reflexão
        $reflection = new \ReflectionClass($read);
        $method = $reflection->getMethod('getConn');
        $method->setAccessible(true);
        $pdo = $method->invoke($read);

        foreach ($files as $file) {
            $sql = file_get_contents($file);
            try {
                $pdo->beginTransaction();
                $pdo->exec($sql);
                $pdo->commit();
                $result[] = basename($file) . ' executado com sucesso';
                $executed++;
            } catch (\PDOException $e) {
                // Verificar se há transação ativa antes de fazer rollback
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $errors[] = 'Erro em ' . basename($file) . ': ' . $e->getMessage();
            }
        }

        // Resposta amigável
        echo '<h2>Migrations executadas</h2>';
        if ($executed > 0) {
            echo '<ul>';
            foreach ($result as $msg) {
                echo '<li style="color:green">' . $msg . '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p>Nenhuma migration executada.</p>';
        }
        if ($errors) {
            echo '<h3>Erros:</h3><ul>';
            foreach ($errors as $err) {
                echo '<li style="color:red">' . $err . '</li>';
            }
            echo '</ul>';
        }
        echo '<p>Processo finalizado.</p>';
    }
} 