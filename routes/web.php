<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'Sync Store API',
        'docs' => 'Consulte o readme.md para documentação dos endpoints.',
    ]);
});
