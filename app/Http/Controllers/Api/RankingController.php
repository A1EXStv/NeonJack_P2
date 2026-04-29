<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\RankingResource;

class RankingController extends Controller
{
    public function index()
    {
        $ranking = DB::table('partida_usuario as pu')
            ->join('users as u', 'pu.user_id', '=', 'u.id')
            ->leftJoin('media as m', function ($join) {
                $join->on('m.model_id', '=', 'u.id')
                     ->where('m.model_type', 'App\Models\User')
                     ->where('m.collection_name', 'images-users');
            })
            ->where('pu.resultado', 'gana')
            ->select(
                'u.id',
                'u.alias',
                DB::raw('COUNT(*) as gana'),
                DB::raw('MAX(m.id) as avatar_id'),
                DB::raw('MAX(m.file_name) as avatar_file')
            )
            ->groupBy('u.id', 'u.alias')
            ->orderByDesc('gana')
            ->limit(4)
            ->get();

        return RankingResource::collection($ranking);
    }

    public function topBeneficio()
    {
        $ranking = DB::table('partida_usuario as pu')
            ->join('users as u', 'pu.user_id', '=', 'u.id')
            ->leftJoin('media as m', function ($join) {
                $join->on('m.model_id', '=', 'u.id')
                    ->where('m.model_type', 'App\Models\User')
                    ->where('m.collection_name', 'images-users');
            })
            ->select(
                'u.id',
                'u.alias as name',
                DB::raw('SUM(pu.balance_resultado) as total'),
                DB::raw('MAX(m.id) as avatar_id'),
                DB::raw('MAX(m.file_name) as avatar_file')
            )
            ->where('pu.estado', 'finalizado')
            ->groupBy('u.id', 'u.alias')
            ->orderByDesc('total')
            ->limit(4)
            ->get();

        return RankingResource::collection($ranking);
    }

    public function topBeneficioPorMano()
    {
        $ranking = DB::table('partida_usuario as pu')
            ->join('users as u', 'pu.user_id', '=', 'u.id')
            ->leftJoin('media as m', function ($join) {
                $join->on('m.model_id', '=', 'u.id')
                     ->where('m.model_type', 'App\Models\User')
                     ->where('m.collection_name', 'images-users');
            })
            ->select(
                'u.id',
                'u.alias as name',
                DB::raw('MAX(pu.balance_resultado) as total'),
                DB::raw('MAX(m.id) as avatar_id'),
                DB::raw('MAX(m.file_name) as avatar_file')
            )
            ->where('pu.estado', 'finalizado')
            ->where('pu.balance_resultado', '>', 0)
            ->groupBy('u.id', 'u.alias')
            ->orderByDesc('total')
            ->limit(4)
            ->get();

        return RankingResource::collection($ranking);
    }
}