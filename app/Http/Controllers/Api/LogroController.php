<?php

namespace App\Http\Controllers;

use App\Models\Logro;
use Illuminate\Http\Request;

class LogroController extends Controller
{
    /**
     * Muestra la lista de recursos.
     */
    public function index()
    {
        $logros = Logro::where('activo', true)->get();
        return response()->json($logros);
    }

    /**
     * Almacena un recurso recién creado.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        $logro = Logro::create([
            'nombre' => $request->name,
            'descripcion' => $request->description,
            'activo' => true,
        ]);

        return response()->json($logro, 201);
    }

    /**
     * Muestra el recurso especificado.
     */
    public function show(Logro $logro)
    {
        return response()->json($logro);
    }

    /**
     * Actualiza el recurso especificado.
     */
    public function update(Request $request, Logro $logro)
    {
        $request->validate([
            'nombre' => 'string|max:255',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
        ]);

        $logro->update($request->all());

        return response()->json($logro);
    }

    /**
     * Elimina el recurso especificado.
     */
    public function destroy(Logro $logro)
    {
        $logro->delete();
        return response()->json(null, 204);
    }
}
