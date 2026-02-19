<?php

namespace App\Http\Controllers\Api;

use App\http\Controllers\Controller;
use App\Models\Skin;
use Illuminate\Http\Request;
use App\Http\Requests\StoreSkinRequest;
use App\Http\Requests\UpdateSkinRequest;


class SkinController extends Controller
{
    /**
     * Muestra la lista de recursos.
     */
    public function index()
    {
        $skins = Skin->get();
        return response()->json($skins);
    }

    /**
     * Almacena un recurso recién creado.
     */
    public function store(StoreSkinRequest $request)
    {
        $data = $request->validated();
        $skin = Skin::create($data);
        return $skin;
    }

    /**
     * Muestra el recurso especificado.
     */
    public function show(Skin $skin)
    {
        return $skin;
    }

    /**
     * Actualiza el recurso especificado.
     */
    public function update(UpdateSkinRequest $request, Skin $skin)
    {
        $skin->nombre = $request->nombre ?? $skin->nombre;
        $skin->precio = $request->precio ?? $skin->precio;
        
        if ($request->has('activo')) {
            $skin->activo = $request->activo;
        }

        if ($skin->save()) {
            return response()->json([
                'message' => 'Skin actualizada correctamente',
                'data' => $skin
            ], 200);
        }

        return response()->json(['error' => 'No se pudo actualizar la skin'], 500);
    }

    /**
     * Elimina el recurso especificado.
     */
    public function destroy(Skin $skin)
    {
        $skin->delete();
        return response()->json(null, 204);
    }
}
