<?php

namespace Agencia\Close\Controllers\Produtos;
use Agencia\Close\Models\Produtos\Cor;
use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Produtos\Produtos;
use Agencia\Close\Models\Produtos\Categorias;
use Agencia\Close\Models\Produtos\Empresas;

class ProdutosController extends Controller
{

  private Categorias $categories;
  public int $id = 0;

  public function index($params)
  {
    $this->setParams($params);

    $produtos = new Produtos();
    $produtos = $produtos->getProdutos()->getResult();

    $this->render('pages/produtos/index.twig', ['menu' => 'produtos', 'produtos' => $produtos]);
  }

  public function criar($params)
  {
    $this->setParams($params);

    $cores = new Cor();
    $cores = $cores->getCores()->getResult();

    $empresas = new Empresas();
    $empresas = $empresas->getEmpresas();
    $empresas = $empresas->getResult();

    $categorias_lista = $this->getCategoryList();

    $this->render('pages/produtos/form.twig', [
      'menu' => 'produtos',
      'cores' => $cores,
      'empresas' => $empresas,
      'categorias' => $categorias_lista,
      'blacklist_empresas' => []
    ]);
  }

  public function editar($params)
  {
    $this->setParams($params);

    $produto = new Produtos();
    $produto = $produto->getProduto($this->params['id']);
    $produto = $produto->getResult()[0];

    $variations = new Produtos();
    $variations = $variations->getProdutoVariations($this->params['id']);
    $variations = $variations->getResult();

    $precos = new Produtos();
    $precos = $precos->getProdutoPrecos($this->params['id']);
    $precos = $precos->getResult();

    $empresas = new Empresas();
    $empresas = $empresas->getEmpresas();
    $empresas = $empresas->getResult();

    // Carregar empresas da blacklist
    $blacklist = new Produtos();
    $blacklist_empresas = $blacklist->getProdutoBlacklist($this->params['id']);
    $blacklist_empresas = $blacklist_empresas->getResult();
    $blacklist_ids = array_column($blacklist_empresas, 'id_empresa');

    $cores = new Cor();
    $cores = $cores->getCores()->getResult();

    $imagens = new Produtos();
    $imagem = $imagens->getProdutoImages($this->params['id'])->getResult();

    $categorias_lista = $this->getCategoryList();

    $this->render('pages/produtos/form.twig', [
      'menu' => 'produtos',
      'produto' => $produto,
      'imagens' => $imagem,
      'variations' => $variations,
      'precos' => $precos,
      'empresas' => $empresas,
      'cores' => $cores,
      'categorias' => $categorias_lista,
      'blacklist_empresas' => $blacklist_ids
    ]);
  }

  public function obterListaCores()
  {
    $cores = array(
      array("nome" => "Azul", "hex" => "#0000FF"),
      array("nome" => "Azul Celeste", "hex" => "#87CEEB"),
      array("nome" => "Azul Fraco", "hex" => "#ADD8E6"),
      array("nome" => "Azul Marinho", "hex" => "#001F54"),
      array("nome" => "Ameixa", "hex" => "#8E4585"),
      array("nome" => "Amarelo", "hex" => "#FFFF00"),
      array("nome" => "Azeitona", "hex" => "#6B8E23"),
      array("nome" => "Branco", "hex" => "#FFFFFF"),
      array("nome" => "Bronze", "hex" => "#CD7F32"),
      array("nome" => "Champagne", "hex" => "#F7E7CE"),
      array("nome" => "Ciano", "hex" => "#00FFFF"),
      array("nome" => "Cinza", "hex" => "#808080"),
      array("nome" => "Dourado", "hex" => "#FFD700"),
      array("nome" => "Esmeralda", "hex" => "#50C878"),
      array("nome" => "Fúcsia", "hex" => "#FF00FF"),
      array("nome" => "Garnet", "hex" => "#733635"),
      array("nome" => "Gelo", "hex" => "#D4F4F4"),
      array("nome" => "Grafite", "hex" => "#36454F"),
      array("nome" => "Ígneo", "hex" => "#FF4500"),
      array("nome" => "Índigo", "hex" => "#4B0082"),
      array("nome" => "Laranja", "hex" => "#FFA500"),
      array("nome" => "Lavanda", "hex" => "#E6E6FA"),
      array("nome" => "Lima", "hex" => "#BFFF00"),
      array("nome" => "Lódo", "hex" => "#7C881A"),
      array("nome" => "Malva", "hex" => "#B784A7"),
      array("nome" => "Magenta", "hex" => "#FF00FF"),
      array("nome" => "Marrom", "hex" => "#A52A2A"),
      array("nome" => "Mint", "hex" => "#3EB489"),
      array("nome" => "Oliva", "hex" => "#808000"),
      array("nome" => "Orquídea", "hex" => "#DA70D6"),
      array("nome" => "Pink", "hex" => "#FF007F"),
      array("nome" => "Porcelana", "hex" => "#EFF2F3"),
      array("nome" => "Preto", "hex" => "#000000"),
      array("nome" => "Prata", "hex" => "#C0C0C0"),
      array("nome" => "Pêssego", "hex" => "#FFE5B4"),
      array("nome" => "Petróleo", "hex" => "#005E7D"),
      array("nome" => "Rosa", "hex" => "#FC5CAC"),
      array("nome" => "Roxo", "hex" => "#800080"),
      array("nome" => "Rose", "hex" => "#FFC0CB"),
      array("nome" => "Rubi", "hex" => "#E0115F"),
      array("nome" => "Salmão", "hex" => "#FA8072"),
      array("nome" => "Teal", "hex" => "#008080"),
      array("nome" => "Titanium", "hex" => "#878787"),
      array("nome" => "Tijolo", "hex" => "#CB4154"),
      array("nome" => "Tomate", "hex" => "#FF6347"),
      array("nome" => "Turquesa", "hex" => "#40E0D0"),
      array("nome" => "Vermelho", "hex" => "#FF0000"),
      array("nome" => "Verde", "hex" => "#00FF00"),
      array("nome" => "Verde Escuro", "hex" => "#03300b"),
      array("nome" => "Verde limão", "hex" => "#32CD32"),
      array("nome" => "Verde Musgo", "hex" => "#232d11"),
      array("nome" => "Vinho", "hex" => "#800020"),
      array("nome" => "Violeta", "hex" => "#8A2BE2"),
      array("nome" => "Zinco", "hex" => "#7B788F"),
      array("nome" => "Íris", "hex" => "#5A4FCF")
    );

    return $cores;
  }

