<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SincronizacaoService;
use Illuminate\Http\JsonResponse;

class SincronizarController extends Controller
{
    public function __construct(
        private SincronizacaoService $sincronizacaoService
    ) {}

    public function produtos(): JsonResponse
    {
        $resultado = $this->sincronizacaoService->sincronizarProdutos();

        return response()->json([
            'message' => 'Sincronização de produtos concluída.',
            'inseridos' => $resultado['inseridos'],
            'atualizados' => $resultado['atualizados'],
            'removidos' => $resultado['removidos'],
        ]);
    }

    public function precos(): JsonResponse
    {
        $resultado = $this->sincronizacaoService->sincronizarPrecos();

        return response()->json([
            'message' => 'Sincronização de preços concluída.',
            'inseridos' => $resultado['inseridos'],
            'atualizados' => $resultado['atualizados'],
            'removidos' => $resultado['removidos'],
        ]);
    }
}
