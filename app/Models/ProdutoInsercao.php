<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProdutoInsercao extends Model
{
    protected $table = 'produto_insercao';

    protected $fillable = [
        'codigo',
        'nome',
        'categoria',
        'subcategoria',
        'descricao',
        'fabricante',
        'modelo',
        'cor',
        'peso',
        'largura',
        'altura',
        'profundidade',
        'unidade',
        'data_cadastro',
    ];

    protected $casts = [
        'data_cadastro' => 'date',
    ];

    public function preco(): HasOne
    {
        return $this->hasOne(PrecoInsercao::class);
    }
}
