<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {

    public function up(): void {
        DB::statement("
            CREATE VIEW vw_produtos_processados AS
            SELECT
                TRIM(UPPER(prod_cod)) AS codigo,
                TRIM(prod_nome) AS nome,
                TRIM(UPPER(prod_cat)) AS categoria,
                TRIM(UPPER(prod_subcat)) AS subcategoria,
                TRIM(prod_desc) AS descricao,
                TRIM(prod_fab) AS fabricante,
                TRIM(prod_mod) AS modelo,
                TRIM(prod_cor) AS cor,
                TRIM(REPLACE(REPLACE(prod_peso, ' ', ''), ',', '.')) AS peso,
                TRIM(REPLACE(REPLACE(prod_larg, ' ', ''), ',', '.')) AS largura,
                TRIM(REPLACE(REPLACE(prod_alt, ' ', ''), ',', '.')) AS altura,
                TRIM(REPLACE(REPLACE(prod_prof, ' ', ''), ',', '.')) AS profundidade,
                COALESCE(TRIM(UPPER(prod_und)), 'UN') AS unidade,
                prod_dt_cad AS data_cadastro_origem,
                id AS prod_id_origem
            FROM produtos_base
            WHERE prod_atv = 1
            AND TRIM(prod_cod) != ''
        ");

        DB::statement("
            CREATE VIEW vw_precos_processados AS
            SELECT
                TRIM(UPPER(prc_cod_prod)) AS codigo_produto,
                CASE
                    WHEN TRIM(prc_valor) = '' OR LOWER(TRIM(prc_valor)) = 'sem preço' OR prc_valor IS NULL THEN NULL
                    WHEN prc_valor LIKE '%,%' THEN CAST(REPLACE(REPLACE(REPLACE(TRIM(prc_valor), ' ', ''), '.', ''), ',', '.') AS REAL)
                    ELSE CAST(REPLACE(REPLACE(TRIM(prc_valor), ' ', ''), ',', '.') AS REAL)
                END AS valor,
                CASE
                    WHEN TRIM(prc_promo) = '' OR prc_promo IS NULL THEN NULL
                    WHEN prc_promo LIKE '%,%' THEN CAST(REPLACE(REPLACE(REPLACE(TRIM(prc_promo), ' ', ''), '.', ''), ',', '.') AS REAL)
                    ELSE CAST(REPLACE(REPLACE(TRIM(prc_promo), ' ', ''), ',', '.') AS REAL)
                END AS valor_promocional,
                COALESCE(TRIM(UPPER(prc_moeda)), 'BRL') AS moeda,
                prc_desc AS desconto_percentual,
                prc_acres AS acrescimo_percentual,
                prc_dt_ini_promo AS data_inicio_promocao,
                prc_dt_fim_promo AS data_fim_promocao,
                TRIM(prc_origem) AS origem,
                TRIM(prc_tipo_cli) AS tipo_cliente,
                TRIM(prc_vend_resp) AS vendedor_responsavel,
                TRIM(prc_obs) AS observacao
            FROM precos_base
            WHERE LOWER(TRIM(prc_status)) = 'ativo'
            AND TRIM(prc_cod_prod) != ''
        "
        );
    }

    public function down(): void {
        DB::statement('DROP VIEW IF EXISTS vw_precos_processados');
        DB::statement('DROP VIEW IF EXISTS vw_produtos_processados');
    }
};
