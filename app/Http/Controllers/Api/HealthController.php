<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $databaseOk = false;

        try {
            DB::connection()->getPdo();
            DB::connection()->getDatabaseName();
            DB::select('SELECT 1');
            $databaseOk = true;
        } catch (\Throwable $e) {
            Log::error('Health check: banco de dados indisponível', [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
            ]);
        }

        $status = $databaseOk ? 'healthy' : 'unhealthy';
        $httpStatus = $databaseOk ? 200 : 503;

        return response()->json([
            'status' => $status,
            'database' => $databaseOk ? 'connected' : 'disconnected',
        ], $httpStatus);
    }
}
