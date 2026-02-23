<?php

use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\ProdutosPrecosController;
use App\Http\Controllers\Api\SincronizarController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class);

Route::post('/sincronizar/produtos', [SincronizarController::class, 'produtos']);
Route::post('/sincronizar/precos', [SincronizarController::class, 'precos']);
Route::get('/produtos-precos', [ProdutosPrecosController::class, 'index']);
