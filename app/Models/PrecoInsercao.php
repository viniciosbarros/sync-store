<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrecoInsercao extends Model
{
    protected $table = 'preco_insercao';

    protected $fillable = [
        'produto_insercao_id',
        'valor',
        'moeda',
        'desconto_percentual',
        'acrescimo_percentual',
        'valor_promocional',
        'data_inicio_promocao',
        'data_fim_promocao',
        'origem',
        'tipo_cliente',
        'vendedor_responsavel',
        'observacao',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'valor_promocional' => 'decimal:2',
        'desconto_percentual' => 'decimal:2',
        'acrescimo_percentual' => 'decimal:2',
        'data_inicio_promocao' => 'date',
        'data_fim_promocao' => 'date',
    ];

    public function produto(): BelongsTo
    {
        return $this->belongsTo(ProdutoInsercao::class, 'produto_insercao_id');
    }
}
