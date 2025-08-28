<?php

namespace Agencia\Close\Models\User;

use Agencia\Close\Conn\Conn;
use Agencia\Close\Conn\Create;
use Agencia\Close\Conn\Read;
use Agencia\Close\Conn\Update;
use Agencia\Close\Conn\Delete;
use Agencia\Close\Helpers\User\Identification;
use Agencia\Close\Helpers\User\UserIdentification;
use Agencia\Close\Models\Model;

class User extends Model
{
    private string $table = 'usuarios';

    public function getUserByID(string $user): Read
    {
        $read = new Read();
        $read->ExeRead($this->table, 'WHERE id = :id', "id={$user}");
        return $read;
    }

    public function emailExist(string $email): Read
    {
        $read = new Read();
        $read->ExeRead($this->table, 'WHERE email = :email', "email={$email}");
        return $read;
    }

    public function saveUserCookie($idUser, $email, $cookieHash)
    {
        $this->saveDatabase($cookieHash, $idUser);
        $this->saveCookie($email, $cookieHash);
    }

    public function saveDatabase($cookieHash, $idUser): void
    {
        $data = ['cookie_key' => $cookieHash];
        $update = new Update();
        $update->ExeUpdate($this->table, $data, 'WHERE id = :idUser', "idUser={$idUser}");
    }

    public function saveCookie($email, $cookieHash): void
    {
        $expire = time() + 3600 * 24 * 365;
        setcookie("CookieLoginEmail", $email, $expire);
        setcookie("CookieLoginHash", $cookieHash, $expire);
    }

    public function saveUser(string $name, Identification $identification, string $sector, string $password)
    {
        $secondIdentification = new Identification();
        if ($identification->getType() !== 'email'){
            $userIdentification = new UserIdentification();
            $secondIdentification = $userIdentification->getFakeEmail();
        }

        $data = [
            'nome' => $name,
            'user_setor' => $sector,
            'senha' => sha1($password),
        ];

        $data = array_merge($data, [$identification->getColumn() => $identification->getIdentification()]);

        if($secondIdentification->getColumn() !== ''){
            $data = array_merge($data, [$secondIdentification->getColumn() => $secondIdentification->getIdentification()]);
        }

        $create = new Create();
        $create->ExeCreate($this->table, $data);
        return $create->getResult();
    }

    public function saveUserByOauth(string $name, string $email, array $arrayWithFieldAndId)
    {
        $data = [
            'nome' => $name,
            'email' => $email
        ];

        $data = array_merge($data, $arrayWithFieldAndId);

        $create = new Create();
        $create->ExeCreate($this->table, $data);
        return $create->getResult();
    }

    public function changePasswordByEmail(string $email, string $password): bool
    {
        $data = [
            'senha' => sha1($password),
        ];
        $this->update = new Update();
        $this->update->ExeUpdate($this->table, $data, "WHERE email = :email", "email={$email}");
        $result = $this->update->getResult();
        return $result === true;
    }

    public function updateLastAccess(int $userId): bool
    {
        $data = ['ultimo_acesso' => date('Y-m-d H:i:s')];
        $this->update = new Update();
        $this->update->ExeUpdate($this->table, $data, "WHERE id = :id", "id={$userId}");
        $result = $this->update->getResult();
        return $result === true;
    }

