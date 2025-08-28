<?php

namespace Agencia\Close\Models\User;

use Agencia\Close\Models\Model;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Create;
use Agencia\Close\Conn\Update;
use Agencia\Close\Conn\Delete;

class Cargo extends Model
{
    public function getAllCargos(): Read
    {
        $this->read = new Read();
        $this->read->FullRead("SELECT * FROM cargos ORDER BY nome ASC");
        return $this->read;
    }

    public function getCargosAtivos(): Read
    {
        $this->read = new Read();
        $this->read->FullRead("SELECT * FROM cargos WHERE status = 'ativo' ORDER BY nome ASC");
        return $this->read;
    }

    public function getCargoById(int $id): Read
    {
        $this->read = new Read();
        $this->read->FullRead("SELECT * FROM cargos WHERE id = :id", "id={$id}");
        return $this->read;
    }

    public function createCargo(array $data): int|false
    {
        $this->create = new Create();
        $this->create->ExeCreate("cargos", $data);
        return $this->create->getResult();
    }

    public function updateCargo(int $id, array $data): bool
    {
        $this->update = new Update();
        $this->update->ExeUpdate("cargos", $data, "WHERE id = :id", "id={$id}");
        $result = $this->update->getResult();
        return $result === true;
    }

    public function deleteCargo(int $id): bool
    {
        // Verificar se há usuários usando este cargo
        $this->read = new Read();
        $this->read->FullRead("SELECT COUNT(*) as total FROM usuario_cargos WHERE cargo_id = :id", "id={$id}");
        $result = $this->read->getResult();
        
        if ($result && $result[0]['total'] > 0) {
            return false; // Não pode deletar se há usuários usando
        }
        
        $this->delete = new Delete();
        $this->delete->ExeDelete("cargos", "WHERE id = :id", "id={$id}");
        $result = $this->delete->getResult();
        return $result === true;
    }

    public function getPermissoesDoCargo(int $cargoId): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT p.* FROM permissoes p
            INNER JOIN cargo_permissoes cp ON p.id = cp.permissao_id
            WHERE cp.cargo_id = :cargo_id
            ORDER BY p.modulo, p.acao
        ", "cargo_id={$cargoId}");
        return $this->read;
    }

    public function setPermissoesDoCargo(int $cargoId, array $permissoesIds): bool
    {
        try {
            // Remover permissões existentes
            $this->delete = new Delete();
            $this->delete->ExeDelete("cargo_permissoes", "WHERE cargo_id = :cargo_id", "cargo_id={$cargoId}");
            $deleteResult = $this->delete->getResult();
            
            // Se o delete falhou, retornar false
            if ($deleteResult !== true) {
                error_log("Erro ao deletar permissões existentes do cargo {$cargoId}: " . json_encode($deleteResult));
                return false;
            }
            
            // Inserir novas permissões
            if (!empty($permissoesIds)) {
                $this->create = new Create();
                foreach ($permissoesIds as $permissaoId) {
                    $data = [
                        'cargo_id' => $cargoId,
                        'permissao_id' => $permissaoId
                    ];
                    $this->create->ExeCreate("cargo_permissoes", $data);
                    $result = $this->create->getResult();
                    
                    // Verificar se a inserção foi bem-sucedida
                    if ($result === null) {
                        error_log("Erro ao inserir permissão {$permissaoId} para cargo {$cargoId}");
                        return false;
                    }
                }
            }
            
            return true;
        } catch (\Exception $e) {
            error_log("Exceção ao definir permissões do cargo {$cargoId}: " . $e->getMessage());
            return false;
        }
    }

    public function cargoTemPermissao(int $cargoId, string $modulo, string $acao): bool
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT COUNT(*) as total FROM cargo_permissoes cp
            INNER JOIN permissoes p ON cp.permissao_id = p.id
            WHERE cp.cargo_id = :cargo_id AND p.modulo = :modulo AND p.acao = :acao
        ", "cargo_id={$cargoId}&modulo={$modulo}&acao={$acao}");
        
        $result = $this->read->getResult();
        return $result && $result[0]['total'] > 0;
    }
} 