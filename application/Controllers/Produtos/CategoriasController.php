<?php

namespace Agencia\Close\Controllers\Produtos;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Produtos\Categorias;

class CategoriasController extends Controller
{

    private Categorias $categories;

    public function index($params)
    {
        $this->setParams($params);

        $semcategorias = new Categorias();
        $semcategorias = $semcategorias->getSemCategorias()->getResult();

        $categorias_lista = $this->getCategoryList();
        $this->render('pages/produtos/categorias.twig', ['menu' => 'produtos', 'categorias' => $categorias_lista, 'semcategorias' => $semcategorias]);
    }


    public function editar($params)
    {
        $this->setParams($params);

        $semcategorias = new Categorias();
        $semcategorias = $semcategorias->getSemCategorias()->getResult();

        $editar = new Categorias();
        $editar = $editar->getCategoriasID($params['id'])->getResultSingle();

        $categorias_lista = $this->getCategoryList();
        $this->render('pages/produtos/categorias.twig', ['menu' => 'produtos', 'categorias' => $categorias_lista, 'semcategorias' => $semcategorias, 'editar' => $editar]);
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

    public function save($params)
    {
        $this->setParams($params);
        $createCategory = new Categorias();
        $createCategory = $createCategory->createCategory($this->params)->getResult();
        if ($createCategory) {echo'success';} else {echo 'error';}
    }

    public function save_edit($params)
    {
        $this->setParams($params);
        $editarCategory = new Categorias();
        $editarCategory = $editarCategory->editarCategory($this->params)->getResult();
        if ($editarCategory){echo 'success';}else{echo 'error';}
    }
}