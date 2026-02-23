<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mano;
use Illuminate\Http\Request;
use App\Http\Requests\StoreManoRequest;


class ManoController extends Controller
{
    /**
     * Muestra la lista de recursos.
     */
    public function index()
    {
        $mano = Mano::with('sala','usarios')->latest()->get();
        return response()->json($mano);
    }

    /**
     * Almacena un recurso recién creado.
     */
    public function store(StoreManoRequest $request)
    {
        $data = $request->validated();
        $mano = Mano::create($data);
        return $mano;
    }

    /**
     * Muestra el recurso especificado.
     */
    public function show(Mano $mano)
    {
        return  $mano;
    }

    /*
    * Elimina eñ recursp
     */
    public function destroy(Mano $mano)
    {
        $mano->delete();
        return  $mano;
    }
}
