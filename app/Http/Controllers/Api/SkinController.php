<?php

namespace App\Http\Controllers;

use App\Models\Skin;
use Illuminate\Http\Request;

class SkinController extends Controller
{
    /**
     * Muestra la lista de recursos.
     */
    public function index()
    {
        $skins = Skin::where('activo', true)->get();
        return response()->json($skins);
    }

    /**
     * Almacena un recurso recién creado.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'precio' => 'required|numeric',
        ]);

        $skin = Skin::create([
            'nombre' => $request->name,
            'precio' => $request->price,
            'activo' => true,
        ]);

        return response()->json($skin, 201);
    }

    /**
     * Muestra el recurso especificado.
     */
    public function show(Skin $skin)
    {
        return response()->json($skin);
    }

    /**
     * Actualiza el recurso especificado.
     */
    public function update(Request $request, Skin $skin)
    {
        $request->validate([
            'nombre' => 'string|max:255',
            'precio' => 'numeric',
            'activo' => 'boolean',
        ]);

        $skin->update($request->all());

        return response()->json($skin);
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
