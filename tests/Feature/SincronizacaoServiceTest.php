<?php

namespace Tests\Feature;

use App\Models\PrecoInsercao;
use App\Models\ProdutoInsercao;
use App\Services\SincronizacaoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SincronizacaoServiceTest extends TestCase {

    use RefreshDatabase;

    public function test_sincronizar_produtos_evita_duplicidade(): void
    {
        $service = app(SincronizacaoService::class);

        $result1 = $service->sincronizarProdutos();
        $result2 = $service->sincronizarProdutos();

        $this->assertGreaterThan(0, $result1['inseridos']);
        $this->assertEquals(0, $result2['inseridos']);
        $this->assertEquals(0, $result2['atualizados']);
    }

    public function test_sincronizar_produtos_remove_inativos(): void
    {
        $service = app(SincronizacaoService::class);
        $service->sincronizarProdutos();

        $ativos = ProdutoInsercao::count();
        $this->assertLessThan(12, $ativos);
    }

    public function test_sincronizar_precos_associa_ao_produto(): void
    {
        $service = app(SincronizacaoService::class);
        $service->sincronizarProdutos();
        $service->sincronizarPrecos();

        $produtoComPreco = ProdutoInsercao::has('preco')->first();
        $this->assertNotNull($produtoComPreco);
        $this->assertNotNull($produtoComPreco->preco->valor);
    }
}
