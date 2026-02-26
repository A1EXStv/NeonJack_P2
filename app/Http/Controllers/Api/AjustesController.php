<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ajustes;
use Illuminate\Http\Request;
use App\Http\Requests\StoreAjustesRequest;


class AjustesController extends Controller
{
    /**
     * Muestra la lista de recursos.
     */
    public function index()
    {
        $ajustes = Ajustes::get();
        return response()->json($ajustes);
    }

    /**
     * Almacena un recurso recién creado.
     */
    public function store(StoreAjustesRequest $request)
    {
        $data = $request->validated();
        $ajuste = Ajustes::create($data);
        return $ajuste;
    }

    public function update(StoreAjustesRequest $request, Ajustes $ajuste)
    {
        $data = $request->validated();
        $ajuste->update($data);
        return response()->json($ajuste);
    }

    /**
     * Muestra el recurso especificado.
     */
    public function show(Ajustes $ajuste)
    {
        return  $ajuste;
    }

    /*
    * Elimina eñ recursp
     */
    public function destroy(Ajustes $ajuste)
    {
        $ajuste->delete();
        return  $ajuste;
    }
}
