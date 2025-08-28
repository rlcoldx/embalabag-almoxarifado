<?php

namespace Agencia\Close\Controllers\Login;

use Agencia\Close\Adapters\TemplateAdapter;
use Agencia\Close\Helpers\Device\CheckDevice;
use Agencia\Close\Helpers\User\EmailUser;
use Agencia\Close\Helpers\User\Identification;
use Agencia\Close\Models\User\User;
use Agencia\Close\Models\User\Permissao;
use Agencia\Close\Models\Log\LogAcesso;
use Agencia\Close\Services\Login\Logon;
use CoffeeCode\Router\Router;

class LoginController
{
    protected TemplateAdapter $template;
    private array $dataDefault = [];
    protected Router $router;
    protected array $params;

    public function __construct($router)
    {
        $this->router = $router;
        $this->template = new TemplateAdapter();
        $this->setDefault();
    }

    public function index(array $params)
    {
        $this->setParams($params);
        $this->render('pages/login/login.twig', []);
    }

    public function recover(array $params)
    {
        $this->setParams($params);
        $this->render('pages/login/recover.twig', []);
    }

    public function sign(array $params)
    {
        $this->setParams($params);
        
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        
        $logAcesso = new LogAcesso();
        
        // Validar dados de entrada
        if (empty($email) || empty($password)) {
            $logAcesso->registrarFalhaLogin($email, 'Email ou senha vazios');
            $this->responseJson([
                'success' => false,
                'error' => 'Email e senha são obrigatórios'
            ]);
            return;
        }
        
        // Verificar se o usuário existe e está ativo
        $user = new User();
        $result = $user->emailExist($email);
        
        if (!$result->getResult()) {
            $logAcesso->registrarFalhaLogin($email, 'Usuário não encontrado');
            $this->responseJson([
                'success' => false,
                'error' => 'Usuário não encontrado'
            ]);
            return;
        }
        
        $usuario = $result->getResult()[0];
        
        // Verificar se o usuário está ativo
        if ($usuario['status'] !== 'ativo') {
            $logAcesso->registrarFalhaLogin($email, 'Usuário inativo');
            $this->responseJson([
                'success' => false,
                'error' => 'Usuário inativo. Entre em contato com o administrador.'
            ]);
            return;
        }
        
        // Verificar se o usuário não está bloqueado
        if ($usuario['tipo'] == '4') {
            $logAcesso->registrarFalhaLogin($email, 'Usuário bloqueado');
            $this->responseJson([
                'success' => false,
                'error' => 'Usuário bloqueado. Entre em contato com o administrador.'
            ]);
            return;
        }
        
        // Tentar fazer login
        $logon = new Logon();
        if ($logon->loginByEmail($email, $password)) {
            // Login bem-sucedido
            $logAcesso->registrarLogin($usuario['id'], $email, 'sucesso', 'Login realizado com sucesso');
            
            // Atualizar último acesso
            $user->updateLastAccess($usuario['id']);
            
            // Se marcou "lembrar-me", criar cookie
            if ($remember) {
                $loginCookie = new \Agencia\Close\Services\Login\LoginCookie();
                $loginCookie->createCookie($usuario['id'], $email);
            }
            
            // Buscar permissões do usuário
            $permissao = new Permissao();
            $permissoes = $permissao->getPermissoesDoUsuario($usuario['id']);
            
            // Armazenar permissões na sessão
            $_SESSION[BASE.'user_permissoes'] = $permissoes->getResult();
            
            $this->responseJson([
                'success' => true,
                'message' => 'Login realizado com sucesso',
                'redirect' => DOMAIN . '/'
            ]);
        } else {
            // Login falhou
            $logAcesso->registrarFalhaLogin($email, 'Senha incorreta');
            $this->responseJson([
                'success' => false,
                'error' => 'Email ou senha incorretos'
            ]);
        }
    }

    public function logout(array $params)
    {
        $this->setParams($params);
        
        $usuarioId = $_SESSION[BASE.'user_id'] ?? null;
        $email = $_SESSION[BASE.'user_email'] ?? '';
        
        // Registrar logout no log
        if ($usuarioId) {
            $logAcesso = new LogAcesso();
            $logAcesso->registrarLogout($usuarioId, $email);
        }
        
        // Destruir sessão
        session_destroy();
        
        // Remover cookies
        setcookie("CookieLoginEmail", "", time() - 3600);
        setcookie("CookieLoginHash", "", time() - 3600);
        
        // Se for requisição Ajax, retornar JSON
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            $this->responseJson([
                'success' => true,
                'message' => 'Logout realizado com sucesso',
                'redirect' => DOMAIN . '/login'
            ]);
        } else {
            // Redirecionamento normal
            header('Location: ' . DOMAIN . '/login');
            exit();
        }
    }
    
    public function checkPermission(array $params)
    {
        $this->setParams($params);
        
        $modulo = $_POST['modulo'] ?? '';
        $acao = $_POST['acao'] ?? '';
        
        if (empty($modulo) || empty($acao)) {
            $this->responseJson([
                'success' => false,
                'error' => 'Módulo e ação são obrigatórios'
            ]);
            return;
        }
        
        $usuarioId = $_SESSION[BASE.'user_id'] ?? null;
        
        if (!$usuarioId) {
            $this->responseJson([
                'success' => false,
                'error' => 'Usuário não autenticado'
            ]);
            return;
        }
        
        $permissao = new Permissao();
        $temPermissao = $permissao->usuarioTemPermissao($usuarioId, $modulo, $acao);
        
        $this->responseJson([
            'success' => true,
            'hasPermission' => $temPermissao
        ]);
    }

    // Métodos auxiliares
    protected function render(string $link, array $arrayData = [])
    {
        $arrayDataWithDefault = $this->mergeWithDefault($arrayData);
        echo $this->template->render($link, $arrayDataWithDefault);
    }

    protected function responseJson($response){
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    private function mergeWithDefault($arrayToMerge): array
    {
        return array_merge($this->dataDefault, $arrayToMerge);
    }

    protected function setParams(array $params)
    {
        $this->params = $params;
        $this->setDefault();
    }

    private function setDefault()
    {
        $this->dataDefault['BASE'] = BASE;
        $this->dataDefault['mobile'] = $this->isMobileDevice();
        $this->dataDefault['currentUrl'] = $this->getCurrentUrl();
        $this->dataDefault['session'] = $_SESSION;
        $this->dataDefault['cookie'] = $_COOKIE;
        $this->dataDefault['get'] = $_GET;
    }

    private function getCurrentUrl(): string
    {
        return parse_url((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]", PHP_URL_PATH);
    }

    private function isMobileDevice(): bool
    {
        $checkDevice = new CheckDevice();
        return $checkDevice->isMobileDevice();
    }
}
