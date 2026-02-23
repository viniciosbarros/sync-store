<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('produtos_base', function (Blueprint $table) {
            $table->id();
            $table->string('prod_cod', 30)->nullable();
            $table->string('prod_nome', 150)->nullable();
            $table->string('prod_cat', 50)->nullable();
            $table->string('prod_subcat', 50)->nullable();
            $table->text('prod_desc')->nullable();
            $table->string('prod_fab', 100)->nullable();
            $table->string('prod_mod', 50)->nullable();
            $table->string('prod_cor', 30)->nullable();
            $table->text('prod_peso')->nullable();
            $table->text('prod_larg')->nullable();
            $table->text('prod_alt')->nullable();
            $table->text('prod_prof')->nullable();
            $table->string('prod_und', 10)->nullable();
            $table->boolean('prod_atv')->default(true);
            $table->string('prod_dt_cad')->nullable();
        });

        Schema::create('precos_base', function (Blueprint $table) {
            $table->id();
            $table->string('prc_cod_prod', 30)->nullable();
            $table->text('prc_valor')->nullable();
            $table->string('prc_moeda', 10)->nullable();
            $table->text('prc_desc')->nullable();
            $table->text('prc_acres')->nullable();
            $table->text('prc_promo')->nullable();
            $table->string('prc_dt_ini_promo')->nullable();
            $table->string('prc_dt_fim_promo')->nullable();
            $table->string('prc_dt_atual')->nullable();
            $table->string('prc_origem', 50)->nullable();
            $table->string('prc_tipo_cli', 30)->nullable();
            $table->string('prc_vend_resp', 100)->nullable();
            $table->text('prc_obs')->nullable();
            $table->string('prc_status', 20)->nullable();
        });

        $sqlPath = database_path('base_data_sql_convertido_sqlite.sql');
        if (file_exists($sqlPath)) {
            DB::unprepared(file_get_contents($sqlPath));
        }
    }

    public function down(): void {
        Schema::dropIfExists('produtos_base');
        Schema::dropIfExists('precos_base');
    }
};
