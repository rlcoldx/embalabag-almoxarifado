<?php

namespace Agencia\Close\Adapters;

use Agencia\Close\Adapters\Twig\PayStatus;
use Agencia\Close\Adapters\Twig\DayTranslate;
use Agencia\Close\Adapters\Twig\MonthTranslate;
use Agencia\Close\Adapters\Twig\FilterHash;
use Agencia\Close\Helpers\String\Strings;
use Agencia\Close\Helpers\Link\Url;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;

class TemplateAdapter
{
    private $twig;
    
    public function __construct()
    {
        $loader = new FilesystemLoader('view');
        $this->twig = new Environment($loader, [
            'cache' => false,
        ]);
        $this->twig->addExtension(new FilterHash());
        $this->twig->addExtension(new MonthTranslate());
        $this->twig->addExtension(new DayTranslate());
        $this->twig->addExtension(new PayStatus());
        $this->globals();

        return $this->twig;
    }

    public function render($view, array $data = []): string
    {
        return $this->twig->render($view, $data);
    }

    private function globals()
    {
        $this->twig->addGlobal('DOMAIN', DOMAIN);
        $this->twig->addGlobal('PATH', PATH);
        $this->twig->addGlobal('NAME', NAME);
        $this->twig->addGlobal('PRODUCTION', PRODUCTION);
        $this->twig->addGlobal('getCurrentUrl', Url::getCurrentUrl());
        $this->twig->addGlobal('_session', $_SESSION);
        $this->twig->addGlobal('_request', $_REQUEST);
        $this->twig->addGlobal('_post', $_POST);
        $this->twig->addGlobal('_get', $_GET);
        $this->twig->addGlobal('_cookie', $_COOKIE);
        $this->twig->addGlobal('VERSION', defined('VERSION') ? VERSION : '1.0.0');
    }
}