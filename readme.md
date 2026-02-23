# Sync Store

Aplicação backend para processamento, transformação e sincronização de dados de produtos e preços, utilizando Views SQL para padronização e API REST para disponibilização dos dados.

## Requisitos

- Docker
- Docker Compose

## Como executar

### 1. Executar a aplicação

Comando no terminal (primeira execução):
```bash
cp .env.example .env
docker compose build
docker compose run --rm app php artisan key:generate
docker compose up
```

API disponivel em http://localhost:8000

### 2. Preparar o banco de dados (primeira execução)

Comando no terminal:

```bash
docker compose run --rm app php artisan migrate --force
```

O comando `migrate` cria as tabelas e carrega os dados base a partir de `database/base_data_sql_convertido_sqlite.sql`.

### 3. Sincronizar dados

```bash
# Sincronizar produtos (base -> destino)
curl -X POST http://localhost:8000/api/sincronizar/produtos

# Sincronizar preços (base -> destino)
curl -X POST http://localhost:8000/api/sincronizar/precos
```

### 4. Executar testes

```bash
docker compose run --rm app php artisan test
```

### 5. Postman

Importe a collection em **Postman** -> File -> Import -> `postman/Sync-Store-API.postman_collection.json`

A variável `base_url` (padrão: `http://localhost:8000`) pode ser alterada no .env.

---

## Endpoints da API

### GET /api/health

Verifica a saúde da aplicação e a conectividade com o banco de dados.

**Resposta HTTP 200: saudável:**
```json
{
  "status": "healthy",
  "database": "connected"
}
```

**Resposta HTTP 503: banco indisponível:**
```json
{
  "status": "unhealthy",
  "database": "disconnected"
}
```

---

### POST /api/sincronizar/produtos

Executa a transformação e sincronização dos dados de `produtos_base` para `produto_insercao`.

**Resposta HTTP 200:**
```json
{
  "message": "Sincronização de produtos concluída.",
  "inseridos": 10,
  "atualizados": 0,
  "removidos": 0
}
```

---

### POST /api/sincronizar/precos

Executa a transformação e sincronização dos dados de `precos_base` para `preco_insercao`.

**Resposta HTTP 200:**
```json
{
  "message": "Sincronização de preços concluída.",
  "inseridos": 11,
  "atualizados": 0,
  "removidos": 0
}
```

---

### GET /api/produtos-precos

Retorna os produtos sincronizados com seus precos, de forma paginada.

**Query string (opcional):**
| Parâmetro | Tipo | Padrão | Descrição |
|-----------|------|--------|-----------|
| `page` | int | 1 | Página atual |
| `per_page` | int | 15 | Itens por página (1–100) |

**Exemplo:** `GET /api/produtos-precos?page=1&per_page=10`

**Resposta HTTP 200:**
```json
{
  "data": [
    {
      "id": 1,
      "codigo": "PRD001",
      "nome": "Teclado Mecânico RGB",
      "categoria": "PERIFERICOS",
      "subcategoria": "TECLADOS",
      "descricao": "Teclado com iluminação RGB e switches azuis",
      "fabricante": "HyperTech",
      "modelo": "HT-KEY-RGB",
      "cor": "Preto",
      "peso": "1.2kg",
      "largura": "45cm",
      "altura": "5cm",
      "profundidade": "15cm",
      "unidade": "UN",
      "data_cadastro": "2025-10-10",
      "preco": {
        "valor": 499.9,
        "valor_promocional": 474.9,
        "moeda": "BRL"
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "from": 1,
    "to": 10,
    "prev_page_url": null,
    "next_page_url": "http://localhost:8000/api/produtos-precos?page=2"
  }
}
```

---

## Arquitetura

### Tabelas de origem
- `produtos_base` – dados brutos de produtos
- `precos_base` – dados brutos de preços

### Tabelas de destino
- `produto_insercao` – produtos processados
- `preco_insercao` – preços processados (FK para `produto_insercao`)

### Views SQL
- `vw_produtos_processados` – normalização e filtro de produtos ativos (`prod_atv = 1`)
- `vw_precos_processados` – normalização e filtro de preços ativos (`prc_status = 'ativo'`)

### Paginação (simplePaginate)
A API usa `simplePaginate` em vez de `paginate` completo: evita o `COUNT(*)` no banco, que fica custoso em tabelas grandes. A navegação usa `next_page_url` e `prev_page_url`. Os parâmetros `page` e `per_page` seguem iguais.

### Fluxo de sincronização
1. Dados são lidos das views (já normalizados)
2. Inserção de novos registros
3. Atualização apenas quando há alteração
4. Remoção de registros que não existem mais na origem (ex.: produtos inativos)

- Script de referência (formato original): `base_scripts.sql` na raiz
- Dados base para SQLite: `database/base_data_sql_convertido_sqlite.sql` (executado pela migration)

---

## Stack

- PHP 8.4
- Laravel 12
- SQLite
- Docker / Docker Compose
