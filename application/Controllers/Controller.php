<?php

namespace Agencia\Close\Controllers;

use Agencia\Close\Adapters\TemplateAdapter;
use Agencia\Close\Helpers\Device\CheckDevice;
use Agencia\Close\Middleware\MiddlewareCollection;
use Agencia\Close\Helpers\Result;
use Agencia\Close\Conn\Read;
use Agencia\Close\Helpers\User\ViewPermissionHelper;
use Agencia\Close\Helpers\User\MenuPermissionHelper;
use Agencia\Close\Helpers\User\ButtonPermissionHelper;
use CoffeeCode\Router\Router;

class Controller
{
    protected TemplateAdapter $template;
    private array $dataDefault = [];
    protected Router $router;
    protected array $params;
    protected Result $result;

    public function __construct($router)
    {
        $this->router = $router;
        $this->template = new TemplateAdapter();
        $this->middleware();
    }

    private function middleware()
    {
        $middlewares = new MiddlewareCollection();
        $middlewares->default();
        $middlewares->run();
    }

    // Método para verificar sessão - deve ser chamado pelos controllers que precisam
    protected function checkSession()
    {
        if (empty($_SESSION[BASE.'user_id']) && strpos($this->getCurrentUrl(), '/login') === false) {
            $this->redirectUrlToLogin();
            exit;
        }
    }

    protected function redirectUrlToLogin()
    {
        header('Location: ' . DOMAIN . '/login');
        exit;
    }

    private function isMobileDevice(): bool
    {
        $checkDevice = new CheckDevice();
        return $checkDevice->isMobileDevice();
    }

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

    protected function getCurrentUrl(): string
    {
        return  parse_url((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]", PHP_URL_PATH);
    }

    private function setDefault()
    {
        $this->dataDefault['BASE'] = BASE;
        $this->dataDefault['mobile'] = $this->isMobileDevice();
        $this->dataDefault['currentUrl'] = $this->getCurrentUrl();
        $this->dataDefault['session'] = $_SESSION;
        $this->dataDefault['cookie'] = $_COOKIE;
        $this->dataDefault['get'] = $_GET;
        
        // Adicionar dados de permissões para todos os templates
        $this->dataDefault['permissions'] = ViewPermissionHelper::getPermissionsForTemplate();
        $this->dataDefault['menu_permissions'] = MenuPermissionHelper::getMenuVisibility();
        $this->dataDefault['button_permissions'] = ButtonPermissionHelper::getButtonVisibility();
    }

    protected function getDefault(): array
    {
        return $this->dataDefault;
    }

    protected function redirectUrl(string $url)
    {
        header('Location: '. $url);
    }
}