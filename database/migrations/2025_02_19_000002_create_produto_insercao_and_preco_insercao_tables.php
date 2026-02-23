<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('produto_insercao', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 30)->unique();
            $table->string('nome', 150);
            $table->string('categoria', 50)->nullable();
            $table->string('subcategoria', 50)->nullable();
            $table->text('descricao')->nullable();
            $table->string('fabricante', 100)->nullable();
            $table->string('modelo', 50)->nullable();
            $table->string('cor', 30)->nullable();
            $table->string('peso', 50)->nullable();
            $table->string('largura', 50)->nullable();
            $table->string('altura', 50)->nullable();
            $table->string('profundidade', 50)->nullable();
            $table->string('unidade', 10)->default('UN');
            $table->date('data_cadastro')->nullable();
            $table->timestamps();
        });

        Schema::create('preco_insercao', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produto_insercao_id')->constrained('produto_insercao')->cascadeOnDelete();
            $table->decimal('valor', 12, 2);
            $table->string('moeda', 10)->default('BRL');
            $table->decimal('desconto_percentual', 5, 2)->nullable();
            $table->decimal('acrescimo_percentual', 5, 2)->nullable();
            $table->decimal('valor_promocional', 12, 2)->nullable();
            $table->date('data_inicio_promocao')->nullable();
            $table->date('data_fim_promocao')->nullable();
            $table->string('origem', 50)->nullable();
            $table->string('tipo_cliente', 30)->nullable();
            $table->string('vendedor_responsavel', 100)->nullable();
            $table->text('observacao')->nullable();
            $table->timestamps();
        });

        Schema::table('preco_insercao', function (Blueprint $table) {
            $table->index('produto_insercao_id');
        });
    }

    public function down(): void {
        Schema::dropIfExists('preco_insercao');
        Schema::dropIfExists('produto_insercao');
    }
};
