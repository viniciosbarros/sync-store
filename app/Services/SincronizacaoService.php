<?php

namespace App\Services;

use App\Models\PrecoInsercao;
use App\Models\ProdutoInsercao;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SincronizacaoService
{
    public function sincronizarProdutos(): array
    {
        $start = microtime(true);
        Log::info('Sincronização de produtos iniciada');

        try {
            $inseridos = 0;
            $atualizados = 0;
            $removidos = 0;

            $produtosView = DB::select('SELECT * FROM vw_produtos_processados');
            $codigosNaView = collect($produtosView)->pluck('codigo')->toArray();

            foreach ($produtosView as $row) {
                $produto = ProdutoInsercao::where('codigo', $row->codigo)->first();

                $dataCadastro = $this->parseDate($row->data_cadastro_origem ?? null);

                $payload = [
                    'codigo' => $row->codigo,
                    'nome' => $row->nome,
                    'categoria' => $row->categoria,
                    'subcategoria' => $row->subcategoria,
                    'descricao' => $row->descricao,
                    'fabricante' => $row->fabricante,
                    'modelo' => $row->modelo,
                    'cor' => $row->cor,
                    'peso' => $row->peso,
                    'largura' => $row->largura,
                    'altura' => $row->altura,
                    'profundidade' => $row->profundidade,
                    'unidade' => $row->unidade ?? 'UN',
                    'data_cadastro' => $dataCadastro,
                ];

                if ($produto) {
                    if ($this->produtoAlterado($produto, $payload)) {
                        $produto->update($payload);
                        $atualizados++;
                    }
                } else {
                    ProdutoInsercao::create($payload);
                    $inseridos++;
                }
            }

            $remover = ProdutoInsercao::whereNotIn('codigo', $codigosNaView)->get();
            foreach ($remover as $p) {
                $p->delete();
                $removidos++;
            }

            $duration = round((microtime(true) - $start) * 1000);
            Log::info('Sincronização de produtos concluída', [
                'inseridos' => $inseridos,
                'atualizados' => $atualizados,
                'removidos' => $removidos,
                'duration_ms' => $duration,
            ]);

            return [
                'inseridos' => $inseridos,
                'atualizados' => $atualizados,
                'removidos' => $removidos,
            ];
        } catch (\Throwable $e) {
            Log::error('Falha na sincronização de produtos', [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            throw $e;
        }
    }

    public function sincronizarPrecos(): array
    {
        $start = microtime(true);
        Log::info('Sincronização de preços iniciada');

        try {
            $inseridos = 0;
            $atualizados = 0;
            $removidos = 0;

            $precosView = DB::select('SELECT * FROM vw_precos_processados');

            foreach ($precosView as $row) {
                if ($row->valor === null) {
                    continue;
                }

                $produto = ProdutoInsercao::where('codigo', $row->codigo_produto)->first();
                if (!$produto) {
                    continue;
                }

                $valorFinal = $row->valor_promocional ?? $row->valor;
                $desconto = $this->parsePercentual($row->desconto_percentual);
                $acrescimo = $this->parsePercentual($row->acrescimo_percentual);
                $dtIni = $this->parseDate($row->data_inicio_promocao);
                $dtFim = $this->parseDate($row->data_fim_promocao);

                $payload = [
                    'produto_insercao_id' => $produto->id,
                    'valor' => $row->valor,
                    'moeda' => $row->moeda ?? 'BRL',
                    'desconto_percentual' => $desconto,
                    'acrescimo_percentual' => $acrescimo,
                    'valor_promocional' => $row->valor_promocional,
                    'data_inicio_promocao' => $dtIni,
                    'data_fim_promocao' => $dtFim,
                    'origem' => $row->origem,
                    'tipo_cliente' => $row->tipo_cliente,
                    'vendedor_responsavel' => $row->vendedor_responsavel,
                    'observacao' => $row->observacao,
                ];

                $preco = PrecoInsercao::where('produto_insercao_id', $produto->id)->first();

                if ($preco) {
                    if ($this->precoAlterado($preco, $payload)) {
                        $preco->update($payload);
                        $atualizados++;
                    }
                } else {
                    PrecoInsercao::create($payload);
                    $inseridos++;
                }
            }

            $produtosComPreco = collect($precosView)
                ->filter(fn ($p) => $p->valor !== null)
                ->pluck('codigo_produto')
                ->unique()
                ->toArray();

            $produtosIds = ProdutoInsercao::whereIn('codigo', $produtosComPreco)->pluck('id');
            $remover = PrecoInsercao::whereNotIn('produto_insercao_id', $produtosIds)->get();
            foreach ($remover as $p) {
                $p->delete();
                $removidos++;
            }

            $duration = round((microtime(true) - $start) * 1000);
            Log::info('Sincronização de preços concluída', [
                'inseridos' => $inseridos,
                'atualizados' => $atualizados,
                'removidos' => $removidos,
                'duration_ms' => $duration,
            ]);

            return [
                'inseridos' => $inseridos,
                'atualizados' => $atualizados,
                'removidos' => $removidos,
            ];
        } catch (\Throwable $e) {
            Log::error('Falha na sincronização de preços', [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            throw $e;
        }
    }

    private function produtoAlterado(ProdutoInsercao $p, array $payload): bool
    {
        foreach ($payload as $key => $value) {
            $current = $p->{$key};
            if ($current instanceof Carbon) {
                $current = $current->format('Y-m-d');
            }
            if ($value instanceof Carbon) {
                $value = $value->format('Y-m-d');
            }
            if ((string) $current !== (string) $value) {
                return true;
            }
        }
        return false;
    }

    private function precoAlterado(PrecoInsercao $p, array $payload): bool
    {
        $compare = ['valor', 'moeda', 'valor_promocional', 'desconto_percentual', 'acrescimo_percentual'];
        foreach ($compare as $key) {
            $a = $p->{$key};
            $b = $payload[$key] ?? null;
            if ($a != $b) {
                return true;
            }
        }
        return false;
    }

    private function parsePercentual(?string $value): ?float
    {
        if ($value === null || trim($value) === '') {
            return null;
        }
        $num = (float) preg_replace('/[^\d,.-]/', '', str_replace(',', '.', $value));
        return $num ?: null;
    }

    private function parseDate(?string $value): ?Carbon
    {
        if ($value === null || trim($value) === '') {
            return null;
        }
        $value = trim($value);
        $formats = ['Y/m/d', 'Y-m-d', 'd/m/Y', 'd-m-Y', 'Y.m.d', 'd.m.Y'];
        foreach ($formats as $fmt) {
            try {
                $d = Carbon::createFromFormat($fmt, $value);
                if ($d !== false) {
                    return $d;
                }
            } catch (\Exception) {
                continue;
            }
        }
        return null;
    }
}