    // Novos métodos para gerenciamento de usuários
    public function getAllUsers(): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT u.*, 
                   GROUP_CONCAT(c.nome SEPARATOR ', ') as cargos
            FROM usuarios u
            LEFT JOIN usuario_cargos uc ON u.id = uc.usuario_id
            LEFT JOIN cargos c ON uc.cargo_id = c.id
            GROUP BY u.id
            ORDER BY u.nome ASC
        ");
        return $this->read;
    }

    public function getUsersByType(string $tipo): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT u.*, 
                   GROUP_CONCAT(c.nome SEPARATOR ', ') as cargos
            FROM usuarios u
            LEFT JOIN usuario_cargos uc ON u.id = uc.usuario_id
            LEFT JOIN cargos c ON uc.cargo_id = c.id
            WHERE u.tipo = :tipo
            GROUP BY u.id
            ORDER BY u.nome ASC
        ", "tipo={$tipo}");
        return $this->read;
    }

    public function createUser(array $data): int|false
    {
        $this->create = new Create();
        $this->create->ExeCreate($this->table, $data);
        return $this->create->getResult();
    }

    public function updateUser(int $id, array $data): bool
    {
        $this->update = new Update();
        $this->update->ExeUpdate($this->table, $data, "WHERE id = :id", "id={$id}");
        $result = $this->update->getResult();
        return $result === true;
    }

    public function deleteUser(int $id): bool
    {
        $this->delete = new Delete();
        $this->delete->ExeDelete($this->table, "WHERE id = :id", "id={$id}");
        $result = $this->delete->getResult();
        return $result === true;
    }

    public function getUserWithCargos(int $id): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT u.*, 
                   GROUP_CONCAT(c.id SEPARATOR ',') as cargo_ids,
                   GROUP_CONCAT(c.nome SEPARATOR ', ') as cargo_nomes
            FROM usuarios u
            LEFT JOIN usuario_cargos uc ON u.id = uc.usuario_id
            LEFT JOIN cargos c ON uc.cargo_id = c.id
            WHERE u.id = :id
            GROUP BY u.id
        ", "id={$id}");
        return $this->read;
    }

    public function setUserCargos(int $userId, array $cargosIds): bool
    {
        // Remover cargos existentes
        $this->delete = new Delete();
        $this->delete->ExeDelete("usuario_cargos", "WHERE usuario_id = :usuario_id", "usuario_id={$userId}");
        $deleteResult = $this->delete->getResult();
        
        // Se o delete falhou, retornar false
        if ($deleteResult !== true) {
            return false;
        }
        
        // Inserir novos cargos
        if (!empty($cargosIds)) {
            $this->create = new Create();
            foreach ($cargosIds as $cargoId) {
                $data = [
                    'usuario_id' => $userId,
                    'cargo_id' => $cargoId
                ];
                $this->create->ExeCreate("usuario_cargos", $data);
                // Verificar se a inserção foi bem-sucedida
                if ($this->create->getResult() === null) {
                    return false;
                }
            }
        }
        
        return true;
    }

    public function getCargosDoUsuario(int $userId): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT c.* FROM cargos c
            INNER JOIN usuario_cargos uc ON c.id = uc.cargo_id
            WHERE uc.usuario_id = :usuario_id
            ORDER BY c.nome
        ", "usuario_id={$userId}");
        return $this->read;
    }

    public function userTemCargo(int $userId, int $cargoId): bool
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT COUNT(*) as total FROM usuario_cargos 
            WHERE usuario_id = :usuario_id AND cargo_id = :cargo_id
        ", "usuario_id={$userId}&cargo_id={$cargoId}");
        
        $result = $this->read->getResult();
        return $result && $result[0]['total'] > 0;
    }

    public function getPermissoesDoUsuario(int $userId): Read
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT DISTINCT p.* FROM permissoes p
            INNER JOIN cargo_permissoes cp ON p.id = cp.permissao_id
            INNER JOIN usuario_cargos uc ON cp.cargo_id = uc.cargo_id
            WHERE uc.usuario_id = :usuario_id
            ORDER BY p.modulo, p.acao
        ", "usuario_id={$userId}");
        return $this->read;
    }

    public function usuarioTemPermissao(int $userId, string $modulo, string $acao): bool
    {
        $this->read = new Read();
        $this->read->FullRead("
            SELECT COUNT(*) as total FROM usuario_cargos uc
            INNER JOIN cargo_permissoes cp ON uc.cargo_id = cp.cargo_id
            INNER JOIN permissoes p ON cp.permissao_id = p.id
            WHERE uc.usuario_id = :usuario_id AND p.modulo = :modulo AND p.acao = :acao
        ", "usuario_id={$userId}&modulo={$modulo}&acao={$acao}");
        
        $result = $this->read->getResult();
        return $result && $result[0]['total'] > 0;
    }
}