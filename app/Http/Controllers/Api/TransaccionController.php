<?php

namespace App\Http\Controllers\Api;

use App\http\Controllers\Controller;
use App\Models\Transaccion;
use App\Models\Ajustes;
use App\Models\Cartera;
use App\Models\User;
// use App\Http\Controllers\Api\AjustesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreTransaccionRequest;


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
     * Devuelve las transacciones del usuario autenticado.
     */
    public function misTransacciones(Request $request)
    {
        $transacciones = Transaccion::where('user_id', $request->user()->id)
            ->latest()
            ->get();
        return response()->json($transacciones);
    }

    /**
     * Almacena un recurso recién creado.
     */
    public function store(StoreTransaccionRequest $request)
    {
        $data = $request->validated();

        $conversion = Ajustes::where('clave', '1 euro')->value('valor');

        if (!$conversion) {
            return response()->json([
                'message' => 'No existe conversión configurada'
            ], 400);
        }

        $fichas = $data['cantidad'] * $conversion;

        if ($data['tipo'] === 'retirada') {
            $saldoActual = User::find($data['user_id'])?->wallet ?? 0;

            if ($saldoActual < $fichas) {
                return response()->json([
                    'message' => 'Saldo insuficiente'
                ], 400);
            }
        }

        $transaccion = DB::transaction(function () use ($data, $fichas) {

            $transaccion = Transaccion::create($data);

            Cartera::create([
                'user_id'        => $data['user_id'],
                'cantidad'       => $data['tipo'] === 'deposito' ? $fichas : -$fichas,
                'tipoMovimiento' => $data['tipo'] === 'deposito' ? 'deposito' : 'retiro',
                'concepto'       => $data['tipo'] === 'deposito' ? 'Depósito en euros' : 'Retirada en euros',
            ]);

            $user = User::find($data['user_id']);
            if ($data['tipo'] === 'deposito') {
                $user->increment('wallet', $fichas);
            } else {
                $user->decrement('wallet', $fichas);
            }

            return $transaccion;
        });

        return response()->json($transaccion);
    }



    /**
     * Muestra el recurso especificado.
     */
    public function show(Transaccion $transaccion)
    {
        return  $transaccion;
    }

    /*
    * Elimina eñ recursp
     */
    public function destroy(Transaccion $transaccion)
    {
        $transaccion->delete();
        return  $transaccion;
    }
}