  //CRIAR O PRODUTO EM RASCUNHO
  public function save_draft($params)
  {
    $this->setParams($params);
    $produtos = new Produtos();
    $result = $produtos->createDraft($this->params);
    $produto_draft = $result->getResult()[0];

    header("Content-Type: application/json");
    echo json_encode($produto_draft);
  }

  //SALVA O EDITAR DO PRODUTO
  public function save_edit($params)
  {
    $this->setParams($params);
    $produtos = new Produtos();
    $result = $produtos->saveEdit($this->params)->getResult();

    if ($result) {
      echo 'success';
    } else {
      echo 'error';
    }
  }

  //EXCLUI O PRODUTO
  public function excluir_produto($params)
  {
    $this->setParams($params);
    $excluir = new Produtos();
    $excluir->excluirProduto($this->params['id']);
    if ($excluir) {
      header("Content-Type: application/json");
      echo json_encode([
        'success' => true,
        'message' => 'Produto excluído com sucesso'
      ]);
    } else {
      header("Content-Type: application/json");
      echo json_encode([
        'success' => false,
        'message' => 'Erro ao excluir produto'
      ]);
    }    
  }


  public function getCategoryList(): array
  {
    $this->categories = new Categorias();
    $result = $this->categories->getCategory();
    if ($result->getResult()) {
      return $this->buildTree($result->getResult());
    } else {
      return [];
    }
  }

  public function buildTree($categories, $parentId = 0): array
  {
    $branch = array();
    foreach ($categories as $item) {
      if ($item['parent'] == $parentId) {
        $children = $this->buildTree($categories, $item['id']);
        if ($children) {
          $item['children'] = $children;
        }
        $branch[] = $item;
      }
    }
    return $branch;
  }

  // ESTOQUE BAIXO
  public function estoqueBaixo($params)
  {
    $this->setParams($params);
    
    $produtos = new Produtos();
    $produtos_estoque_baixo = $produtos->getProdutosEstoqueBaixo();
    $produtos_estoque_baixo = $produtos_estoque_baixo->getResult();

    $this->render('pages/produtos/estoque-baixo.twig', [
      'menu' => 'produtos',
      'produtos' => $produtos_estoque_baixo
    ]);
  }

  // BUSCAR PRODUTO PARA AJAX
  public function buscarProduto($params)
  {
    $this->setParams($params);
    
    $produtos = new Produtos();
    $produto = $produtos->getProduto($this->params['id']);
    $produto = $produto->getResult();

    if ($produto) {
      header("Content-Type: application/json");
      echo json_encode([
        'success' => true,
        'produto' => $produto[0]
      ]);
    } else {
      header("Content-Type: application/json");
      echo json_encode([
        'success' => false,
        'message' => 'Produto não encontrado'
      ]);
    }
  }

  // ENTRADA DE ESTOQUE
  public function entradaEstoque($params)
  {
    $this->setParams($params);
    
    $produtos = new Produtos();
    $result = $produtos->adicionarEstoque($this->params);

    if ($result) {
      header("Content-Type: application/json");
      echo json_encode([
        'success' => true,
        'message' => 'Estoque atualizado com sucesso'
      ]);
    } else {
      header("Content-Type: application/json");
      echo json_encode([
        'success' => false,
        'message' => 'Erro ao atualizar estoque'
      ]);
    }
  }

  // EXPORTAR ESTOQUE BAIXO
  public function exportarEstoqueBaixo($params)
  {
    $this->setParams($params);
    
    $produtos = new Produtos();
    $produtos_estoque_baixo = $produtos->getProdutosEstoqueBaixo();
    $produtos_estoque_baixo = $produtos_estoque_baixo->getResult();

    // Configurar headers para download CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=estoque-baixo-' . date('Y-m-d') . '.csv');

    // Criar arquivo CSV
    $output = fopen('php://output', 'w');
    
    // Cabeçalho do CSV
    fputcsv($output, [
      'ID',
      'Nome do Produto',
      'SKU',
      'Categoria',
      'Estoque Atual',
      'Estoque Mínimo',
      'Status',
      'Data de Criação'
    ], ';');

    // Dados dos produtos
    foreach ($produtos_estoque_baixo as $produto) {
      fputcsv($output, [
        $produto['id'],
        $produto['nome'],
        $produto['SKU'] ?? '',
        $produto['categoria_nome'] ?? '',
        $produto['estoque_atual'] ?? 0,
        $produto['estoque_minimo'] ?? 0,
        $produto['status'],
        $produto['date_create']
      ], ';');
    }

    fclose($output);
    exit;
  }
}