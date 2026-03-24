<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cartera;
use Illuminate\Http\Request;
use App\Http\Requests\StoreCarteraRequest;


class CarteraController extends Controller
{
    /**
     * Mostrar movimientos de un usuario
     */
    public function index(Request $request)
    {
        
        // $request->validate([
        //     'user_id' => 'required|exists:users,id'
        // ]);

        $movimientos = Cartera::get();

        return response()->json($movimientos);
    }

    /**
     * Crear movimiento manual (premios, apuestas, etc.)
     */
    public function store(Request $request)
    {
       
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'cantidad' => 'required|numeric|min:1',
            'tipoMovimiento' => 'required|in:entrada,salida',
            'concepto' => 'nullable|string'
        ]);

        $cantidad = $request->cantidad;

        // Si es salida, validar saldo
        if ($request->tipoMovimiento === 'salida') {

            $saldoActual = Cartera::where('user_id', $request->user_id)
                ->sum('cantidad');

            if ($saldoActual < $cantidad) {
                return response()->json([
                    'message' => 'Saldo insuficiente'
                ], 400);
            }

            $cantidad = -$cantidad; // convertir a negativo
        }

        $movimiento = Cartera::create([
            'user_id' => $request->user_id,
            'cantidad' => $cantidad,
            'tipoMovimiento' => $request->tipoMovimiento,
            'concepto' => $request->concepto ?? 'Movimiento manual'
        ]);

        return response()->json($movimiento);
    }

    /**
     * Mostrar saldo actual del usuario
     */
    public function show($user_id)
    {
        $entradas = Cartera::where('user_id', $user_id)
            ->where('tipoMovimiento', 'entrada')
            ->sum('cantidad');

        $salidas = Cartera::where('user_id', $user_id)
            ->where('tipoMovimiento', 'salida')
            ->sum('cantidad');

        $saldo = $entradas - $salidas;

        return response()->json([
            'user_id' => $user_id,
            'saldo_fichas' => $saldo
        ]);
    }

    /**
     * Eliminar movimiento
     */
    public function destroy(Cartera $cartera)
    {
        $cartera->delete();
        return response()->json($cartera);
    }
}