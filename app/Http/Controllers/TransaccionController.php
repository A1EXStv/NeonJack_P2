<?php

namespace App\Http\Controllers;

use App\Models\Transaccion;
use Illuminate\Http\Request;

class TransaccionController extends Controller
{
    /**
     * Muestra la lista de recursos.
     */
    public function index()
    {
        $transacciones = Transaccion::with('user')->latest()->get();
        return response()->json($transacciones);
    }

    /**
     * Almacena un recurso recién creado.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|string|max:50',
            'amount' => 'required|numeric',
        ]);

        $transaccion = Transaccion::create($request->all());

        return response()->json($transaccion, 201);
    }

    /**
     * Muestra el recurso especificado.
     */
    public function show(Transaccion $transaccion)
    {
        return response()->json($transaccion->load('user'));
    }
}
