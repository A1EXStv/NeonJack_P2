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