<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProdutoInsercao;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProdutosPrecosController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $perPage = min(max($perPage, 1), 100);
        $page = max(1, (int) $request->query('page', 1));

        $columns = [
            'id', 'codigo', 'nome', 'categoria', 'subcategoria', 'descricao',
            'fabricante', 'modelo', 'cor', 'peso', 'largura', 'altura',
            'profundidade', 'unidade', 'data_cadastro',
        ];

        $query = ProdutoInsercao::with('preco:id,produto_insercao_id,valor,valor_promocional,moeda')
            ->select($columns)
            ->orderBy('codigo');

        $paginator = $query->simplePaginate($perPage, $columns, 'page', $page);

        $data = $paginator->getCollection()->map(function (ProdutoInsercao $produto) {
            $preco = $produto->preco;
            return [
                'id' => $produto->id,
                'codigo' => $produto->codigo,
                'nome' => $produto->nome,
                'categoria' => $produto->categoria,
                'subcategoria' => $produto->subcategoria,
                'descricao' => $produto->descricao,
                'fabricante' => $produto->fabricante,
                'modelo' => $produto->modelo,
                'cor' => $produto->cor,
                'peso' => $produto->peso,
                'largura' => $produto->largura,
                'altura' => $produto->altura,
                'profundidade' => $produto->profundidade,
                'unidade' => $produto->unidade,
                'data_cadastro' => $produto->data_cadastro?->format('Y-m-d'),
                'preco' => $preco ? [
                    'valor' => (float) $preco->valor,
                    'valor_promocional' => $preco->valor_promocional ? (float) $preco->valor_promocional : null,
                    'moeda' => $preco->moeda,
                ] : null,
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'prev_page_url' => $paginator->previousPageUrl(),
                'next_page_url' => $paginator->nextPageUrl(),
            ],
        ]);
    }
}
