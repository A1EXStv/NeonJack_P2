<?php

namespace App\Http\Controllers\Api;

use App\http\Controllers\Controller;
use App\Models\Transaccion;
use App\Models\Ajustes;
use App\Models\Cartera;
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
            $saldoActual = Cartera::where('user_id', $data['user_id'])->sum('cantidad');

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
