<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ajustes;
use App\Models\Cartera;
use App\Models\Transaccion;
use App\Models\User;
use App\Services\RedsysService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RedsysController extends Controller
{
    public function __construct(private RedsysService $redsys) {}

    /**
     * POST /api/redsys/create-payment
     * Authenticated user provides amount (euros). Returns form fields for Redsys.
     */
    public function createPayment(Request $request)
    {
        $request->validate(['cantidad' => 'required|numeric|min:1|max:10000']);

        $user      = $request->user();
        $euros     = (float) $request->cantidad;
        $cents     = (int) round($euros * 100);

        // 12-char unique order id: 4 digits of userId + 8 random
        $order = str_pad($user->id, 4, '0', STR_PAD_LEFT) . strtoupper(substr(uniqid(), -8));

        // Cache the pending order so we can look it up when Redsys notifies/redirects
        Cache::put("redsys_order_{$order}", [
            'user_id' => $user->id,
            'euros'   => $euros,
        ], now()->addHour());

        $base = rtrim($request->getSchemeAndHttpHost(), '/');

        $formData = $this->redsys->buildPaymentForm(
            amountCents:      $cents,
            order:            $order,
            urlOk:            "{$base}/redsys/success",
            urlKo:            "{$base}/redsys/failure",
            urlNotification:  "{$base}/redsys/notification",
        );

        return response()->json([
            'tpv_url'  => RedsysService::TPV_URL,
            'version'  => $formData['version'],
            'params'   => $formData['params'],
            'signature'=> $formData['signature'],
        ]);
    }

    /**
     * POST /redsys/notification  (web route, CSRF exempt)
     * Redsys server-to-server notification. Verify and credit funds.
     */
    public function notification(Request $request)
    {
        $params   = $this->redsys->verifyResponse(
            $request->input('Ds_MerchantParameters', ''),
            $request->input('Ds_Signature', '')
        );

        if (!$params || !$this->redsys->isSuccess($params)) {
            return response('KO', 400);
        }

        $order = $params['Ds_Order'] ?? '';
        $this->processOrder($order, $params);

        return response('OK', 200);
    }

    /**
     * GET /redsys/success
     * Browser redirect after successful payment. Verify, credit, go to SPA.
     */
    public function success(Request $request)
    {
        $params = $this->redsys->verifyResponse(
            $request->query('Ds_MerchantParameters', ''),
            $request->query('Ds_Signature', '')
        );

        if ($params && $this->redsys->isSuccess($params)) {
            $this->processOrder($params['Ds_Order'] ?? '', $params);
            return redirect('/app/transactions?payment=ok');
        }

        return redirect('/app/transactions?payment=error');
    }

    /**
     * GET /redsys/failure
     */
    public function failure()
    {
        return redirect('/app/transactions?payment=ko');
    }

    // -------------------------------------------------------------------------

    private function processOrder(string $order, array $redsysParams): void
    {
        if (!$order) return;

        $cacheKey = "redsys_order_{$order}";
        $pending  = Cache::get($cacheKey);

        // Already processed or unknown order
        if (!$pending) return;

        $userId = $pending['user_id'];
        $euros  = $pending['euros'];

        $conversion = Ajustes::where('clave', '1 euro')->value('valor');
        if (!$conversion) return;

        $fichas = $euros * $conversion;

        DB::transaction(function () use ($userId, $euros, $fichas) {
            Transaccion::create([
                'user_id'  => $userId,
                'tipo'     => 'deposito',
                'cantidad' => $euros,
            ]);

            Cartera::create([
                'user_id'        => $userId,
                'cantidad'       => $fichas,
                'tipoMovimiento' => 'deposito',
                'concepto'       => 'Depósito vía Redsys',
            ]);

            User::find($userId)?->increment('wallet', $fichas);
        });

        Cache::forget($cacheKey);
    }
}
