<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SincronizacaoApiTest extends TestCase {

    use RefreshDatabase;

    public function test_sincronizar_produtos_retorna_sucesso(): void
    {
        $response = $this->postJson('/api/sincronizar/produtos');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'inseridos',
                'atualizados',
                'removidos',
            ]);
    }

    public function test_sincronizar_precos_retorna_sucesso(): void
    {
        $this->postJson('/api/sincronizar/produtos');

        $response = $this->postJson('/api/sincronizar/precos');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'inseridos',
                'atualizados',
                'removidos',
            ]);
    }

    public function test_produtos_precos_retorna_paginado(): void
    {
        $this->postJson('/api/sincronizar/produtos');
        $this->postJson('/api/sincronizar/precos');

        $response = $this->getJson('/api/produtos-precos');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => [
                    'current_page',
                    'per_page',
                    'from',
                    'to',
                    'prev_page_url',
                    'next_page_url',
                ],
            ]);
    }

    public function test_produtos_precos_aceita_parametros_paginacao(): void
    {
        $this->postJson('/api/sincronizar/produtos');
        $this->postJson('/api/sincronizar/precos');

        $response = $this->getJson('/api/produtos-precos?page=1&per_page=5');

        $response->assertStatus(200);
        $this->assertLessThanOrEqual(5, count($response->json('data')));
        $this->assertEquals(5, $response->json('meta.per_page'));
    }

    public function test_sincronizacao_produtos_insere_dados_corretos(): void
    {
        $response = $this->postJson('/api/sincronizar/produtos');

        $response->assertStatus(200);
        $this->assertGreaterThan(0, $response->json('inseridos'));

        $listResponse = $this->getJson('/api/produtos-precos');
        $produtos = $listResponse->json('data');
        $this->assertNotEmpty($produtos);

        $primeiro = $produtos[0];
        $this->assertArrayHasKey('codigo', $primeiro);
        $this->assertArrayHasKey('nome', $primeiro);
        $this->assertArrayHasKey('categoria', $primeiro);
    }
}
