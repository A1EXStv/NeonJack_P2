<?php

namespace App\Http\Controllers\Api;

use App\http\Controllers\Controller;
use App\Models\Logro;
use Illuminate\Http\Request;
use App\Http\Requests\StoreLogroRequest;
use App\Http\Requests\UpdateLogroRequest;

class LogroController extends Controller
{
    /**
     * Muestra la lista de recursos.
     */
    public function index()
    {
        $logros = Logro->get();
        return response()->json($logros);
    }

    /**
     * Almacena un recurso recién creado.
     */
    public function store(StoreLogrosRequest $request)
    {
        $data = $request->validated();
        $logro = Logro::create($data);
        return $logro;
    }

    /**
     * Muestra el recurso especificado.
     */
    public function show(Logro $logro)
    {
        return response()->json($logro);
    }

    /**
     * Actualiza el recurso especificado.
     */
    public function update(UpdateLogroRequest $request, Logro $logro)
    {
        $logro->nombre = $request->nombre ?? $logro->nombre;
        $logro->descripcion = $request->descripcion ?? $logro->descripcion;
        
        if($request->has('activo')){
            $logro->activo = $request->activo;
        }

        if($logro->save()){
            return response()->json([
                'message' => 'Logro actualizado correctamente',
                'data' => $logro
            ], 200);
        }
        return response()->json(['error' => 'No se pudo actualizar el logro.']);
    }

    /**
     * Elimina el recurso especificado.
     */
    public function destroy(Logro $logro)
    {
        $logro->delete();
        return response()->json(null, 204);
    }
}
