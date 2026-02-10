<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;

class LogController extends Controller
{
    /**
     * Muestra la lista de recursos.
     */
    public function index()
    {
        $logs = Log::with('user')->latest()->get();
        return response()->json($logs);
    }

    /**
     * Almacena un recurso recién creado.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'action' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $log = Log::create($request->all());

        return response()->json($log, 201);
    }

    /**
     * Muestra el recurso especificado.
     */
    public function show(Log $log)
    {
        return response()->json($log->load('user'));
    }
}
