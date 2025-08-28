<?php

namespace Agencia\Close\Controllers\Home;
use Agencia\Close\Controllers\Controller;
use Agencia\Close\Services\Home\HomeStatsService;

class HomeController extends Controller
{	

  public function index($params)
  {
    $this->setParams($params);
    $this->checkSession();
    
    // Obter estatísticas do dashboard
    $homeStatsService = new HomeStatsService();
    $stats = $homeStatsService->getDashboardStats();
    
    $this->render('pages/home/home.twig', [
      'menu' => 'home',
      'stats' => $stats
    ]);
  }

}