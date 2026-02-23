<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sala;
use Illuminate\Http\Request;
use App\Http\Requests\StoreSalaRequest;


class SalaController extends Controller
{
    /**
     * Muestra la lista de recursos.
     */
    public function index()
    {
        $salas = Sala::with(['manos.usuarios'])->latest()->get();
        return response()->json($salas);
    }

    /**
     * Almacena un recurso recién creado.
     */
    public function store(StoreSalaRequest $request)
    {
        $data = $request->validated();
        $sala = Sala::create($data);
        return $sala;
    }

    public function update(StoreSalaRequest $request, Sala $sala)
    {
        $data = $request->validated();
        $sala->update($data);
        return response()->json($sala);
    }

    /**
     * Muestra el recurso especificado.
     */
    public function show(Sala $sala)
    {
        return  $sala;
    }

    /*
    * Elimina eñ recursp
     */
    public function destroy(Sala $sala)
    {
        $sala->delete();
        return  $sala;
    }
}
