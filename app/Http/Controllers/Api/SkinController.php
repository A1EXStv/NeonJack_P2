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
        $skins = Skin::where('is_active', true)->get();
        return response()->json($skins);
    }

    /**
     * Almacena un recurso recién creado.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
        ]);

        $skin = Skin::create([
            'name' => $request->name,
            'price' => $request->price,
            'is_active' => true,
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
            'name' => 'string|max:255',
            'price' => 'numeric',
            'is_active' => 'boolean',
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
