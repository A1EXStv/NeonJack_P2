<?php

namespace App\Http\Controllers\Api;

use App\http\Controllers\Controller;
use App\Models\Transaccion;
use Illuminate\Http\Request;
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
        $transaccion = Transaccion::create($data);
        return $transaccion;
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
