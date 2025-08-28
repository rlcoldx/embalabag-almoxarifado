<?php

namespace Agencia\Close\Models\Log;

use Agencia\Close\Conn\Conn;
use Agencia\Close\Conn\Create;
use Agencia\Close\Conn\Read;
use Agencia\Close\Models\Model;

class LogAcesso extends Model
{
    private string $table = 'log_acessos';

    public function registrarLogin($usuarioId, $email, $status = 'sucesso', $mensagem = null)
    {
        $data = [
            'usuario_id' => $usuarioId,
            'email' => $email,
            'ip' => $this->getClientIP(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'tipo_acesso' => 'login',
            'status' => $status,
            'mensagem' => $mensagem
        ];

        $create = new Create();
        $create->ExeCreate($this->table, $data);
        return $create->getResult();
    }

    public function registrarLogout($usuarioId, $email)
    {
        $data = [
            'usuario_id' => $usuarioId,
            'email' => $email,
            'ip' => $this->getClientIP(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'tipo_acesso' => 'logout',
            'status' => 'sucesso',
            'mensagem' => 'Logout realizado com sucesso'
        ];

        $create = new Create();
        $create->ExeCreate($this->table, $data);
        return $create->getResult();
    }

    public function registrarFalhaLogin($email, $mensagem = 'Credenciais inválidas')
    {
        $data = [
            'usuario_id' => null,
            'email' => $email,
            'ip' => $this->getClientIP(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'tipo_acesso' => 'falha_login',
            'status' => 'falha',
            'mensagem' => $mensagem
        ];

        $create = new Create();
        $create->ExeCreate($this->table, $data);
        return $create->getResult();
    }

    public function getLogsPorUsuario($usuarioId, $limit = 50)
    {
        $this->read = new Read();
        $this->read->ExeRead($this->table, "WHERE usuario_id = :usuario_id ORDER BY data_acesso DESC LIMIT :limit", "usuario_id={$usuarioId}&limit={$limit}");
        return $this->read;
    }

    public function getLogsRecentes($limit = 100)
    {
        $this->read = new Read();
        $this->read->ExeRead($this->table, "ORDER BY data_acesso DESC LIMIT :limit", "limit={$limit}");
        return $this->read;
    }

    public function getLogsPorPeriodo($dataInicio, $dataFim)
    {
        $this->read = new Read();
        $this->read->ExeRead($this->table, "WHERE data_acesso BETWEEN :data_inicio AND :data_fim ORDER BY data_acesso DESC", "data_inicio={$dataInicio}&data_fim={$dataFim}");
        return $this->read;
    }

    private function getClientIP()
    {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'HTTP_CLIENT_IP', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
} 