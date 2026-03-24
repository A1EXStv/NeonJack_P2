<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Skin;
use Illuminate\Http\Request;
use App\Http\Requests\StoreSkinRequest;
use App\Http\Requests\UpdateSkinRequest;
use App\Http\Resources\SkinResource;

class SkinController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orderColumn = request('order_column', 'created_at');

        if (!in_array($orderColumn, ['id', 'nombre', 'precio', 'created_at'])) {
            $orderColumn = 'created_at';
        }

        $orderDirection = request('order_direction', 'desc');

        if (!in_array($orderDirection, ['asc', 'desc'])) {
            $orderDirection = 'desc';
        }

        $skins = Skin::
            when(request('search_id'), function ($query) {
                $query->where('id', request('search_id'));
            })
            ->when(request('search_nombre'), function ($query) {
                $query->where('nombre', 'like', '%'.request('search_nombre').'%');
            })
            ->when(request('search_global'), function ($query) {
                $query->where(function($q) {
                    $q->where('id', request('search_global'))
                      ->orWhere('nombre', 'like', '%'.request('search_global').'%');
                });
            })
            ->orderBy($orderColumn, $orderDirection)
            ->paginate(500);

        return SkinResource::collection($skins);
    }

    /**
     * Store a newly created resource in storage.
     */
   /* public function store(StoreSkinRequest $request)
    {
        $data = $request->validated();

        $skin = Skin::create($data);

        return new SkinResource($skin);
    }*/
public function store(StoreSkinRequest $request)
{
    $data = $request->validated();

    $skin = Skin::create($data);

    if ($request->hasFile('picture')) {
        $skin->addMediaFromRequest('picture')
            ->preservingOriginal()
            ->toMediaCollection('skins');
    }

    return new SkinResource($skin);
}

    /**
     * Display the specified resource.
     */
    public function show(Skin $skin)
    {
        return new SkinResource($skin);
    }

    /**
     * Update the specified resource.
     */
    public function update(UpdateSkinRequest $request, Skin $skin)
    {
        $skin->update($request->validated());

        return new SkinResource($skin);
    }

    /**
     * Subir o actualizar imagen
     */
    public function updateimg(Request $request)
    {
        $skin = Skin::find($request->id);

        if ($request->hasFile('picture')) {

            $skin->media()->delete();

            $skin->addMediaFromRequest('picture')
                ->preservingOriginal()
                ->toMediaCollection('skins');
        }

        $skin = Skin::with('media')->find($request->id);

        return new SkinResource($skin);
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(Skin $skin)
    {
        $skin->delete();

        return response()->noContent();
    }
}
/*namespace App\Http\Controllers\Api;

use App\http\Controllers\Controller;
use App\Models\Skin;
use Illuminate\Http\Request;
use App\Http\Requests\StoreSkinRequest;
use App\Http\Requests\UpdateSkinRequest;


class SkinController extends Controller
{
    /**
     * Muestra la lista de recursos.
     *
    public function index()
    {
        $skins = Skin::get();        
        return response()->json($skins);
    }

    /**
     * Almacena un recurso recién creado.
     *
    public function store(StoreSkinRequest $request)
    {
        $data = $request->validated();
        $skin = Skin::create($data);
        return $skin;
    }

    /**
     * Muestra el recurso especificado.
     *
    public function show(Skin $skin)
    {
        return $skin;
    }

    /**
     * Actualiza el recurso especificado.
     *
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
     *
    public function destroy(Skin $skin)
    {
        $skin->delete();
        return response()->json(null, 204);
    }
}*/
