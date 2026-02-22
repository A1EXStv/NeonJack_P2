<?php

namespace App\Http\Controllers\Api;

use App\http\Controllers\Controller;
use App\Models\Log;
use Illuminate\Http\Request;
use App\Http\Requests\StoreLogRequest;

class LogController extends Controller
{
    /**
     * Muestra la lista de recursos.
     */
    public function index()
    {
        $logs = Log::get();
        return response()->json($logs);
    }

    /**
     * Almacena un recurso recién creado.
     */
    public function store(StoreLogRequest $request)
    {
        $data = $request->validated();
        $log = Log::create($data);
        return response()->json($log, 201);
    }

    /**
     * Muestra el recurso especificado.
     */
    public function show(Log $log)
    {
        return response()->json($log->load('user'));
    }

    /**
     * Elimina el recurso especificado.
     */
    public function destroy(Log $log){
        $log->delete();
        return response()->json(null, 204);
    }
}
